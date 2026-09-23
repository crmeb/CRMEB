<?php

namespace AlibabaCloud\Credentials\Utils;

use InvalidArgumentException;

/**
 * Class Filter
 *
 * @package AlibabaCloud\Credentials\Utils
 */
class Filter
{

    /**
     * @param $name
     *
     * @codeCoverageIgnore
     * @return string
     */
    public static function credentialName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Name must be a string');
        }

        if ($name === '') {
            throw new InvalidArgumentException('Name cannot be empty');
        }

        return $name;
    }

    /**
     * @param $bearerToken
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function bearerToken($bearerToken)
    {
        if (!is_string($bearerToken)) {
            throw new InvalidArgumentException('bearerToken must be a string');
        }

        if ($bearerToken === '') {
            throw new InvalidArgumentException('bearerToken cannot be empty');
        }

        return $bearerToken;
    }

    /**
     * @param $publicKeyId
     *
     * @return mixed
     */
    public static function publicKeyId($publicKeyId)
    {
        if (!is_string($publicKeyId)) {
            throw new InvalidArgumentException('publicKeyId must be a string');
        }

        if ($publicKeyId === '') {
            throw new InvalidArgumentException('publicKeyId cannot be empty');
        }

        return $publicKeyId;
    }

    /**
     * @param $privateKeyFile
     *
     * @return mixed
     */
    public static function privateKeyFile($privateKeyFile)
    {
        if (!is_string($privateKeyFile)) {
            throw new InvalidArgumentException('privateKeyFile must be a string');
        }

        if ($privateKeyFile === '') {
            throw new InvalidArgumentException('privateKeyFile cannot be empty');
        }

        return $privateKeyFile;
    }

    /**
     * @param string|null $roleName
     */
    public static function roleName($roleName)
    {
        if ($roleName === null) {
            return;
        }

        if (!is_string($roleName)) {
            throw new InvalidArgumentException('roleName must be a string');
        }

        if ($roleName === '') {
            throw new InvalidArgumentException('roleName cannot be empty');
        }
    }

    /**
     * @param boolean|null $disableIMDSv1
     */
    public static function disableIMDSv1($disableIMDSv1)
    {
        if (!is_bool($disableIMDSv1)) {
            throw new InvalidArgumentException('disableIMDSv1 must be a boolean');
        }
    }


    /**
     * @param string|null $roleArn
     */
    public static function roleArn($roleArn)
    {
        if (is_null($roleArn) || $roleArn === '') {
            throw new InvalidArgumentException('roleArn cannot be empty');
        }
    }

    /**
     * @param string|null $roleArn
     */
    public static function oidcProviderArn($oidcProviderArn)
    {
        if (is_null($oidcProviderArn) || $oidcProviderArn === '') {
            throw new InvalidArgumentException('oidcProviderArn cannot be empty');
        }
    }

    /**
     * @param string|null $roleArn
     */
    public static function oidcTokenFilePath($oidcTokenFilePath)
    {
        if (is_null($oidcTokenFilePath) || $oidcTokenFilePath === '') {
            throw new InvalidArgumentException('oidcTokenFilePath cannot be empty');
        }
    }

    /**
     * @param string $accessKeyId
     * @param string $accessKeySecret
     */
    public static function accessKey($accessKeyId, $accessKeySecret)
    {
        if (!is_string($accessKeyId)) {
            throw new InvalidArgumentException('accessKeyId must be a string');
        }

        if ($accessKeyId === '') {
            throw new InvalidArgumentException('accessKeyId cannot be empty');
        }

        if (!is_string($accessKeySecret)) {
            throw new InvalidArgumentException('accessKeySecret must be a string');
        }

        if ($accessKeySecret === '') {
            throw new InvalidArgumentException('accessKeySecret cannot be empty');
        }
    }

    /**
     * @param string $securityToken
     */
    public static function securityToken($securityToken)
    {
        if (!is_string($securityToken)) {
            throw new InvalidArgumentException('securityToken must be a string');
        }

        if ($securityToken === '') {
            throw new InvalidArgumentException('securityToken cannot be empty');
        }
    }

    /**
     * @param int $expiration
     */
    public static function expiration($expiration)
    {
        if (!is_int($expiration)) {
            throw new InvalidArgumentException('expiration must be a int');
        }
    }

    /**
     * @param int $connectTimeout
     * @param int $readTimeout
     */
    public static function timeout($connectTimeout, $readTimeout)
    {
        if (!is_int($connectTimeout)) {
            throw new InvalidArgumentException('connectTimeout must be a int');
        }

        if (!is_int($readTimeout)) {
            throw new InvalidArgumentException('readTimeout must be a int');
        }
    }

    /**
     * @param string|null $credentialsURI
     */
    public static function credentialsURI($credentialsURI)
    {
        if (!is_string($credentialsURI)) {
            throw new InvalidArgumentException('credentialsURI must be a string');
        }

        if ($credentialsURI === '') {
            throw new InvalidArgumentException('credentialsURI cannot be empty');
        }
    }

    /**
     * @param boolean|null $reuseLastProviderEnabled
     */
    public static function reuseLastProviderEnabled($reuseLastProviderEnabled)
    {
        if (!is_bool($reuseLastProviderEnabled)) {
            throw new InvalidArgumentException('reuseLastProviderEnabled must be a boolean');
        }
    }
}
