<?php

namespace Matrix;

/**
 *
 * Autoloader for Matrix classes
 *
 * @package Matrix
 * @copyright  Copyright (c) 2014 Mark Baker (https://github.com/MarkBaker/PHPMatrix)
 * @license    https://opensource.org/licenses/MIT          MIT
 */
class Autoloader
{
    /**
     * Register the Autoloader with SPL
     *
     */
    public static function Register()
    {
        if (function_exists('__autoload')) {
            //    Register any existing autoloader function with SPL, so we don't get any clashes
            spl_autoload_register('__autoload');
        }
        //    Register ourselves with SPL
        return spl_autoload_register(['Matrix\\Autoloader', 'Load']);
    }


    /**
     * Autoload a class identified by name
     *
     * @param    string    $pClassName    Name of the object to load
     */
    public static function Load($pClassName)
    {
        if ((class_exists($pClassName, false)) || (strpos($pClassName, 'Matrix\\') !== 0)) {
            // Either already loaded, or not a Matrix class request
            return false;
        }

        $pClassFilePath = __DIR__ . DIRECTORY_SEPARATOR .
                          'src' . DIRECTORY_SEPARATOR .
                          str_replace(['Matrix\\', '\\'], ['', '/'], $pClassName) .
                          '.php';

        if ((file_exists($pClassFilePath) === false) || (is_readable($pClassFilePath) === false)) {
            // Can't load
            return false;
        }
        require($pClassFilePath);
    }
}
