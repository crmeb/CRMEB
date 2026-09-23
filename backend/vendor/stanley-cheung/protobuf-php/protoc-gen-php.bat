@echo off
REM  Protobuf-PHP
REM  Copyright (C) 2011 Iván -DrSlump- Montes <drslump@pollinimini.net>
REM
REM  This source file is subject to the MIT license that is bundled
REM  with this package in the file LICENSE.
REM  It is also available through the world-wide-web at this URL:
REM  http://creativecommons.org/licenses/MIT/

if "%PHPBIN%" == "" set PHPBIN=@php_bin@
if not exist "%PHPBIN%" if "%PHP_PEAR_PHP_BIN%" neq "" goto USE_PEAR_PATH
GOTO RUN

:USE_PEAR_PATH
set PHPBIN=%PHP_PEAR_PHP_BIN%

:RUN
"%PHPBIN%" -d display_errors=stderr -d log_errors=On -d error_log=Off "@bin_dir@\protoc-gen-php" %*
