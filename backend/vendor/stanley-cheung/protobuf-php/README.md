Protobuf for PHP
================

Protobuf for PHP is an implementation of Google's Protocol Buffers for the PHP
language, supporting its binary data serialization and including a `protoc` 
plugin to generate PHP classes from .proto files.

Great effort has been put into generating PHP files that include all sort of type
hints to aide IDE's with autocompletion. Therefore, it can not only be used to
communicate with Protocol Buffers services but also as a generation tool for 
_data objects_ no matter what the final serialization is.

For more information see the [included man pages](http://drslump.github.com/Protobuf-PHP/).


## Requirements

  - PHP 5.3  
  - Pear's Console_CommandLine (for the protoc wrapper tool)
  - Google's `protoc` compiler version 2.3 or above
  - GMP or BC Math extensions ¹

  ¹ Only needed for negative values in `int32`, `int64` or `fixed64` types. See
    the _known issues_ section.


## Features

### Working

  - Standard types (numbers, string, enums, messages, etc)
  - Pluggable serialization backends (codecs)
    - Standard Binary
    - Standard TextFormat ¹
    - PhpArray
    - JSON
    - [ProtoJson](https://github.com/drslump/ProtoJson) (_TagMap_ and _Indexed_ variants)
    - XML
  - Protoc compiler plugin to generate the PHP classes
  - Extensions
  - Unknown fields
  - Packed fields
  - Reflection
  - Dynamic messages with annotations support
  - Generates service interfaces
  - Includes comments from .proto files in the generated files
  - Pear package for easy installation

¹ Only serialization is supported

### Future

  - Speed optimized code generation mode
  - Support numbers beyond PHP's native limits



## Example usage

```php
<?php
require_once 'DrSlump/Protobuf.php';
\DrSlump\Protobuf::autoload();

$person = new Tutorial\Person();
$person->name = 'DrSlump';
$person->setId(12);

$book = new Tutorial\AddressBook();
$book->addPerson($person);

// Use default codec
$data = $book->serialize();

// Use custom codec
$codec = new \DrSlump\Protobuf\Codec\Binary();
$data = $codec->encode($book);
// ... or ...
$data = $book->serialize($codec);
```

## Installation

Install with Pear

    pear channel-discover pear.pollinimini.net
    pear install drslump/Protobuf-beta

You can also get the latest version by checking out a copy of the
repository in your computer.



## Known issues


### Types

PHP is very weak when dealing with numbers processing. Several work arounds have been applied
to the standard binary codec to reduce incompatibilities between Protobuf types and PHP ones.

  - Protobuf stores floating point values using the [IEEE 754](http://en.wikipedia.org/wiki/IEEE_754) standard
    with 64bit words for the `double` and 32bit for the `float` types. PHP supports IEEE 754 natively although
    the precission is platform dependant, however it typically supports 64bit doubles. It means that
    if your PHP was compiled with 64bit sized doubles (or greater) you shouldn't have any problem encoding
    and decoded float and double typed values with Protobuf.

  - Integer values are also [platform dependant in PHP](http://www.php.net/manual/en/language.types.integer.php).
    The library has been developed and tested against PHP binaries compiled with 64bit integers. The encoding and
    decoding algorithm should in theory work no matter if PHP uses 32bit or 64bit integers internally, just take
    into account that with 32bit integers the numbers cannot exceed in any case the `PHP_INT_MAX` value (2147483647).

    While Protobuf supports unsigned integers PHP does not. In fact, numbers above the compiled PHP maximum
    integer (`PHP_INT_MAX`, 0x7FFFFFFFFFFFFFFF for 64bits) will be automatically casted to doubles, which
    typically will offer 53bits of decimal precission, allowing to safely work with numbers upto
    0x20000000000000 (2^53), even if they are represented in PHP as floats instead of integers. Higher numbers
    will loose precission or might even return an _infinity_ value, note that the library does not include
    any checking for these numbers and using them might provoke unexpected behaviour.

    Negative values when encoded as `int32`, `int64` or `fixed64` types require the big integer extensions
    [GMP](http://www.php.net/gmp) or [BC Math](http://www.php.net/bc) (the later only for 64bit architectures)
    to be available in your PHP environment. The reason is that when encoding these negative numbers without
    using _zigzag_ the binary representation uses the most significant bit for the sign, thus the numbers become
    above the maximum supported values in PHP. The library will check for these conditions and will automatically
    try to use GMP or BC to process the value.


### Strings

The binary codec expects strings to be encoded using UTF-8. PHP does not natively support string encodings,
PHP's string data type is basically a length delimited stream of bytes, so it's not trivial to include
automatic encoding conversion into the library encoding and decoding routines. Instead of trying to guess
or offer a configuration interface for the encoding, the binary codec will process the `string` type just as
it would process `byte` one, delegating on your application the task of encoding or decoding in the desired
character set.

### Memory usage

Large messages might be troublesome since the way the library is modelled does not allow to parse or
serialize messages as a streams, instead the whole operation is performed in memory, which allows for faster
processing but could consume too much RAM if messages are too large.


### Unknown fields

Since wire types are different across different codec's formats, it's not possible to transcode unkwnon
fields consumed in one codec to another. This means, for example, that when consuming a message using the
binary codec, if it contains unknown fields, they won't be included when serializing the message using the
Json codec.


## Generating PHP classes

The generation tool is designed to be run as a `protoc` plugin, thus it should
work with any proto file supported by the official compiler.

    protoc --plugin=protoc-gen-php --php_out=./build tutorial.proto

To make use of non-standard options in your proto files (like `php.namespace`) you'll
have to import the `php.proto` file included with the library. It's location will 
depend on where you've installed this library.

    protoc -I=./Protobuf-PHP/library/DrSlump/Protobuf/Compiler/protos \
           --plugin=protoc-gen-php --php_out=./build tutorial.proto

In order to make your life easier, the supplied protoc plugin offers an additional
execution mode, where it acts as a wrapper for the `protoc` invocation. It will
automatically include the `php.proto` path so that you don't need to worry about it.

    protoc-gen-php -o ./build tutorial.proto


## LICENSE:

    The MIT License

    Copyright (c) 2011 Iván -DrSlump- Montes

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    'Software'), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
    IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
    CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
    TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
    SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.






