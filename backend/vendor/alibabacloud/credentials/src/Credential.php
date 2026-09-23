<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Credential\Config;
use AlibabaCloud\Credentials\Credential\CredentialModel;
use AlibabaCloud\Credentials\Providers\DefaultCredentialsProvider;
use AlibabaCloud\Credentials\Providers\EcsRamRoleCredentialsProvider;
use AlibabaCloud\Credentials\Providers\OIDCRoleArnCredentialsProvider;
use AlibabaCloud\Credentials\Providers\RamRoleArnCredentialsProvider;
use AlibabaCloud\Credentials\Providers\RsaKeyPairCredentialsProvider;
use AlibabaCloud\Credentials\Providers\StaticAKCredentialsProvider;
use AlibabaCloud\Credentials\Providers\StaticSTSCredentialsProvider;
use AlibabaCloud\Credentials\Providers\URLCredentialsProvider;
use AlibabaCloud\Credentials\Utils\Helper;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class Credential
 *
 * @package AlibabaCloud\Credentials
 *
 */
class Credential
{

    /**
     * Version of the Client
     */
    const VERSION = '1.1.5';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CredentialsInterface
     */
    protected $credential;

    /**
     * Credential constructor.
     *
     * @param array|Config $config
     */
    public function __construct($config = [])
    {
        if (\is_array($config)) {
            if (empty($config)) {
                $this->config = null;
            } else {
                $this->config = new Config($this->parseConfig($config));
            }
        } else {
            $this->config = $config;
        }
        $this->credential = $this->getCredentials($this->config);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function parseConfig($config)
    {
        $res = [];
        foreach (\array_change_key_case($config) as $key => $value) {
            $res[Helper::snakeToCamelCase($key)] = $value;
        }
        return $res;
    }



    /**
     * Credentials getter.
     *
     * @param Config $config
     * @return CredentialsInterface
     *
     */
    private function getCredentials($config)
    {
        if (is_null($config)) {
            return new CredentialsProviderWrap('default', new DefaultCredentialsProvider());
        }
        switch ($config->type) {
            case 'access_key':
                $provider = new StaticAKCredentialsProvider([
                    'accessKeyId' => $config->accessKeyId,
                    'accessKeySecret' => $config->accessKeySecret,
                ]);
                return new CredentialsProviderWrap('access_key', $provider);
            case 'sts':
                $provider = new StaticSTSCredentialsProvider([
                    'accessKeyId' => $config->accessKeyId,
                    'accessKeySecret' => $config->accessKeySecret,
                    'securityToken' => $config->securityToken,
                ]);
                return new CredentialsProviderWrap('sts', $provider);
            case 'bearer':
                return new BearerTokenCredential($config->bearerToken);
            case 'ram_role_arn':
                if (!is_null($config->securityToken) && $config->securityToken !== '') {
                    $innerProvider = new StaticSTSCredentialsProvider([
                        'accessKeyId' => $config->accessKeyId,
                        'accessKeySecret' => $config->accessKeySecret,
                        'securityToken' => $config->securityToken,
                    ]);
                } else {
                    $innerProvider = new StaticAKCredentialsProvider([
                        'accessKeyId' => $config->accessKeyId,
                        'accessKeySecret' => $config->accessKeySecret,
                    ]);
                }
                $provider = new RamRoleArnCredentialsProvider([
                    'credentialsProvider' => $innerProvider,
                    'roleArn' => $config->roleArn,
                    'roleSessionName' => $config->roleSessionName,
                    'policy' => $config->policy,
                    'durationSeconds' => $config->roleSessionExpiration,
                    'externalId' => $config->externalId,
                    'stsEndpoint' => $config->STSEndpoint,
                ], [
                    'connectTimeout' => $config->connectTimeout,
                    'readTimeout' => $config->readTimeout,
                ]);
                return new CredentialsProviderWrap('ram_role_arn', $provider);
            case 'rsa_key_pair':
                $provider = new RsaKeyPairCredentialsProvider([
                    'publicKeyId' => $config->publicKeyId,
                    'privateKeyFile' => $config->privateKeyFile,
                    'durationSeconds' => $config->roleSessionExpiration,
                    'stsEndpoint' => $config->STSEndpoint,
                ], [
                    'connectTimeout' => $config->connectTimeout,
                    'readTimeout' => $config->readTimeout,
                ]);
                return new CredentialsProviderWrap('rsa_key_pair', $provider);
            case 'ecs_ram_role':
                $provider = new EcsRamRoleCredentialsProvider([
                    'roleName' => $config->roleName,
                    'disableIMDSv1' => $config->disableIMDSv1,
                ], [
                    'connectTimeout' => $config->connectTimeout,
                    'readTimeout' => $config->readTimeout,
                ]);
                return new CredentialsProviderWrap('ecs_ram_role', $provider);
            case 'oidc_role_arn':
                $provider = new OIDCRoleArnCredentialsProvider([
                    'roleArn' => $config->roleArn,
                    'oidcProviderArn' => $config->oidcProviderArn,
                    'oidcTokenFilePath' => $config->oidcTokenFilePath,
                    'roleSessionName' => $config->roleSessionName,
                    'policy' => $config->policy,
                    'durationSeconds' => $config->roleSessionExpiration,
                    'stsEndpoint' => $config->STSEndpoint,
                ], [
                    'connectTimeout' => $config->connectTimeout,
                    'readTimeout' => $config->readTimeout,
                ]);
                return new CredentialsProviderWrap('oidc_role_arn', $provider);
            case "credentials_uri":
                $provider = new URLCredentialsProvider([
                    'credentialsURI' => $config->credentialsURI,
                ], [
                    'connectTimeout' => $config->connectTimeout,
                    'readTimeout' => $config->readTimeout,
                ]);
                return new CredentialsProviderWrap('credentials_uri', $provider);
            default:
                throw new InvalidArgumentException('Unsupported credential type option: ' . $config->type . ', support: access_key, sts, bearer, ecs_ram_role, ram_role_arn, rsa_key_pair, oidc_role_arn, credentials_uri');
        }
    }

    /**
     * @return CredentialModel
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function getCredential()
    {
        return $this->credential->getCredential();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config->toMap();
    }

    /**
     * @deprecated use getCredential() instead
     *
     * @return string
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function getType()
    {
        return $this->credential->getCredential()->getType();
    }

    /**
     * @deprecated use getCredential() instead
     * 
     * @return string
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function getAccessKeyId()
    {
        return $this->credential->getCredential()->getAccessKeyId();
    }

    /**
     * @deprecated use getCredential() instead
     * 
     * @return string
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function getAccessKeySecret()
    {
        return $this->credential->getCredential()->getAccessKeySecret();
    }

    /**
     * @deprecated use getCredential() instead
     * 
     * @return string
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function getSecurityToken()
    {
        return $this->credential->getCredential()->getSecurityToken();
    }

    /**
     * @deprecated use getCredential() instead
     * 
     * @return string
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function getBearerToken()
    {
        return $this->credential->getCredential()->getBearerToken();
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->credential->$name($arguments);
    }
}
