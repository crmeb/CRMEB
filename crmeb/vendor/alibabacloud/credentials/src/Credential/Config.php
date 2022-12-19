<?php

namespace AlibabaCloud\Credentials\Credential;

class Config
{
    /**
     * @var string
     */
    public $type = 'default';

    public $accessKeyId = "";

    public $accessKeySecret = "";

    public $securityToken = "";

    public $bearerToken = "";

    public $roleName = "";

    public $roleArn = "";

    public $roleSessionName = "";

    public $host = "";

    public $publicKeyId = "";

    public $privateKeyFile = "";

    public $readTimeout = 0;

    public $connectTimeout = 0;

    public $certFile = "";

    public $certPassword = "";

    public $proxy = "";

    public $expiration = 0;

    public function __construct($config)
    {
        foreach ($config as $k => $v) {
            $this->{$k} = $v;
        }
    }
}
