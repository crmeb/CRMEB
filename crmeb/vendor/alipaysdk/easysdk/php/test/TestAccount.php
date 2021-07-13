<?php


namespace Alipay\EasySDK\Test;


use Alipay\EasySDK\Kernel\Config;

class TestAccount
{
    public function getTestAccount()
    {
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->appId = '<-- 请填写您的AppId，例如：2019022663440152 -->';
        $options->signType = 'RSA2';
        $options->alipayPublicKey = '<-- 请填写您的支付宝公钥，例如：MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAumX1EaLM4ddn1Pia4SxTRb62aVYxU8I2mHMqrcpQU6F01mIO/DjY7R4xUWcLi0I2oH/BK/WhckEDCFsGrT7mO+JX8K4sfaWZx1aDGs0m25wOCNjp+DCVBXotXSCurqgGI/9UrY+QydYDnsl4jB65M3p8VilF93MfS01omEDjUW+1MM4o3FP0khmcKsoHnYGs21btEeh0LK1gnnTDlou6Jwv3Ew36CbCNY2cYkuyPAW0j47XqzhWJ7awAx60fwgNBq6ZOEPJnODqH20TAdTLNxPSl4qGxamjBO+RuInBy+Bc2hFHq3pNv6hTAfktggRKkKzDlDEUwgSLE7d2eL7P6rwIDAQAB -->';
        $options->merchantPrivateKey = $this->getPrivateKey($options->appId);
        return $options;
    }

    public function getTestCertAccount()
    {
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->appId = '<-- 请填写您的AppId，例如：2019051064521003 -->';
        $options->signType = 'RSA2';
        $options->alipayCertPath = '<-- 请填写您的支付宝公钥证书文件路径，例如：dirname(__FILE__) . "/resources/fixture/alipayCertPublicKey_RSA2.crt" -->';
        $options->alipayRootCertPath = '<-- 请填写您的支付宝根证书文件路径，例如：dirname(__FILE__) . "/resources/fixture/alipayRootCert.crt" -->';
        $options->merchantCertPath = '<-- 请填写您的应用公钥证书文件路径，例如：dirname(__FILE__) . "/resources/fixture/appCertPublicKey_2019051064521003.crt" -->';
        $options->merchantPrivateKey = $this->getPrivateKey($options->appId);
        return $options;
    }

    private function getPrivateKey($appId)
    {
        $filePath = dirname(__FILE__) . '/resources/fixture/privateKey.json';
        $stream = fopen($filePath, 'r');
        fwrite($stream, '$filePath');
        $result = json_decode(stream_get_contents($stream));
        return $result->$appId;
    }
}