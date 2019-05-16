<?php
namespace app\core\util;

use app\core\model\routine\Token;
use service\CacheService;

class TokenService
{
    //加密分割key
    private static $KEY='fb';
    //10位加密key
    private static $sKey='crmeb12345';
    //相隔付
    private static $seperater = "{&&}";
    //是否开启辅助验证
    private static $isAuxiliary=false;
    //加密方式 为空为普通加密
    private static $encryptType='DES';
    //实例化类
    private static $instance=null;
    //构造单例模式
    private function __construct()
    {

    }
    /*
     * 初始化类
     * @return object
     * */
    public static function instance()
    {
        if(is_null(self::$instance)) self::$instance=new self();
        return self::$instance;
    }

    /*
     * 设置加密方式
     * @param $encryptType 加密类型
     * */
    public function setEncryptType($encryptType='DES')
    {
        self::$encryptType=$encryptType;
    }
    /*
     * 验证token
     * @param string $token token
     * @param boolean $isSkip 是否跳过验证
     * @return Boolean | array
     * */
    public static function checkToken($token,$isSkip=false)
    {
        self::InitSkey();
        //跳过验证
        if($isSkip && !$token) return true;
        //解密token
        if(strtolower(self::$encryptType)==='des'){
            $data=explode(self::$seperater,self::DesDecrypt($token));
        }else{
            $data=explode(self::$seperater,self::deCode($token));
        }
        //验证是否为数组 数组length为6
        if(is_array($data) && count($data) == 7){
            //第一位不能为空
            if(empty($data[0])) return false;
            //验证公钥是否被篡改
            if($data[3]!=self::$sKey) return false;
            //验证token是否过期
            if((int)$data[5]<time()) return false;
            //验证当前用户加密的随机字符串
            if(self::$isAuxiliary===true) if(!Token::checkRandString($data[0],$data[2])) return false;
            //返回第一位和二位数据
            return [$data[0],$data[1]];
        }
        return false;
    }

    /*
      * 获取token
      * @param string $string 加密字符串
      * @param string $openid 加密openid
      * @param int $valid_peroid token过期时间默认5天 259200
      * @param string $randstring 随机字符串提供辅助验证token
      * @return token
      * */
    public static function getToken($string=1,$openid='0',$randstring='0',$valid_peroid=259200)
    {
        self::InitSkey();
        if(self::$isAuxiliary===true) {
            $randstring = self::createNonceStr();
            $res = Token::SetRandString($string, $randstring);
            if (!$res) return false;
        }
        return self::enToken($string,$openid,$randstring,$valid_peroid);
    }

    /*
     * 获取token公钥并缓存
     * @param boolean $debuy 是否为调试模式
     * @param int $default 默认缓存时效
     * */
    private static function InitSkey($debuy=true,$default=3600)
    {
        if($token_skey=CacheService::get('token_skey'))
            self::$sKey=$token_skey;
        else{
            $token_skey=SystemConfigService::get('token_skey');
            if(!$token_skey && $debuy===false) exception('请先配置小程序访问TO_kEN [token_skey] 公钥');
            if($token_skey){
                CacheService::set('token_skey',$token_skey,$default);
                self::$sKey=$token_skey;
            }
        }
    }

    /*
     * 加密token字符串
     * @param string $string 加密字符串
     * @param string $openid 加密openid
     * @param int $valid_peroid token过期时间默认5天
     * @return token
     * */
    private static function enToken($string,$openid='0',$randString='',$valid_peroid=259200)
    {
        //加密字符串 + 相隔付 + openid + 相隔付 + 随机字符串 + 相隔付 +  加密字符串 + 相隔付 + 当前时间 + 相隔付 + token过期时间 + 相隔付
        $to_ken=$string.self::$seperater.
            $openid.self::$seperater.
            $randString.self::$seperater.
            self::$sKey.self::$seperater.
            time().self::$seperater.
            (time()+$valid_peroid).self::$seperater;
        if(strtolower(self::$encryptType)==='des'){
            $token=self::DesNncrypt($to_ken);
        }else {
            $token = self::enCode($to_ken);
        }
        return $token;
    }

    /**
     * 通用加密
     * @param String $string 需要加密的字串
     * @return String
     */
    private static function enCode($string) {
        $skey = array_reverse(str_split(self::$KEY));
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach ($skey as $key => $value) {
            $key < $strCount && $strArr[$key].=$value;
        }
        return str_replace('=', 'O0O0O', join('', $strArr));
    }

    /**
     * 通用解密
     * @param String $string 需要解密的字串
     * @param String $skey 解密KEY
     * @return String
     */
    private static function deCode($string) {
        $skey = array_reverse(str_split(self::$KEY));
        $strArr = str_split(str_replace('O0O0O', '=', $string), 2);
        $strCount = count($strArr);
        foreach ($skey as $key => $value) {
            $key < $strCount && $strArr[$key] = rtrim($strArr[$key], $value);
        }
        return base64_decode(join('', $strArr));
    }

    /*
     *  DES 加密
     *  @param string $data 待加密明文
     *  @param string $deskey 加密秘钥
     *  @return string
     **/
    private static function DesNncrypt($data, $key='')
    {
        $deskey=$key=='' ? self::$sKey : $key;
        if(strlen($deskey) > 8) $deskey=substr($deskey,0,8);//php加密秘钥只能为8位
        if(function_exists('openssl_encrypt')){
            $data = openssl_encrypt($data, 'AES-128-ECB', $deskey, OPENSSL_RAW_DATA);
            $data = strtolower(bin2hex($data));
            return $data;
        }else{
            $blocksize = mcrypt_get_block_size(MCRYPT_DES,MCRYPT_MODE_ECB);
            $pad = $blocksize - (strlen($data) % $blocksize);
            $data1 = $data. str_repeat(chr($pad),$pad);
            $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES,MCRYPT_MODE_ECB),MCRYPT_RAND); //初始化向量
            $data_encrypt  = mcrypt_encrypt(MCRYPT_DES,$deskey,$data1,MCRYPT_MODE_ECB);//加密函数
            $datastr = bin2hex($data_encrypt);
            return $datastr;
        }

    }
    /*
     *  DES 解密
     *  @param string $data 待解密密文
     *  @param string $deskey 加密秘钥
     *  @return string
     */
    private static function DesDecrypt($endata,$deskey=''){
        $deskey=$deskey=='' ? self::$sKey : $deskey;
        if(strlen($deskey) > 8) $deskey=substr($deskey,0,8);//php加密秘钥只能为8位
        if(function_exists('openssl_encrypt')){
            $decrypted = openssl_decrypt(hex2bin($endata), 'AES-128-ECB', $deskey, OPENSSL_RAW_DATA);
            return $decrypted;
        }else{
            $de_datastr = $endata !== false && preg_match('/^[0-9a-fA-F]+$/i',$endata) ? pack('H*',$endata):false;
            $data_decrypt = mcrypt_decrypt(MCRYPT_DES,$deskey,$de_datastr,MCRYPT_MODE_ECB,null);//解密函数
            $ret = self::_pkcs5Unpad($data_decrypt);
            $de_data = trim($ret);
            return $de_data;
        }
    }

    private static function _pkcs5Unpad($text){
        $pad = ord($text{strlen($text)-1});
        if($pad > strlen($text)) return false;
        if(strspn($text,chr($pad),strlen($text)-$pad) != $pad) return false;
        $ret = substr($text,0,-1*$pad);
        return trim($ret);
    }
    /**
     * 生成随机填充码
     * @return string 10位
     * @return string
     */
    private static function createNonceStr($length = 5)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return "crmeb".$str;
    }
}