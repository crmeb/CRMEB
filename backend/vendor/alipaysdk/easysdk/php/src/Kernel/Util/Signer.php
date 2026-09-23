<?php

namespace Alipay\EasySDK\Kernel\Util;

class Signer
{
    /**
     * @param $content        string 待签名的内容
     * @param $privateKeyPem  string 私钥
     * @return string         签名值的Base64串
     */
    public function sign($content, $privateKeyPem)
    {
        $priKey = $privateKeyPem;
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        openssl_sign($content, $sign, $res, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * @param $content        string 待验签的内容
     * @param $sign           string 签名值的Base64串
     * @param $publicKeyPem   string 支付宝公钥
     * @return bool           true：验证成功；false：验证失败
     */
    public function verify($content, $sign, $publicKeyPem)
    {
        $pubKey = $publicKeyPem;
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值
        $result = FALSE;
        $result = (openssl_verify($content, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1);
        return $result;
    }

    public function verifyParams($parameters, $publicKey)
    {
        $sign = $parameters['sign'];
        $content = $this->getSignContent($parameters);
        return $this->verify($content, $sign, $publicKey);
    }

    public function getSignContent($params)
    {
        ksort($params);
        unset($params['sign']);
        unset($params['sign_type']);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }
}