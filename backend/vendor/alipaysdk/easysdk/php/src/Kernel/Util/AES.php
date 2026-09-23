<?php


namespace Alipay\EasySDK\Kernel\Util;


class AES
{
    /**
     * AES加密
     *
     * @param $plainText  String 明文
     * @param $key        String 对称密钥
     * @return string
     * @throws \Exception
     */
    public function aesEncrypt($plainText, $key)
    {
        try {
            if(strlen($key) == 0){
                throw new \Exception("AES加密失败，plainText=".$plainText."，AES密钥为空。");
            }
            //AES, 128 模式加密数据 CBC
            $screct_key = base64_decode($key);
            $str = trim($plainText);
            $str = $this->addPKCS7Padding($str);
            $iv = str_repeat("\0", 16);
            $encrypt_str = openssl_encrypt($str, 'aes-128-cbc', $screct_key, OPENSSL_NO_PADDING, $iv);
            return base64_encode($encrypt_str);
        } catch (\Exception $e) {
            throw new \Exception("AES加密失败，plainText=".$plainText."，keySize=".strlen($key)."。".$e->getMessage());
        }
    }


    /**
     * AES解密
     *
     * @param $cipherText String 密文
     * @param $key        String 对称密钥
     * @return false|string
     * @throws \Exception
     */
    public function aesDecrypt($cipherText, $key)
    {
        try{
            if(strlen($key) == 0){
                throw new \Exception("AES加密失败，plainText=".$cipherText."，AES密钥为空。");
            }
            //AES, 128 模式加密数据 CBC
            $str = base64_decode($cipherText);
            $screct_key = base64_decode($key);
            $iv = str_repeat("\0", 16);
            $decrypt_str = openssl_decrypt($str, 'aes-128-cbc', $screct_key, OPENSSL_NO_PADDING, $iv);
            $decrypt_str = $this->stripPKSC7Padding($decrypt_str);
            return $decrypt_str;
        }catch (\Exception $e){
            throw new \Exception("AES解密失败，cipherText=".$cipherText."，keySize=".strlen($key)."。".$e->getMessage());
        }
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    private function addPKCS7Padding($source)
    {
        $source = trim($source);
        $block = 16;

        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }

    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    private function stripPKSC7Padding($source)
    {
        $char = substr($source, -1);
        $num = ord($char);
        if ($num == 62) return $source;
        $source = substr($source, 0, -$num);
        return $source;
    }

}