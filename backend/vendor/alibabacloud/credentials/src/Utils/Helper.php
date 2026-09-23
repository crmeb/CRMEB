<?php

namespace AlibabaCloud\Credentials\Utils;

use AlibabaCloud\Credentials\Credential;
use org\bovigo\vfs\vfsStream;
use Closure;

/**
 * Class Helper
 *
 * @package AlibabaCloud\Credentials\Utils
 */
class Helper
{
    /**
     * @param array $arrays
     *
     * @return array
     */
    public static function merge(array $arrays)
    {
        $result = [];
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_int($key)) {
                    $result[] = $value;
                    continue;
                }

                if (isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::merge(
                        [$result[$key], $value]
                    );
                    continue;
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }

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
        if (0 === strpos($filename, vfsStream::SCHEME)) {
            // 虚拟文件忽略
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
            if ($dir[strlen($dir) - 1] !== DIRECTORY_SEPARATOR) {
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
     * @param $key
     *
     * @return bool|mixed
     */
    public static function envNotEmpty($key)
    {
        $value = self::env($key, false);
        if ($value) {
            return $value;
        }

        return false;
    }

    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return self::value($default);
        }

        if (self::envSubstr($value)) {
            return substr($value, 1, -1);
        }

        return self::envConversion($value);
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function envSubstr($value)
    {
        return ($valueLength = strlen($value)) > 1
            && strpos($value, '"') === 0
            && $value[$valueLength - 1] === '"';
    }

    /**
     * @param $value
     *
     * @return bool|string|null
     */
    public static function envConversion($value)
    {
        $key = strtolower($value);

        if ($key === 'null' || $key === '(null)') {
            return null;
        }

        $list = [
            'true'    => true,
            '(true)'  => true,
            'false'   => false,
            '(false)' => false,
            'empty'   => '',
            '(empty)' => '',
        ];

        return isset($list[$key]) ? $list[$key] : $value;
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

    /**
     * @param mixed ...$parameters
     *
     * @codeCoverageIgnore
     */
    public static function dd(...$parameters)
    {
        dump(...$parameters);
        exit;
    }

    /**
     * Snake to camel case.
     *
     * @param string $str
     *
     * @return string
     */
    public static function snakeToCamelCase($str)
    {
        $components = explode('_', $str);
        $camelCaseStr = $components[0];
        for ($i = 1; $i < count($components); $i++) {
            $camelCaseStr .= ucfirst($components[$i]);
        }
        return $camelCaseStr;
    }

    /**
     * Get user agent.
     *
     * @param string $userAgent
     *
     * @return string
     */
    public static function getUserAgent()
    {
        return sprintf('AlibabaCloud (%s; %s) PHP/%s Credentials/%s TeaDSL/1', PHP_OS, \PHP_SAPI, PHP_VERSION, Credential::VERSION);
    }

    /**
     * @param array $arrays
     * @param string $key
     *
     * @return mix
     */
    public static function unsetReturnNull(array $arrays, $key)
    {
        if(isset($arrays[$key])) {
            return $arrays[$key];
        }
        return null;
    }
}
