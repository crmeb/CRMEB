<?php
declare(strict_types=1);

namespace Fastknife\Utils;

/**
 * Class AesUtils
 * @package Fastknife\Utils
 */
class AesUtils
{

    /**
     * @param $str
     * @param $secretKey string 只有长度等于16位才能与前端CryptoJS加密一致
     * @return string
     */
    public static function encrypt($str, string $secretKey)
    {
        return base64_encode(openssl_encrypt($str, 'AES-128-ECB', $secretKey, OPENSSL_RAW_DATA));

    }

    /**
     * 解密
     * @param $str
     * @param $secretKey string 只有长度等于16位才能与前端CryptoJS加密一致
     * @return string
     */
    public static function decrypt($str, string $secretKey): string
    {
        $ret = openssl_decrypt(base64_decode($str), 'AES-128-ECB', $secretKey,OPENSSL_RAW_DATA);
        if($ret === false){
            throw new \RuntimeException('请检查密钥是否正确！');
        }
        return $ret;
    }

}
