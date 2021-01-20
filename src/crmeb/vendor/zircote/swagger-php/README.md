[![Build Status](https://img.shields.io/travis/zircote/swagger-php/master.svg?style=flat-square)](https://travis-ci.org/zircote/swagger-php)
[![Total Downloads](https://img.shields.io/packagist/dt/zircote/swagger-php.svg?style=flat-square)](https://packagist.org/packages/zircote/swagger-php)
[![License](https://img.shields.io/badge/license-Apache2.0-blue.svg?style=flat-square)](LICENSE-2.0.txt)

# swagger-php

Generate interactive [OpenAPI](https://www.openapis.org) documentation for your RESTful API using [doctrine annotations](https://www.doctrine-project.org/projects/doctrine-annotations/en/latest/index.html).

## Features

- Compatible with the OpenAPI 3.0 specification.
- Extracts information from code & existing phpdoc annotations.
- Command-line interface available.
- [Documentation site](https://zircote.github.io/swagger-php/) with a getting started guide.
- Exceptional error reporting (with hints, context)

## Installation (with [Composer](https://getcomposer.org))

```bash
composer require zircote/swagger-php
```

For cli usage from anywhere install swagger-php globally and make sure to place the `~/.composer/vendor/bin` directory in your PATH so the `openapi` executable can be located by your system.

```bash
composer global require zircote/swagger-php
```

## Usage

Add annotations to your php files.

```php
/**
 * @OA\Info(title="My First API", version="0.1")
 */

/**
 * @OA\Get(
 *     path="/api/resource.json",
 *     @OA\Response(response="200", description="An example resource")
 * )
 */
```

Visit the [Documentation website](https://zircote.github.io/swagger-php/) for the [Getting started guide](https://zircote.github.io/swagger-php/Getting-started.html) or look at the [Examples directory](Examples/) for more examples.

### Usage from php

Generate always-up-to-date documentation.

```php
<?php
require("vendor/autoload.php");
$openapi = \OpenApi\scan('/path/to/project');
header('Content-Type: application/x-yaml');
echo $openapi->toYaml();
```

### Usage from the Command Line Interface

Generate the documentation to a static json file.

```bash
./vendor/bin/openapi --help
```

### Usage from the Deserializer

Generate the OpenApi annotation object from a json string, which makes it easier to manipulate objects programmatically.

```php
<?php

use OpenApi\Serializer;

$serializer = new Serializer();
$openapi = $serializer->deserialize($jsonString, 'OpenApi\Annotations\OpenApi');
echo $openapi->toJson();
```

### Usage from [docker](https://docker.com)

Generate the swagger documentation to a static json file.

```
docker run -v "$PWD":/app -it tico/swagger-php --help
```

## More on OpenApi & Swagger

- https://swagger.io
- https://www.openapis.org
- [OpenApi Documentation](https://swagger.io/docs/)
- [OpenApi Specification](http://swagger.io/specification/)
- [Related projects](docs/Related-projects.md)

## Contributing

Feel free to submit [Github Issues](https://github.com/zircote/swagger-php/issues)
or pull requests.

The documentation website is build from the [docs](docs/) folder with [vuepress](https://vuepress.vuejs.org).

Make sure pull requests pass [PHPUnit](https://phpunit.de/)
and [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) (PSR-2) tests.

### To run both unit tests and linting execute:
```bash
composer test
```

### Running unit tests only:
```bash
./bin/phpunit
```

### Running linting only:
```bash
composer lint
```

### To make `php-cs-fixer` fix linting errors:
```bash
composer cs
```
