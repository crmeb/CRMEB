<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace service;


use think\Request;
use service\JsonService;

class UtilService
{

    public static function postMore($params,Request $request = null,$suffix = false)
    {
        if($request === null) $request = Request::instance();
        $p = [];
        $i = 0;
        foreach ($params as $param){
            if(!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $request->post($param);
            }else{
                if(!isset($param[1])) $param[1] = null;
                if(!isset($param[2])) $param[2] = '';
                $name = is_array($param[1]) ? $param[0].'/a' : $param[0];
                $p[$suffix == true ? $i++ : (isset($param[3]) ? $param[3] : $param[0])] = $request->post($name,$param[1],$param[2]);
            }
        }
        return $p;
    }

    public static function getMore($params,Request $request=null,$suffix = false)
    {
        if($request === null) $request = Request::instance();
        $p = [];
        $i = 0;
        foreach ($params as $param){
            if(!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $request->get($param);
            }else{
                if(!isset($param[1])) $param[1] = null;
                if(!isset($param[2])) $param[2] = '';
                $name = is_array($param[1]) ? $param[0].'/a' : $param[0];
                $p[$suffix == true ? $i++ : (isset($param[3]) ? $param[3] : $param[0])] = $request->get($name,$param[1],$param[2]);
            }
        }
        return $p;
    }

    public static function encrypt($string, $operation = false, $key = '', $expiry = 0) {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 6;

        // 密匙
        $key = md5($key);

        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == false ? substr($string, 0, $ckey_length):
            substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
//解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == false ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == false) {
            // 验证数据有效性，请看未加密明文的格式
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }


    /**
     * 路径转url路径
     * @param $path
     * @return string
     */
    public static function pathToUrl($path)
    {
        return trim(str_replace(DS, '/', $path),'.');
    }

    /**
     * url转换路径
     * @param $url
     * @return string
     */
    public static function urlToPath($url)
    {
        return ROOT_PATH.trim(str_replace('/',DS,$url),DS);
    }

    public static function timeTran($time)
    {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int)$k)) {
                return $c . $v . '前';
            }
        }
    }

    /**
     * 分级排序
     * @param $data
     * @param int $pid
     * @param string $field
     * @param string $pk
     * @param string $html
     * @param int $level
     * @param bool $clear
     * @return array
     */
    public static function sortListTier($data, $pid = 0, $field = 'pid', $pk = 'id', $html = '|-----', $level = 1, $clear = true)
    {
        static $list = [];
        if ($clear) $list = [];
        foreach ($data as $k => $res) {
            if ($res[$field] == $pid) {
                $res['html'] = str_repeat($html, $level);
                $list[] = $res;
                unset($data[$k]);
                self::sortListTier($data, $res[$pk], $field, $pk, $html, $level + 1, false);
            }
        }
        return $list;
    }
    /**
     * 分级返回多维数组
     * @param $data
     * @param int $pid
     * @param string $field
     * @param string $pk
     * @param int $level
     * @return array
     */
    public static function getChindNode($data, $pid = 0, $field = 'pid', $pk = 'id', $level = 1)
    {

        static $list = [];
        foreach ($data as $k => $res) {
            if ($res['pid'] == $pid) {
                $list[] = $res;
                unset($data[$k]);
                self::getChindNode($data, $res['id'], $field, $pk, $level + 1);

            }
        }
        return $list;


    }
    /**分级返回下级所有分类ID
     * @param $data
     * @param string $children
     * @param string $field
     * @param string $pk
     * @return string
     */
    public static function getChildrenPid($data,$pid, $field = 'pid', $pk = 'id')
    {
        static $pids = '';
        foreach ($data as $k => $res) {
            if ($res[$field] == $pid) {
                $pids .= ','.$res[$pk];
                self::getChildrenPid($data, $res[$pk], $field, $pk);
            }
        }
        return $pids;
    }


    /**
     * 删除公告资源
     * @param $url
     * @return \StdClass
     */
    public static function rmPublicResource($url,$isPath = false)
    {
        $path = $isPath ? $url : realpath(self::urlToPath($url));
        if(!$path) return JsonService::fail('删除文件不存在!');
        if(!file_exists($path)) return JsonService::fail('删除路径不合法!');
//        if(0 !== strpos($path,ROOT_PATH.'public/uploads/')) return JsonService::fail('删除路径不合法!');
        if(!unlink($path)) return JsonService::fail('删除文件失败!');
        return JsonService::successful();
    }

    /**
     * 是否为微信内部浏览器
     * @return bool
     */
    public static function isWechatBrowser()
    {
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false);
    }

    /**
     * 匿名处理
     * @param $name
     * @return string
     */
    public static function anonymity($name)
    {
        $strLen = mb_strlen($name,'UTF-8');
        $min = 3;
        if($strLen <= 1)
            return '*';
        if($strLen<= $min)
            return mb_substr($name,0,1,'UTF-8').str_repeat('*',$min-1);
        else
            return mb_substr($name,0,1,'UTF-8').str_repeat('*',$strLen-1).mb_substr($name,-1,1,'UTF-8');
    }

    /**
     * 身份证验证
     * @param $card
     * @return bool
     */
    public static function setCard($card){
        $city = [11=>"北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",21=>"辽宁",22=>"吉林",23=>"黑龙江 ",31=>"上海",32=>"江苏",33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",41=>"河南",42=>"湖北 ",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏 ",61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",71=>"台湾",81=>"香港",82=>"澳门",91=>"国外 "];
        $tip = "";
        $match = "/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/";
        $pass= true;
        if(!$card || !preg_match($match,$card)){
            //身份证格式错误
            $pass = false;
        }else if(!$city[substr($card,0,2)]){
            //地址错误
            $pass = false;
        }else{
            //18位身份证需要验证最后一位校验位
            if(strlen($card) == 18){
                $card = str_split($card);
                //∑(ai×Wi)(mod 11)
                //加权因子
                $factor = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ];
                //校验位
                $parity = [ 1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2 ];
                $sum = 0;
                $ai = 0;
                $wi = 0;
                for ($i = 0; $i < 17; $i++)
                {
                    $ai = $card[$i];
                    $wi = $factor[$i];
                    $sum += $ai * $wi;
                }
                $last = $parity[$sum % 11];
                if($parity[$sum % 11] != $card[17]){
//                        $tip = "校验位错误";
                    $pass =false;
                }
            }else{
                $pass =false;
            }
        }
        if(!$pass) return false;/* 身份证格式错误*/
        return true;/* 身份证格式正确*/
    }

}