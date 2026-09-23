# CHANGELOG

## 3.1.22 - 2021-05-11

- Deprecate `stream_for` method.

## 3.1.21 - 2021-03-15

- Supported set proxy&timeout on request.

## 3.1.20 - 2020-12-02

- Fix the warning when the Tea::merge method received empty arguments.

## 3.1.19 - 2020-10-09

- Fix the error when the code value is a string.

## 3.1.18 - 2020-09-28

- Require Guzzle Version 7.0

## 3.1.17 - 2020-09-24

- TeaUnableRetryError support get error info.

## 3.1.16 - 2020-08-31

- Fix the Maximum function nesting level error when repeated network requests.

## 3.1.15 - 2020-07-28

- Improved validatePattern method.

## 3.1.14 - 2020-07-03

- Supported set properties of TeaError with `ErrorInfo`.

## 3.1.13 - 2020-06-09

- Reduce dependencies.

## 3.1.12 - 2020-05-13

- Add validate method.
- Supported validate maximun&minimun of property.

## 3.1.11 - 2020-05-07

- Fixed error when class is undefined.

## 3.1.10 - 2020-05-07

- Fixed error when '$item' of array is null

## 3.1.9 - 2020-04-13

- TeaUnableRetryError add $lastException param.

## 3.1.8 - 2020-04-02

- Added some static methods of Model to validate fields of Model.

## 3.1.7 - 2020-03-27

- Improve Tea::isRetryable method.

## 3.1.6 - 2020-03-25

- Fixed bug when body is StreamInterface.

## 3.1.5 - 2020-03-25

- Improve Model.toMap method.
- Improve Tea.merge method.
- Fixed tests.

## 3.1.4 - 2020-03-20

- Added Tea::merge method.
- Change Tea::isRetryable method.

## 3.1.3 - 2020-03-20

- Model: added toModel method.

## 3.1.2 - 2020-03-19

- Model constructor supported array type parameter.

## 3.1.1 - 2020-03-18

- Fixed bug : set method failed.
- Fixed bug : get empty contents form body.

## 3.1.0 - 2020-03-13

- TeaUnableRetryError add 'lastRequest' property.
- Change Tea.send() method return.
- Fixed Tea. allowRetry() method.

## 3.0.0 - 2020-02-14
- Rename package name.

## 2.0.3 - 2020-02-14
- Improved Exception.

## 2.0.2 - 2019-09-11
- Supported `String`.

## 2.0.1 - 2019-08-15
- Supported `IteratorAggregate`.

## 2.0.0 - 2019-08-14
- Design `Request` as a standard `PsrRequest`.

## 1.0.10 - 2019-08-12
- Added `__toString` for `Response`.

## 1.0.9 - 2019-08-01
- Updated `Middleware`.

## 1.0.8 - 2019-07-29
- Supported `TransferStats`.

## 1.0.7 - 2019-07-27
- Improved request.

## 1.0.6 - 2019-07-23
- Trim key for parameter.

## 1.0.5 - 2019-07-23
- Supported default protocol.

## 1.0.4 - 2019-07-22
- Added `toArray()`.

## 1.0.3 - 2019-05-02
- Improved `Request`.

## 1.0.2 - 2019-05-02
- Added getHeader/getHeaders.

## 1.0.1 - 2019-04-02
- Improved design.

## 1.0.0 - 2019-05-02
- Initial release of the AlibabaCloud Tea Version 1.0.0 on Packagist See <https://github.com/aliyun/tea-php> for more information.
