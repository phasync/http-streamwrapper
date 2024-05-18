<?php
namespace phasync\HttpStreamWrapper;

use phasync\Services\CurlMulti;

class HttpStreamWrapper {
    private $position;
    private $url;
    private $handle;
    private $content;
    private $contentLength;
    public $context;

    private static bool $isEnabled = false;

    public static function enable(): void {
        if (self::$isEnabled) {
            return;
        }
        stream_wrapper_unregister('http');
        stream_Wrapper_unregister('https');
        stream_wrapper_register('http', self::class);
        stream_wrapper_register('https', self::class);
        self::$isEnabled = true;
    }
    
    public static function disable(): void {
        if (!self::$isEnabled) {
            return;
        }
        stream_wrapper_restore('http');
        stream_wrapper_restore('https');
        self::$isEnabled = false;
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->url = $path;
        $this->position = 0;

        // Extract the context options
        $this->context = stream_context_get_options($this->context);
        $opts = isset($this->context['http']) ? $this->context['http'] : [];

        // Initialize cURL session
        $this->handle = curl_init();

        // Set cURL options
        curl_setopt($this->handle, CURLOPT_URL, $this->url);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->handle, CURLOPT_MAXREDIRS, 10); // Maximum number of redirects

        // Set headers if available
        if (isset($opts['header'])) {
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, $opts['header']);
        }

        // Set other options like method, content, etc.
        if (isset($opts['method']) && strtolower($opts['method']) === 'post') {
            curl_setopt($this->handle, CURLOPT_POST, true);
            if (isset($opts['content'])) {
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $opts['content']);
            }
        } elseif (isset($opts['method']) && strtolower($opts['method']) === 'put') {
            curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, 'PUT');
            if (isset($opts['content'])) {
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $opts['content']);
            }
        }

        // Execute cURL session
        $this->content = CurlMulti::await($this->handle);

        // Check for errors
        if ($this->content === false) {
            return false;
        }

        $this->contentLength = strlen($this->content);
        return true;
    }

    public function stream_read($count)
    {
        $data = substr($this->content, $this->position, $count);
        $this->position += strlen($data);
        return $data;
    }

    public function stream_eof()
    {
        return $this->position >= $this->contentLength;
    }

    public function stream_stat()
    {
        return [];
    }

    public function stream_close()
    {
        curl_close($this->handle);
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < $this->contentLength && $offset >= 0) {
                    $this->position = $offset;
                    return true;
                } else {
                    return false;
                }
            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;
                    return true;
                } else {
                    return false;
                }
            case SEEK_END:
                if ($this->contentLength + $offset >= 0) {
                    $this->position = $this->contentLength + $offset;
                    return true;
                } else {
                    return false;
                }
            default:
                return false;
        }
    }

    public function url_stat($path, $flags)
    {
        // This can be enhanced to return actual URL stats
        return [];
    }
}