# phasync/http-streamwrapper

`phasync/http-streamwrapper` is a PHP package that makes all HTTP and HTTPS requests asynchronous transparently, when used within a phasync coroutine. This package allows you to perform HTTP operations like fetching data from URLs asynchronously, improving the efficiency of your I/O-bound tasks such as web requests.

## Installation

You can install the package via Composer. There is no configuration needed as it automatically configures itself to be enabled inside coroutines and disables itself outside of coroutines.

```bash
composer require phasync/http-streamwrapper
```

## Usage

When installing this package, async http:// and https:// I/O is automatically enabled inside phasync coroutines. It does not interfere with I/O outside of coroutines.

## Example

Here's an example of how to use phasync/http-streamwrapper within the phasync coroutine framework:

```php
<?php

require 'vendor/autoload.php';

// Example usage within phasync coroutine framework
phasync::run(function() {
    phasync::go(function() {
        $data = file_get_contents("http://example.com");
        // Handle the data
        echo "Data from http://example.com: " . $data . PHP_EOL;
    });

    phasync::go(function() {
        $data = file_get_contents("https://example.com");
        // Handle the data
        echo "Data from https://example.com: " . $data . PHP_EOL;
    });
});
```

In this example, two URLs are fetched asynchronously using file_get_contents within phasync coroutines. The custom stream wrapper ensures that these HTTP operations are non-blocking and efficient.

## License

This package is open-source and licensed under the MIT License.

## Contributing

Contributions are welcome! Please submit pull requests or open issues for any bugs or feature requests.

## Contact

For any questions or inquiries, please open an issue on the GitHub repository.