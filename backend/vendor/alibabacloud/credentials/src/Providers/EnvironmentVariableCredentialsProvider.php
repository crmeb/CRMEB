<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Utils\Helper;
use InvalidArgumentException;

/**
 * @internal This class is intended for internal use within the package. 
 * Class EnvironmentVariableCredentialsProvider
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class EnvironmentVariableCredentialsProvider implements CredentialsProvider
{
    /**
     * EnvironmentVariableCredentialsProvider constructor.
     */
    public function __construct() {}

    /**
     * Get credential.
     *
     * @return Credentials
     * @throws InvalidArgumentException
     */
    public function getCredentials()
    {
        if (Helper::envNotEmpty('ALIBABA_CLOUD_ACCESS_KEY_ID')) {
            $accessKeyId = Helper::env('ALIBABA_CLOUD_ACCESS_KEY_ID');
        } else {
            throw new InvalidArgumentException('Access key ID must be specified via environment variable (ALIBABA_CLOUD_ACCESS_KEY_ID)');
        }

        if (Helper::envNotEmpty('ALIBABA_CLOUD_ACCESS_KEY_SECRET')) {
            $accessKeySecret = Helper::env('ALIBABA_CLOUD_ACCESS_KEY_SECRET');
        } else {
            throw new InvalidArgumentException('Access key Secret must be specified via environment variable (ALIBABA_CLOUD_ACCESS_KEY_SECRET)');
        }

        if (Helper::envNotEmpty('ALIBABA_CLOUD_SECURITY_TOKEN')) {
            $securityToken = Helper::env('ALIBABA_CLOUD_SECURITY_TOKEN');
            return new Credentials([
                'accessKeyId' => $accessKeyId,
                'accessKeySecret' => $accessKeySecret,
                'securityToken' => $securityToken,
                'providerName' => $this->getProviderName(),
            ]);
        }

        return new Credentials([
            'accessKeyId' => $accessKeyId,
            'accessKeySecret' => $accessKeySecret,
            'providerName' => $this->getProviderName(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getProviderName()
    {
        return "env";
    }
}
