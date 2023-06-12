<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\utils;

use think\exception\ValidateException;

/**
 * Class Rsa
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/5/16
 * @package crmeb\utils
 */
class Rsa
{
    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * 获取证书文件
     * @param $publicKey
     * @param $privateKey
     */
    public function __construct(string $publicKey = 'cert_public_password.key', string $privateKey = 'cert_private_password.key')
    {
        $this->basePath = app()->getRootPath();
        if ($publicKey) {
            $this->publicKey = $this->basePath . $publicKey;
        }
        if ($privateKey) {
            $this->privateKey = $this->basePath . $publicKey;
        }
        if (!is_file($this->publicKey) || !is_file($this->privateKey)) {
            $this->exportOpenSSLFile();
        }
    }

    /**
     * @return false|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/16
     */
    public function getPublicKey()
    {
        if (!is_file($this->publicKey)) {
            $this->exportOpenSSLFile();
        }

        return file_get_contents($this->publicKey);
    }

    /**
     * 生成证书
     * @return bool
     */
    public function exportOpenSSLFile($passwork = null)
    {

        $publicKey = $privateKey = '';
        $dir = app()->getRootPath() . 'runtime/conf';
        $conf = 'openssl.cnf';
        if (!is_dir($dir)) {
            mkdir($dir, 0700);
        }
        if (!file_exists($conf)) {
            touch($dir . '/' . $conf);
        }

        //参数设置
        $config = [
            "digest_alg" => "sha256",
            //字节数    512 1024  2048   4096 等
            "private_key_bits" => 1024,
            "config" => $dir . '/' . $conf,
            //加密类型
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        //创建私钥和公钥
        $res = openssl_pkey_new($config);
        if ($res == false) {
            //创建失败,请检查openssl.cnf文件是否存在
            return false;
        }

        //将密钥导出为PEM编码的字符串，并输出（通过引用传递）。
        openssl_pkey_export($res, $privateKey, $passwork, $config);
        $publicKey = openssl_pkey_get_details($res);
        $publicKey = $publicKey["key"];

        //生成证书
        $createPublicFileRet = file_put_contents($this->publicKey, $publicKey);
        $createPrivateFileRet = file_put_contents($this->privateKey, $privateKey);
        if (!($createPublicFileRet || $createPrivateFileRet)) {
            return false;
        }

        openssl_free_key($res);
        return true;
    }

    /**
     * 数据加密
     * @param string $data
     * @param string|null $passwork
     * @return false|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/16
     */
    function privateEncrypt(string $data, string $passwork = null)
    {
        $encrypted = '';
        $pi_key = openssl_pkey_get_private(file_get_contents($this->privateKey), $passwork);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        //最大允许加密长度为117，得分段加密
        $plainData = str_split($data, 100);//生成密钥位数 1024 bit key
        foreach ($plainData as $chunk) {
            $partialEncrypted = '';
            $encryptionOk = openssl_private_encrypt($chunk, $partialEncrypted, $pi_key);//私钥加密
            if ($encryptionOk === false) {
                return false;
            }
            $encrypted .= $partialEncrypted;
        }

        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }

    /**
     * RSA公钥解密(私钥加密的内容通过公钥可以解密出来)
     * @param string $public_key 公钥
     * @param string $data 私钥加密后的字符串
     * @return string $decrypted 返回解密后的字符串
     * @author mosishu
     */
    function publicDecrypt(string $data)
    {
        $decrypted = '';
        $pu_key = openssl_pkey_get_public(file_get_contents($this->publicKey));//这个函数可用来判断公钥是否是可用的
        $plainData = str_split(base64_decode($data), 128);//生成密钥位数 1024 bit key
        foreach ($plainData as $chunk) {
            $str = '';
            $decryptionOk = openssl_public_decrypt($chunk, $str, $pu_key);//公钥解密
            if ($decryptionOk === false) {
                return false;
            }
            $decrypted .= $str;
        }
        return $decrypted;
    }

    /**
     * 私钥解密
     * @param string $data
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/16
     */
    public function privateDecrypt(string $data)
    {
        if (!is_file($this->privateKey)) {
            $this->exportOpenSSLFile();
        }

        $res = openssl_private_decrypt(base64_decode($data), $decryptedData, file_get_contents($this->privateKey));

        if (false === $res) {
            throw new ValidateException('RSA:解密失败');
        }

        return $decryptedData;
    }

}
