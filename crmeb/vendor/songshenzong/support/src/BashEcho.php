<?php

namespace Songshenzong\Support;

/**
 * Class BashEcho
 *
 * @package Songshenzong\Support
 */
class BashEcho
{

    /**
     * @param string $string
     */
    public static function echoRed($string)
    {
        $cmd = "echo -ne \"\033[31m" . $string . " \033[0m\n\"";
        $a   = exec($cmd);
        print  $a . PHP_EOL;
    }

    /**
     * @param string $string
     */
    public static function echoCyan($string)
    {
        $cmd = "printf \"\033[35m" . $string . "\033[0m\n\"";
        $a   = exec($cmd);
        print $a . PHP_EOL;
    }

    /**
     * @param string $string
     */
    public static function echoGreen($string)
    {
        $cmd = "printf \"\033[32m" . $string . "\033[0m\n\"";
        $a   = exec($cmd);
        print $a . PHP_EOL;
    }

    /**
     * @param string $string
     */
    public static function echoBrown($string)
    {
        $cmd = "printf \"\033[33m" . $string . "\033[0m\n\"";
        $a   = exec($cmd);
        print $a . PHP_EOL;
    }

    /**
     * @param string $string
     */
    public static function echoBlue($string)
    {
        $cmd = "printf \"\033[34m" . $string . "\033[0m\n\"";
        $a   = exec($cmd);
        print $a . PHP_EOL;
    }

    /**
     * @param string $string
     */
    public static function echoPurple($string)
    {
        $cmd = "printf \"\033[35m" . $string . "\033[0m\n\"";
        $a   = exec($cmd);
        print  $a . PHP_EOL;
    }
}
