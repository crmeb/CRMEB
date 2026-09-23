<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Utils\Filter;
use AlibabaCloud\Credentials\Utils\Helper;
use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * @internal This class is intended for internal use within the package. 
 * Class DefaultCredentialsProvider
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class DefaultCredentialsProvider implements CredentialsProvider
{

    /**
     * @var array
     */
    private static $defaultProviders = [];

    /**
     * @var bool
     */
    private $reuseLastProviderEnabled;

    /**
     * @var CredentialsProvider
     */
    private $lastUsedCredentialsProvider;

    /**
     * @var array
     */
    private static $customChain = [];

    /**
     * DefaultCredentialsProvider constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->filterReuseLastProviderEnabled($params);
        $this->createDefaultChain();
        Filter::reuseLastProviderEnabled($this->reuseLastProviderEnabled);
    }

    private function filterReuseLastProviderEnabled(array $params)
    {
        $this->reuseLastProviderEnabled = true;
        if (isset($params['reuseLastProviderEnabled'])) {
            $this->reuseLastProviderEnabled = $params['reuseLastProviderEnabled'];
        }
    }

    private function createDefaultChain()
    {
        self::$defaultProviders = [
            new EnvironmentVariableCredentialsProvider(),
        ];
        if (
            Helper::envNotEmpty('ALIBABA_CLOUD_ROLE_ARN')
            && Helper::envNotEmpty('ALIBABA_CLOUD_OIDC_PROVIDER_ARN')
            && Helper::envNotEmpty('ALIBABA_CLOUD_OIDC_TOKEN_FILE')
        ) {
            array_push(
                self::$defaultProviders,
                new OIDCRoleArnCredentialsProvider()
            );
        }
        array_push(
            self::$defaultProviders,
            new CLIProfileCredentialsProvider()
        );
        array_push(
            self::$defaultProviders,
            new ProfileCredentialsProvider()
        );
        array_push(
            self::$defaultProviders,
            new EcsRamRoleCredentialsProvider()
        );
        if (Helper::envNotEmpty('ALIBABA_CLOUD_CREDENTIALS_URI')) {
            array_push(
                self::$defaultProviders,
                new URLCredentialsProvider()
            );
        }
    }

    /**
     * @param CredentialsProvider ...$providers
     */
    public static function set(...$providers)
    {
        if (empty($providers)) {
            throw new InvalidArgumentException('No providers in chain');
        }

        foreach ($providers as $provider) {
            if (!$provider instanceof CredentialsProvider) {
                throw new InvalidArgumentException('Providers must all be CredentialsProvider');
            }
        }

        self::$customChain = $providers;
    }

    /**
     * @return bool
     */
    public static function hasCustomChain()
    {
        return (bool) self::$customChain;
    }

    public static function flush()
    {
        self::$customChain = [];
    }

    /**
     * Get credential.
     *
     * @return Credentials
     * @throws RuntimeException
     */
    public function getCredentials()
    {
        if ($this->reuseLastProviderEnabled && !is_null($this->lastUsedCredentialsProvider)) {
            $credentials = $this->lastUsedCredentialsProvider->getCredentials();
            return new Credentials([
                'accessKeyId' => $credentials->getAccessKeyId(),
                'accessKeySecret' => $credentials->getAccessKeySecret(),
                'securityToken' => $credentials->getSecurityToken(),
                'providerName' => $this->getProviderName() . '/' . $this->lastUsedCredentialsProvider->getProviderName(),
            ]);
        }

        $providerChain = array_merge(
            self::$customChain,
            self::$defaultProviders
        );

        $exceptionMessages = [];

        foreach ($providerChain as $provider) {
            try {
                $credentials = $provider->getCredentials();
                $this->lastUsedCredentialsProvider = $provider;
                return new Credentials([
                    'accessKeyId' => $credentials->getAccessKeyId(),
                    'accessKeySecret' => $credentials->getAccessKeySecret(),
                    'securityToken' => $credentials->getSecurityToken(),
                    'providerName' => $this->getProviderName() . '/' . $provider->getProviderName(),
                ]);
            } catch (Exception $exception) {
                array_push($exceptionMessages, basename(str_replace('\\', '/', get_class($provider))) . ': ' . $exception->getMessage());
            }
        }
        throw new RuntimeException('Unable to load credentials from any of the providers in the chain: ' . implode(', ', $exceptionMessages));

    }

    /**
     * @inheritDoc
     */
    public function getProviderName()
    {
        return "default";
    }
}