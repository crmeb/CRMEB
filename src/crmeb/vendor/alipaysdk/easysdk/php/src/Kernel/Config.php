<?php

namespace Alipay\EasySDK\Kernel;

use AlibabaCloud\Tea\Model;

class Config extends Model
{
	public $protocol;
	public $gatewayHost;
	public $appId;
	public $signType;
	public $alipayPublicKey;
	public $merchantPrivateKey;
	public $merchantCertPath;
	public $alipayCertPath;
	public $alipayRootCertPath;
    public $merchantCertSN;
    public $alipayCertSN;
    public $alipayRootCertSN;
    public $notifyUrl;
    public $encryptKey;

}
