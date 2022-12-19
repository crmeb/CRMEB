<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Providers\ChainProvider;
use ReflectionException;
use RuntimeException;

/**
 * Class Credentials
 *
 * @package AlibabaCloud\Credentials
 */
class Credentials
{
    use MockTrait;

    /**
     * @var array|CredentialsInterface[] containers of credentials
     */
    protected static $credentials = [];

    /**
     * Get the credential instance by name.
     *
     * @param string $name
     *
     * @return Credential
     * @throws ReflectionException
     */
    public static function get($name = null)
    {
        if ($name !== null) {
            Filter::credentialName($name);
        } else {
            $name = ChainProvider::getDefaultName();
        }

        self::load();

        if (self::has($name)) {
            return new Credential(self::$credentials[\strtolower($name)]);
        }

        throw new RuntimeException("Credential '$name' not found");
    }

    private static function load()
    {
        if (self::$credentials) {
            return;
        }

        if (ChainProvider::hasCustomChain()) {
            ChainProvider::customProvider(ChainProvider::getDefaultName());
        } else {
            ChainProvider::defaultProvider(ChainProvider::getDefaultName());
        }
    }

    /**
     * Determine whether there is a credential.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function has($name)
    {
        Filter::credentialName($name);

        return isset(self::$credentials[\strtolower($name)]);
    }

    public static function flush()
    {
        self::$credentials = [];
    }

    /**
     * Get all credentials.
     *
     * @return array
     */
    public static function all()
    {
        self::load();

        return self::$credentials;
    }

    /**
     * @param string $name
     * @param array  $credential
     */
    public static function set($name, array $credential)
    {
        Filter::credentialName($name);

        self::$credentials[\strtolower($name)] = \array_change_key_case($credential);
    }
}
