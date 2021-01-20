<?php

namespace Songshenzong\Support;

use Stringy\Stringy;

/**
 * Class OS
 *
 * @package Songshenzong\Support
 */
class OS
{
    /**
     * @param      $filename
     *
     * @return bool
     */
    public static function inOpenBasedir($filename)
    {
        $open_basedir = ini_get('open_basedir');
        if (!$open_basedir) {
            return true;
        }

        $dirs = explode(PATH_SEPARATOR, $open_basedir);

        return empty($dirs) || self::inDir($filename, $dirs);
    }

    /**
     * @param string $filename
     * @param array  $dirs
     *
     * @return bool
     */
    public static function inDir($filename, array $dirs)
    {
        foreach ($dirs as $dir) {
            if (!Stringy::create($dir)->endsWith(DIRECTORY_SEPARATOR)) {
                $dir .= DIRECTORY_SEPARATOR;
            }

            if (0 === strpos($filename, $dir)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function isWindows()
    {
        return PATH_SEPARATOR === ';';
    }

    /**
     * Gets the environment's HOME directory.
     *
     * @return null|string
     */
    public static function getHomeDirectory()
    {
        if (getenv('HOME')) {
            return getenv('HOME');
        }

        return (getenv('HOMEDRIVE') && getenv('HOMEPATH'))
            ? getenv('HOMEDRIVE') . getenv('HOMEPATH')
            : null;
    }
}
