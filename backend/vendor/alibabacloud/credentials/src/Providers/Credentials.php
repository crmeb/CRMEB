<?php

namespace AlibabaCloud\Credentials\Providers;

/**
 * @internal This class is intended for internal use within the package. 
 * Class Credentials
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class Credentials
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
     * @var int
     */
    private $providerName;

    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $k => $v) {
                $this->{$k} = $v;
            }
        }
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
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }
}
