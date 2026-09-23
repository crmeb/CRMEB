<?php

namespace Alipay\EasySDK\Kernel;

use Alipay\EasySDK\Base\Image\Client as imageClient;
use Alipay\EasySDK\Base\OAuth\Client as oauthClient;
use Alipay\EasySDK\Base\Qrcode\Client as qrcodeClient;
use Alipay\EasySDK\Base\Video\Client as videoClient;
use Alipay\EasySDK\Marketing\OpenLife\Client as openLifeClient;
use Alipay\EasySDK\Marketing\Pass\Client as passClient;
use Alipay\EasySDK\Marketing\TemplateMessage\Client as templateMessageClient;
use Alipay\EasySDK\Member\Identification\Client as identificationClient;
use Alipay\EasySDK\Payment\App\Client as appClient;
use Alipay\EasySDK\Payment\Common\Client as commonClient;
use Alipay\EasySDK\Payment\FaceToFace\Client as faceToFaceClient;
use Alipay\EasySDK\Payment\Huabei\Client as huabeiClient;
use Alipay\EasySDK\Payment\Page\Client as pageClient;
use Alipay\EasySDK\Payment\Wap\Client as wapClient;
use Alipay\EasySDK\Security\TextRisk\Client as textRiskClient;
use Alipay\EasySDK\Util\Generic\Client as genericClient;
use Alipay\EasySDK\Util\AES\Client as aesClient;

class Factory
{
    public $config = null;
    public $kernel = null;
    private static $instance;
    protected static $base;
    protected static $marketing;
    protected static $member;
    protected static $payment;
    protected static $security;
    protected static $util;

    private function __construct($config)
    {
        if (!empty($config->alipayCertPath)) {
            $certEnvironment = new CertEnvironment();
            $certEnvironment->certEnvironment(
                $config->merchantCertPath,
                $config->alipayCertPath,
                $config->alipayRootCertPath
            );
            $config->merchantCertSN = $certEnvironment->getMerchantCertSN();
            $config->alipayRootCertSN = $certEnvironment->getRootCertSN();
            $config->alipayPublicKey = $certEnvironment->getCachedAlipayPublicKey();
        }

        $kernel = new EasySDKKernel($config);
        self::$base = new Base($kernel);
        self::$marketing = new Marketing($kernel);
        self::$member = new Member($kernel);
        self::$payment = new Payment($kernel);
        self::$security = new Security($kernel);
        self::$util = new Util($kernel);
    }

    public static function setOptions($config)
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    public static function base()
    {
        return self::$base;
    }

    public static function marketing()
    {
        return self::$marketing;
    }

    public static function member()
    {
        return self::$member;
    }

    public static function payment()
    {
        return self::$payment;
    }

    public static function security()
    {
        return self::$security;
    }

    public static function util()
    {
        return self::$util;
    }
}


class Base
{
    private $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function image()
    {
        return new imageClient($this->kernel);
    }

    public function oauth()
    {
        return new oauthClient($this->kernel);
    }

    public function qrcode()
    {
        return new qrcodeClient($this->kernel);
    }

    public function video()
    {
        return new videoClient($this->kernel);
    }
}

class Marketing
{
    private $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function openLife()
    {
        return new openLifeClient($this->kernel);
    }

    public function pass()
    {
        return new passClient($this->kernel);
    }

    public function templateMessage()
    {
        return new templateMessageClient($this->kernel);
    }
}

class Member
{
    private $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function identification()
    {
        return new identificationClient($this->kernel);
    }
}

class Payment
{
    private $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function app()
    {
        return new appClient($this->kernel);
    }

    public function common()
    {
        return new commonClient($this->kernel);
    }

    public function faceToFace()
    {
        return new faceToFaceClient($this->kernel);
    }

    public function huabei()
    {
        return new huabeiClient($this->kernel);
    }

    public function page()
    {
        return new pageClient($this->kernel);
    }

    public function wap()
    {
        return new wapClient($this->kernel);
    }
}

class Security
{
    private $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function textRisk()
    {
        return new textRiskClient($this->kernel);
    }
}

class Util
{
    private $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function generic()
    {
        return new genericClient($this->kernel);
    }

    public function aes(){
        return new aesClient($this->kernel);
    }
}

