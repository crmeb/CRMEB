#!/usr/bin/env php
<?php
// The MIT License
//
// Copyright (c) 2011 Iván -DrSlump- Montes
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

// Set up default timezone
date_default_timezone_set('GMT');

// For non pear packaged versions use relative include path
if (strpos('@php_bin@', '@php_bin') === 0) {
    set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'library' . PATH_SEPARATOR . get_include_path());
}

// Disable strict errors for the compiler
error_reporting(error_reporting() & ~E_STRICT);

require_once 'DrSlump/Protobuf.php';

// Setup autoloader
\DrSlump\Protobuf::autoload();

try {
    // Run the cli interface
    \DrSlump\Protobuf\Compiler\Cli::run(__FILE__);
    exit(0);

} catch(Exception $e) {
    fputs(STDERR, (string)$e . PHP_EOL);
    exit(1);
}
