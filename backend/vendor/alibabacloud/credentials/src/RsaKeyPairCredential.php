<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Providers\RsaKeyPairCredentialsProvider;
use AlibabaCloud\Credentials\Credential\CredentialModel;
use AlibabaCloud\Credentials\Signature\ShaHmac1Signature;
use AlibabaCloud\Credentials\Utils\Filter;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * @deprecated
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
    private $privateKeyFile;

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
        $this->privateKeyFile = $private_key_file;
        $this->config = $config;
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
     * @return AlibabaCloud\Credentials\Providers\Credentials
     * @throws Exception
     * @throws GuzzleException
     */
    protected function getSessionCredential()
    {
        $params = [
            'publicKeyId' => $this->publicKeyId,
            'privateKeyFile' => $this->privateKeyFile,
        ];
        return (new RsaKeyPairCredentialsProvider($params))->getCredentials();
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

    /**
     * @inheritDoc
     */
    public function getCredential()
    {
        $credentials = $this->getSessionCredential();
        return new CredentialModel([
            'accessKeyId' => $credentials->getAccessKeyId(),
            'accessKeySecret' => $credentials->getAccessKeySecret(),
            'securityToken' => $credentials->getSecurityToken(),
            'type' => 'rsa_key_pair',
        ]);
    }
}
