Google API Extensions for PHP
=================================

[![Build Status](https://img.shields.io/travis/googleapis/gax-php.svg)](https://travis-ci.org/googleapis/gax-php)

[![Code Coverage](https://img.shields.io/codecov/c/github/googleapis/gax-php.svg)](https://codecov.io/github/googleapis/gax-php)

- [Documentation](http://googleapis.github.io/gax-php/phpdoc)

Google API Extensions for PHP (gax-php) is a set of modules which aids
the development of APIs for clients based on [gRPC][] and Google API
conventions.

Application code will rarely need to use most of the classes within this library
directly, but code generated automatically from the API definition files in
[Google APIs][] can use services such as page streaming and retry to
provide a more convenient and idiomatic API surface to callers.

[gRPC]: http://grpc.io
[Google APIs]: https://github.com/googleapis/googleapis/


PHP Versions
----------------

gax-php currently requires PHP 5.5 or higher.


Contributing
------------

Contributions to this library are always welcome and highly encouraged.

See the [CONTRIBUTING][] documentation for more information on how to get started.

[CONTRIBUTING]: https://github.com/googleapis/gax-php/blob/master/CONTRIBUTING.md


Versioning
----------

This library follows [Semantic Versioning][].

It is currently in major version zero (``0.y.z``), which means that anything
may change at any time and the public API should not be considered
stable.

[Semantic Versioning]: http://semver.org/


Repository Structure
-------

All code lives under src/ and is contained in the `Google\GAX` namespace.


License
-------

BSD - See [LICENSE][] for more information.

[LICENSE]: https://github.com/googleapis/gax-php/blob/master/LICENSE
