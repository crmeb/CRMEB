<?php

use Songshenzong\Support\Strings;

if (!function_exists('strings')) {
    /**
     * @return Strings
     */
    function strings()
    {
        return new Strings;
    }
}
