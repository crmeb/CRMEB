<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Providers\RamRoleArnProvider;
use AlibabaCloud\Credentials\Signature\ShaHmac1Signature;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Use the AssumeRole of the RAM account to complete  the authentication.
 */
class RamRoleArnCredential implements CredentialsInterface
{

    /**
     * @var string
     */
    private $accessKeyId;

    /**
     * @var string
     */
    private $accessKeySecret;

    /**
     * @var string
     */
    private $roleArn;

    /**
     * @var string
     */
    private $roleSessionName;

    /**
     * @var string
     */
    private $policy;

    /**
     * @var array
     */
    private $config;

    /**
     * RamRoleArnCredential constructor.
     *
     * @param array $credential
     * @param array $config
     */
    public function __construct(array $credential = [], array $config = [])
    {
        $this->filterParameters($credential);
        $this->filterPolicy($credential);

        Filter::accessKey($credential['access_key_id'], $credential['access_key_secret']);

        $this->config          = $config;
        $this->accessKeyId     = $credential['access_key_id'];
        $this->accessKeySecret = $credential['access_key_secret'];
        $this->roleArn         = $credential['role_arn'];
        $this->roleSessionName = $credential['role_session_name'];
    }

    /**
     * @param array $credential
     */
    private function filterParameters(array $credential)
    {
        if (!isset($credential['access_key_id'])) {
            throw new InvalidArgumentException('Missing required access_key_id option in config for ram_role_arn');
        }

        if (!isset($credential['access_key_secret'])) {
            throw new InvalidArgumentException('Missing required access_key_secret option in config for ram_role_arn');
        }

        if (!isset($credential['role_arn'])) {
            throw new InvalidArgumentException('Missing required role_arn option in config for ram_role_arn');
        }

        if (!isset($credential['role_session_name'])) {
            throw new InvalidArgumentException('Missing required role_session_name option in config for ram_role_arn');
        }
    }

    /**
     * @param array $credential
     */
    private function filterPolicy(array $credential)
    {
        if (isset($credential['policy'])) {
            if (is_string($credential['policy'])) {
                $this->policy = $credential['policy'];
            }

            if (is_array($credential['policy'])) {
                $this->policy = json_encode($credential['policy']);
            }
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getRoleArn()
    {
        return $this->roleArn;
    }

    /**
     * @return string
     */
    public function getRoleSessionName()
    {
        return $this->roleSessionName;
    }

    /**
     * @return string
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "$this->accessKeyId#$this->accessKeySecret#$this->roleArn#$this->roleSessionName";
    }

    /**
     * @return ShaHmac1Signature
     */
    public function getSignature()
    {
        return new ShaHmac1Signature();
    }

    /**
     * @return string
     */
    public function getOriginalAccessKeyId()
    {
        return $this->accessKeyId;
    }

    /**
     * @return string
     */
    public function getOriginalAccessKeySecret()
    {
        return $this->accessKeySecret;
    }

    /**
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getAccessKeyId()
    {
        return $this->getSessionCredential()->getAccessKeyId();
    }

    /**
     * @return StsCredential
     * @throws Exception
     * @throws GuzzleException
     */
    protected function getSessionCredential()
    {
        return (new RamRoleArnProvider($this))->get();
    }

    /**
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getAccessKeySecret()
    {
        return $this->getSessionCredential()->getAccessKeySecret();
    }

    /**
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getSecurityToken()
    {
        return $this->getSessionCredential()->getSecurityToken();
    }

    /**
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function getExpiration()
    {
        return $this->getSessionCredential()->getExpiration();
    }
}
