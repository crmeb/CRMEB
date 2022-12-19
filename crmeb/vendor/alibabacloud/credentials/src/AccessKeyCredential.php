<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Signature\ShaHmac1Signature;

/**
 * Use the AccessKey to complete the authentication.
 */
class AccessKeyCredential implements CredentialsInterface
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
     * AccessKeyCredential constructor.
     *
     * @param string $access_key_id     Access key ID
     * @param string $access_key_secret Access Key Secret
     */
    public function __construct($access_key_id, $access_key_secret)
    {
        Filter::accessKey($access_key_id, $access_key_secret);

        $this->accessKeyId     = $access_key_id;
        $this->accessKeySecret = $access_key_secret;
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
    public function __toString()
    {
        return "$this->accessKeyId#$this->accessKeySecret";
    }

    /**
     * @return ShaHmac1Signature
     */
    public function getSignature()
    {
        return new ShaHmac1Signature();
    }

    public function getSecurityToken()
    {
        return '';
    }
}
