<?php
namespace phasync;

use phasync\HttpStreamWrapper\HttpStreamWrapper;

\phasync::onEnter(HttpStreamWrapper::enable(...));
\phasync::onExit(HttpStreamWrapper::disable(...));