<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Credentials;
use AlibabaCloud\Credentials\Helper;
use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class ChainProvider
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class ChainProvider
{
    /**
     * @var array
     */
    private static $customChains;

    /**
     * @param callable ...$providers
     */
    public static function set(...$providers)
    {
        if (empty($providers)) {
            throw new InvalidArgumentException('No providers in chain');
        }

        foreach ($providers as $provider) {
            if (!$provider instanceof Closure) {
                throw new InvalidArgumentException('Providers must all be Closures');
            }
        }

        self::$customChains = $providers;
    }

    /**
     * @return bool
     */
    public static function hasCustomChain()
    {
        return (bool)self::$customChains;
    }

    public static function flush()
    {
        self::$customChains = [];
    }

    /**
     * @param string $name
     */
    public static function customProvider($name)
    {
        foreach (self::$customChains as $provider) {
            $provider();

            if (Credentials::has($name)) {
                break;
            }
        }
    }

    /**
     * @param string $name
     */
    public static function defaultProvider($name)
    {
        $providers = [
            self::env(),
            self::ini(),
            self::instance(),
        ];

        foreach ($providers as $provider) {
            $provider();

            if (Credentials::has($name)) {
                break;
            }
        }
    }

    /**
     * @return Closure
     */
    public static function env()
    {
        return static function () {
            $accessKeyId     = Helper::envNotEmpty('ALIBABA_CLOUD_ACCESS_KEY_ID');
            $accessKeySecret = Helper::envNotEmpty('ALIBABA_CLOUD_ACCESS_KEY_SECRET');

            if ($accessKeyId && $accessKeySecret) {
                Credentials::set(
                    self::getDefaultName(),
                    [
                        'type'              => 'access_key',
                        'access_key_id'     => $accessKeyId,
                        'access_key_secret' => $accessKeySecret,
                    ]
                );
            }
        };
    }

    /**
     * @return string
     */
    public static function getDefaultName()
    {
        $name = Helper::envNotEmpty('ALIBABA_CLOUD_PROFILE');

        if ($name) {
            return $name;
        }

        return 'default';
    }

    /**
     * @return Closure
     */
    public static function ini()
    {
        return static function () {
            $filename = Helper::envNotEmpty('ALIBABA_CLOUD_CREDENTIALS_FILE');
            if (!$filename) {
                $filename = self::getDefaultFile();
            }

            if (!Helper::inOpenBasedir($filename)) {
                return;
            }

            if ($filename !== self::getDefaultFile() && (!\is_readable($filename) || !\is_file($filename))) {
                throw new RuntimeException(
                    'Credentials file is not readable: ' . $filename
                );
            }

            $file_array = \parse_ini_file($filename, true);

            if (\is_array($file_array) && !empty($file_array)) {
                foreach (\array_change_key_case($file_array) as $name => $configures) {
                    Credentials::set($name, $configures);
                }
            }
        };
    }

    /**
     * Get the default credential file.
     *
     * @return string
     */
    public static function getDefaultFile()
    {
        return Helper::getHomeDirectory() .
               DIRECTORY_SEPARATOR .
               '.alibabacloud' .
               DIRECTORY_SEPARATOR .
               'credentials';
    }

    /**
     * @return Closure
     */
    public static function instance()
    {
        return static function () {
            $instance = Helper::envNotEmpty('ALIBABA_CLOUD_ECS_METADATA');
            if ($instance) {
                Credentials::set(
                    self::getDefaultName(),
                    [
                        'type'      => 'ecs_ram_role',
                        'role_name' => $instance,
                    ]
                );
            }
        };
    }
}
