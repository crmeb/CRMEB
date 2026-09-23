# Helper class to create PSR-7 server request

[![Latest Version](https://img.shields.io/github/release/Nyholm/psr7-server.svg?style=flat-square)](https://github.com/Nyholm/psr7-server/releases)
[![Build Status](https://img.shields.io/travis/Nyholm/psr7-server/master.svg?style=flat-square)](https://travis-ci.org/Nyholm/psr7-server)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Nyholm/psr7-server.svg?style=flat-square)](https://scrutinizer-ci.com/g/Nyholm/psr7-server)
[![Quality Score](https://img.shields.io/scrutinizer/g/Nyholm/psr7-server.svg?style=flat-square)](https://scrutinizer-ci.com/g/Nyholm/psr7-server)
[![Total Downloads](https://poser.pugx.org/nyholm/psr7-server/downloads)](https://packagist.org/packages/nyholm/psr7-server)
[![Monthly Downloads](https://poser.pugx.org/nyholm/psr7-server/d/monthly.png)](https://packagist.org/packages/nyholm/psr7-server)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A helper class that can create ANY PSR-7 server request. 

## Installation

```bash
composer require nyholm/psr7-server
```

## Usage

```php
// Instanciate ANY PSR-17 factory implementations. Here is nyholm/psr7 as an example
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$serverRequest = $creator->fromGlobals();
```

## Other packages

* [nyholm/psr7](https://github.com/Nyholm/psr7) - A super fast PSR-7 implementation.
* [laminas/laminas-httphandlerrunner](https://github.com/laminas/laminas-httphandlerrunner) - To send/emit PSR-7 responses
