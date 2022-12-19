<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Signature\ShaHmac1Signature;

/**
 * Use the STS Token to complete the authentication.
 */
class StsCredential implements CredentialsInterface
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
    private $securityToken;

    /**
     * @var int
     */
    private $expiration;

    /**
     * StsCredential constructor.
     *
     * @param string $access_key_id     Access key ID
     * @param string $access_key_secret Access Key Secret
     * @param int    $expiration
     * @param string $security_token    Security Token
     */
    public function __construct($access_key_id, $access_key_secret, $expiration, $security_token = '')
    {
        Filter::accessKey($access_key_id, $access_key_secret);
        Filter::expiration($expiration);
        $this->accessKeyId     = $access_key_id;
        $this->accessKeySecret = $access_key_secret;
        $this->expiration      = $expiration;
        $this->securityToken   = $security_token;
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @return string
     */
    public function getAccessKeyId()
    {
        return $this->accessKeyId;
    }

    /**
     * @return string
     */
    public function getAccessKeySecret()
    {
        return $this->accessKeySecret;
    }

    /**
     * @return string
     */
    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "$this->accessKeyId#$this->accessKeySecret#$this->securityToken";
    }

    /**
     * @return ShaHmac1Signature
     */
    public function getSignature()
    {
        return new ShaHmac1Signature();
    }
}
