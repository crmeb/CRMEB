<?php

namespace OSS\Tests;

class StsBase
{
    protected $SignatureVersion = "1.0";

    protected $Version = "2015-04-01";

    protected $Timestamp;

    protected $SignatureMethod = "HMAC-SHA1";

    protected $Format = "JSON";

    protected $AccessKeyId;

    protected $SignatureNonce;

    private $Signature;

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __construct()
    {
        $this->Timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $this->SignatureNonce = time().rand(10000,99999);
    }
}
