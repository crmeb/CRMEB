# CHANGELOG

## 2.9.2 - 2026-07-06

* Pass explicit trim characters ahead of the PHP 8.6 trim default change.

## 2.9.1 - 2026-06-11

* Fixed the compiled runtime to emit function names as string literals, preventing arbitrary code execution.
* Fixed the parser to reject non-identifier function callees, such as literal and raw string callees.

## 2.9.0 - 2026-06-10

* Added PHP 8.5 support.
* Fixed to_number() to parse number strings using the JSON number grammar.
* Fixed reverse() and string slicing to operate on UTF-8 characters rather than bytes.
* Fixed slicing of array-like (ArrayAccess + Countable) values.
* Fixed equality and contains() to use JSON semantics, e.g. 1 == 1.0 is now true.
* Fixed multi-select hashes to end projections, so following tokens apply to the projected list.
* Fixed sort() and sort_by() to compare numbers numerically.
* Changed sort(), sort_by(), max(), min(), max_by() and min_by() to order strings by code point.
* Fixed max_by() and min_by() to error on mixed-type keys instead of returning arbitrary elements.
* Fixed max() returning null or erroring when the first array element is falsy, e.g. max([0, 1]).
* Fixed sum() and join() to return 0 and an empty string respectively for empty arrays.
* Fixed 0.0 to be truthy in filters and logical operators, like every other number.
* Fixed the compiled runtime to apply JMESPath truthiness to || and &&.
* Fixed @(foo), foo[-] and oversized index literals to throw syntax errors.
* Fixed PHP warnings emitted while parsing certain invalid expressions.
* Fixed the caret position in syntax error messages for errors at the end of an expression.
* Fixed map() to error on non-array second arguments instead of returning [].
* Fixed Env::cleanCompileDir() when JP_PHP_COMPILE=on.

## 2.8.0 - 2024-09-04

* Add support for PHP 8.4.

## 2.7.0 - 2023-08-15

* Fixed flattening in arrays starting with null.
* Drop support for HHVM and PHP earlier than 7.2.5.
* Add support for PHP 8.1, 8.2, and 8.3.

## 2.6.0 - 2020-07-31

* Support for PHP 8.0.

## 2.5.0 - 2019-12-30

* Full support for PHP 7.0-7.4.
* Fixed autoloading when run from within vendor folder.
* Full multibyte (UTF-8) string support.

## 2.4.0 - 2016-12-03

* Added support for floats when interpreting data.
* Added a function_exists check to work around redeclaration issues.

## 2.3.0 - 2016-01-05

* Added support for [JEP-9](https://github.com/jmespath/jmespath.site/blob/master/docs/proposals/improved-filters.rst),
  including unary filter expressions, and `&&` filter expressions.
* Fixed various parsing issues, including not removing escaped single quotes
  from raw string literals.
* Added support for the `map` function.
* Fixed several issues with code generation.

## 2.2.0 - 2015-05-27

* Added support for [JEP-12](https://github.com/jmespath/jmespath.site/blob/master/docs/proposals/raw-string-literals.rst)
  and raw string literals (e.g., `'foo'`).

## 2.1.0 - 2014-01-13

* Added `JmesPath\Env::cleanCompileDir()` to delete any previously compiled
  JMESPath expressions.

## 2.0.0 - 2014-01-11

* Moving to a flattened namespace structure.
* Runtimes are now only PHP callables.
* Fixed an error in the way empty JSON literals are parsed so that they now
  return an empty string to match the Python and JavaScript implementations.
* Removed functions from runtimes. Instead there is now a function dispatcher
  class, FnDispatcher, that provides function implementations behind a single
  dispatch function.
* Removed ExprNode in lieu of just using a PHP callable with bound variables.
* Removed debug methods from runtimes and instead into a new Debugger class.
* Heavily cleaned up function argument validation.
* Slice syntax is now properly validated (i.e., colons are followed by the
  appropriate value).
* Lots of code cleanup and performance improvements.
* Added a convenient `JmesPath\search()` function.
* **IMPORTANT**: Relocating the project to https://github.com/jmespath/jmespath.php

## 1.1.1 - 2014-10-08

* Added support for using ArrayAccess and Countable as arrays and objects.

## 1.1.0 - 2014-08-06

* Added the ability to search data returned from json_decode() where JSON
  objects are returned as stdClass objects.
