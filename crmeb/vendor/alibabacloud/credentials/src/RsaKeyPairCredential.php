<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Providers\RsaKeyPairProvider;
use AlibabaCloud\Credentials\Signature\ShaHmac1Signature;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Use the RSA key pair to complete the authentication (supported only on Japanese site)
 */
class RsaKeyPairCredential implements CredentialsInterface
{

    /**
     * @var string
     */
    private $publicKeyId;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var array
     */
    private $config;

    /**
     * RsaKeyPairCredential constructor.
     *
     * @param string $public_key_id
     * @param string $private_key_file
     * @param array  $config
     */
    public function __construct($public_key_id, $private_key_file, array $config = [])
    {
        Filter::publicKeyId($public_key_id);
        Filter::privateKeyFile($private_key_file);

        $this->publicKeyId = $public_key_id;
        $this->config      = $config;
        try {
            $this->privateKey = file_get_contents($private_key_file);
        } catch (Exception $exception) {
            throw new InvalidArgumentException($exception->getMessage());
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
    public function getOriginalAccessKeyId()
    {
        return $this->getPublicKeyId();
    }

    /**
     * @return string
     */
    public function getPublicKeyId()
    {
        return $this->publicKeyId;
    }

    /**
     * @return string
     */
    public function getOriginalAccessKeySecret()
    {
        return $this->getPrivateKey();
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "publicKeyId#$this->publicKeyId";
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
        return (new RsaKeyPairProvider($this))->get();
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
     * @return int
     * @throws Exception
     * @throws GuzzleException
     */
    public function getExpiration()
    {
        return $this->getSessionCredential()->getExpiration();
    }
}
