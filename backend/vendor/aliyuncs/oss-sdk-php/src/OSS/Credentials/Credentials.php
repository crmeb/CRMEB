<?php


namespace OSS\Credentials;

use OSS\Core\OssException;

/**
 * Basic implementation of the OSS Credentials that allows callers to
 * pass in the OSS Access Key and OSS Secret Access Key in the constructor.
 */
class Credentials
{
    private $key;
    private $secret;
    private $token;

    /**
     * Constructor a new BasicOSSCredentials object, with the specified OSS
     * access key and OSS secret key
     *
     * @param string $key     OSS access key ID
     * @param string $secret  OSS secret access key
     * @param string $token   Security token to use
     */
    public function __construct($key, $secret, $token = null)
    {
        if (empty($key)) {
            throw new OssException("access key id is empty");
        }
        if (empty($secret)) {
            throw new OssException("access key secret is empty");
        }
        $this->key = trim($key);
        $this->secret = trim($secret);
        $this->token = $token;
    }


    /**
     * @return string
     */
    public function getAccessKeyId()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getAccessKeySecret()
    {
        return $this->secret;
    }

    /**
     * @return string|null
     */
    public function getSecurityToken()
    {
        return $this->token;
    }
}
