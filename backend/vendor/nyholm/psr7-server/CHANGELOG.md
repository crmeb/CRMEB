# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.2

### Fixed

- `ServerRequestCreator::createStreamFromFile()` should first look if there was an upload error
- Dont crash on numeric header names

## 1.0.1

## Fixed

- PHP8 support

## 1.0.0

- Changed signature of `ServerRequestCreatorInterface::fromArrays()` to allow null values for $post

## 0.4.2

### Fixed

- Support for numerical headers

## 0.4.1

### Fixed

- Support for ´HTTP_X_FORWARDED_PROTO´

## 0.4.0

### Fixed

- Support for Host-header with port-number

## 0.3.0

### Added

- `ServerRequestCreator` is final

### Fixed

- Fallback to an empty Stream if UploadedFileFactory fails.

## 0.2.0

### Changed

- Make sure we use psr/http-factory

## 0.1.2

### Added

- `ServerRequestCreatorInterface`
- `ServerRequestCreator::getHeadersFromServer`

## 0.1.1

### Added

Better testing

## 0.1.0

First release
