<?php

namespace cweagans\Composer\Downloader\Exception;

use Exception;

class HashMismatchException extends Exception
{
    public function __construct($url, $hash, $expected)
    {
        parent::__construct("Hash mismatch for patch downloaded from $url. Got $hash; expected $expected", 0, null);
    }
}
