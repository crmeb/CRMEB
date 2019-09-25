<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace crmeb\services;

use crmeb\services\storage\COS;
use crmeb\services\storage\OSS;
use crmeb\services\storage\Qiniu;
use think\facade\Config;
use dh2y\qrcode\QRcode;

class UtilService
{

    public static function postMore($params,$request = null,$suffix = false)
    {
        if($request === null) $request = app('request');
        $p = [];
        $i = 0;
        foreach ($params as $param){
            if(!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $request->param($param);
            }else{
                if(!isset($param[1])) $param[1] = null;
                if(!isset($param[2])) $param[2] = '';
                $name = is_array($param[1]) ? $param[0].'/a' : $param[0];
                $p[$suffix == true ? $i++ : (isset($param[3]) ? $param[3] : $param[0])] = $request->param($name,$param[1],$param[2]);
            }
        }
        return $p;
    }

    public static function getMore($params,$request=null,$suffix = false)
    {
        if($request === null) $request = app('request');
        $p = [];
        $i = 0;
        foreach ($params as $param){
            if(!is_array($param)) {
                $p[$suffix == true ? $i++ : $param] = $request->param($param);
            }else{
                if(!isset($param[1])) $param[1] = null;
                if(!isset($param[2])) $param[2] = '';
                $name = is_array($param[1]) ? $param[0].'/a' : $param[0];
                $p[$suffix == true ? $i++ : (isset($param[3]) ? $param[3] : $param[0])] = $request->param($name,$param[1],$param[2]);
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
        $path = trim(str_replace('/',DS,$url),DS);
        if(0 !== strripos($path, 'public'))
            $path = 'public' . DS . $path;
        return app()->getRootPath().$path;
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
//        if(0 !== strpos($path,app()->getRootPath().'public/uploads/')) return JsonService::fail('删除路径不合法!');
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

    /**
     * TODO 砍价 拼团 分享海报生成
     * @param array $data
     * @param $path
     * @return array|bool|string
     * @throws \Exception
     */
    public static function setShareMarketingPoster($data = array(), $path){
        $config = array(
            'text'=>array(
                array(
                    'text'=>$data['price'],//TODO 价格
                    'left'=>116,
                    'top'=>200,
                    'fontPath'=>app()->getRootPath().'public/static/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                    'fontSize'=>50,             //字号
                    'fontColor'=>'255,0,0',       //字体颜色
                    'angle'=>0,
                ),
                array(
                    'text'=>$data['label'],//TODO 标签
                    'left'=>394,
                    'top'=>190,
                    'fontPath'=>app()->getRootPath().'public/static/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                    'fontSize'=>24,             //字号
                    'fontColor'=>'255,255,255',       //字体颜色
                    'angle'=>0,
                ),
                array(
                    'text'=>$data['msg'],//TODO 简述
                    'left'=>80,
                    'top'=>270,
                    'fontPath'=>app()->getRootPath().'public/static/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                    'fontSize'=>22,             //字号
                    'fontColor'=>'40,40,40',       //字体颜色
                    'angle'=>0,
                )
            ),
            'image'=>array(
                array(
                    'url'=>$data['image'],     //图片
                    'stream'=>0,
                    'left'=>120,
                    'top'=>340,
                    'right'=>0,
                    'bottom'=>0,
                    'width'=>450,
                    'height'=>450,
                    'opacity'=>100
                ),
                array(
                    'url'=>$data['url'],     //二维码资源
                    'stream'=>0,
                    'left'=>260,
                    'top'=>890,
                    'right'=>0,
                    'bottom'=>0,
                    'width'=>160,
                    'height'=>160,
                    'opacity'=>100
                )
            ),
            'background'=>app()->getRootPath().'/public/static/poster/poster.jpg'
        );
        if(!file_exists($config['background'])) exception('缺少系统预设背景图片');
        if(strlen($data['title']) < 36){
            $text = array(
                'text'=>$data['title'],//TODO 标题
                'left'=>76,
                'top'=>100,
                'fontPath'=>app()->getRootPath().'public/static/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                'fontSize'=>32,         //字号
                'fontColor'=>'0,0,0',       //字体颜色
                'angle'=>0,
            );
            array_push($config['text'],$text);
        }else{
            $titleOne = array(
                'text'=>mb_substr($data['title'], 0, 12),//TODO 标题
                'left'=>76,
                'top'=>70,
                'fontPath'=>app()->getRootPath().'public/static/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                'fontSize'=>32,         //字号
                'fontColor'=>'0,0,0',       //字体颜色
                'angle'=>0,
            );
            $titleTwo = array(
                'text'=> mb_substr($data['title'], 12, 12),//TODO 标题
                'left'=>76,
                'top'=>120,
                'fontPath'=>app()->getRootPath().'public/static/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                'fontSize'=>32,         //字号
                'fontColor'=>'0,0,0',       //字体颜色
                'angle'=>0,
            );
            array_push($config['text'],$titleOne);
            array_push($config['text'],$titleTwo);
        }
        return self::setSharePoster($config, $path);
    }

    /**
     * TODO 生成分享二维码图片
     * @param array $config
     * @param $path
     * @return array|bool|string
     * @throws \Exception
     */
    public static function setSharePoster($config = array(), $path){
        $imageDefault = array(
            'left'=>0,
            'top'=>0,
            'right'=>0,
            'bottom'=>0,
            'width'=>100,
            'height'=>100,
            'opacity'=>100
        );
        $textDefault = array(
            'text'=>'',
            'left'=>0,
            'top'=>0,
            'fontSize'=>32,       //字号
            'fontColor'=>'255,255,255', //字体颜色
            'angle'=>0,
        );
        $background = $config['background'];//海报最底层得背景
        $backgroundInfo = getimagesize($background);
        $background = imagecreatefromstring(file_get_contents($background));
        $backgroundWidth = $backgroundInfo[0];  //背景宽度
        $backgroundHeight = $backgroundInfo[1];  //背景高度
        $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
        $color = imagecolorallocate($imageRes, 0, 0, 0);
        imagefill($imageRes, 0, 0, $color);
        imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
        if(!empty($config['image'])){
            foreach ($config['image'] as $key => $val) {
                $val = array_merge($imageDefault,$val);
                $info = getimagesize($val['url']);
                $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
                if($val['stream']){
                    $info = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res = $function($val['url']);
                $resWidth = $info[0];
                $resHeight = $info[1];
                $canvas=imagecreatetruecolor($val['width'], $val['height']);
                imagefill($canvas, 0, 0, $color);
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
                imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
            }
        }
        if(isset($config['text']) && !empty($config['text'])){
            foreach ($config['text'] as $key => $val) {
                $val = array_merge($textDefault,$val);
                list($R,$G,$B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
                imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$val['text']);
            }
        }
        ob_start();
        imagejpeg ($imageRes);
        imagedestroy($imageRes);
        $res = ob_get_contents();
        ob_end_clean();
        $key = substr(md5(rand(0, 9999)) , 0, 5). date('YmdHis') . rand(0, 999999) . '.jpg';
        return UploadService::imageStream($key,$res,$path);
    }

    /*
     * 获取当前控制器模型方法组合成的字符串
     * @paran object $request Request 实例化后的对象
     * @retun string
     * */
    public static function getCurrentController()
    {
        return strtolower(request()->app().'/'.request()->controller().'/'.request()->action());
    }

    /**
     * TODO 获取小程序二维码是否生成
     * @param $url
     * @return array
     */
    public static function remoteImage($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        $result = json_decode($result,true);
        if(is_array($result)) return ['status'=>false,'msg'=>$result['errcode'].'---'.$result['errmsg']];
        return ['status'=>true];
    }

    /**
     * TODO 修改 https 和 http
     * @param $url $url 域名
     * @param int $type  0 返回https 1 返回 http
     * @return string
     */
    public static function setHttpType($url, $type = 0)
    {
        $domainTop = substr($url,0,5);
        if($type){ if($domainTop == 'https') $url = 'http'.substr($url,5,strlen($url)); }
        else{ if($domainTop != 'https') $url = 'https:'.substr($url,5,strlen($url)); }
        return $url;
    }

    public static function setSiteUrl($image, $siteUrl = '')
    {
        if(!strlen(trim($siteUrl)))  $siteUrl = SystemConfigService::get('site_url');
        $domainTop = substr($image,0,4);
        if($domainTop == 'http') return $image;
        $image = str_replace('\\', '/', $image);
        return $siteUrl.$image;
    }


    /*
     * CURL 检测远程文件是否在
     *
     * */
    public static function CurlFileExist($url)
    {
        $ch = curl_init();
        try{
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $contents = curl_exec($ch);
            if (preg_match("/404/", $contents)) return false;
            if (preg_match("/403/", $contents)) return false;
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 获取二维码
     * @param $url
     * @param $name
     * @return array|bool|string
     */
    public static function getQRCodePath($url, $name)
    {
        if(!strlen(trim($url)) || !strlen(trim($name))) return false;
        $uploadType = SystemConfigService::get('upload_type');
        //TODO 没有选择默认使用本地上传
        if (!$uploadType) $uploadType = 1;
        $siteUrl = SystemConfigService::get('site_url') ?: '.';
        $info = [];
        $outfile = Config::get('qrcode.cache_dir');

        $code = new QRcode();
        $wapCodePath = $code->png($url, $outfile.'/'.$name)->getPath(); //获取二维码生成的地址
        $content = file_get_contents('.'.$wapCodePath);
        switch ($uploadType) {
            case 1 :
                $info["code"] = 200;
                $info["name"] = $name;
                $info["dir"] = $wapCodePath;
                $info["time"] = time();
                $headerArray = get_headers(UtilService::setHttpType($siteUrl, 1) . $info['dir'],true);
                $info['size'] = $headerArray['Content-Length'];
                $info['type'] = $headerArray['Content-Type'];
                $info["image_type"] = 1;
                $info['thumb_path'] = $wapCodePath;
                break;
            case 2 :
                $keys = Qiniu::uploadImageStream($name, $content);
                if (is_array($keys)) {
                    foreach ($keys as $key => &$item) {
                        if (is_array($item)) {
                            $info = Qiniu::imageUrl($item['key']);
                            $info['dir'] = UtilService::setHttpType($info['dir']);
                            $headerArray = get_headers(UtilService::setHttpType(str_replace('\\', '/', $info['dir']), 1), true);
                            $info['size'] = $headerArray['Content-Length'];
                            $info['type'] = $headerArray['Content-Type'];
                            $info['image_type'] = 2;
                        }
                    }
                    if (!count($info)) return '七牛云文件上传失败';
                } else return $keys;
                break;
            case 3 :
                $content = COS::resourceStream($content);
                $serverImageInfo = OSS::uploadImageStream($name, $content);
                if (!is_array($serverImageInfo)) return $serverImageInfo;
                $info['code'] = 200;
                $info['name'] = substr(strrchr($serverImageInfo['info']['url'], '/'), 1);
                $serverImageInfo['info']['url'] = UtilService::setHttpType($serverImageInfo['info']['url']);
                $info['dir'] = $serverImageInfo['info']['url'];
                $info['thumb_path'] = $serverImageInfo['info']['url'];
                $headerArray = get_headers(UtilService::setHttpType(str_replace('\\', '/', $serverImageInfo['info']['url']), 1), true);
                $info['size'] = $headerArray['Content-Length'];
                $info['type'] = $headerArray['Content-Type'];
                $info['time'] = time();
                $info['image_type'] = 3;
                break;
            case 4 :
                list($imageUrl,$serverImageInfo) = COS::uploadImageStream($name, $content);
                if (!is_array($serverImageInfo) && !is_object($serverImageInfo)) return $serverImageInfo;
                if (is_object($serverImageInfo)) $serverImageInfo = $serverImageInfo->toArray();
                $serverImageInfo['ObjectURL'] = $imageUrl;
                $info['code'] = 200;
                $info['name'] = substr(strrchr($serverImageInfo['ObjectURL'], '/'), 1);
                $info['dir'] = $serverImageInfo['ObjectURL'];
                $info['thumb_path'] = $serverImageInfo['ObjectURL'];
                $headerArray = get_headers(UtilService::setHttpType(str_replace('\\', '/', $serverImageInfo['ObjectURL']), 1), true);
                $info['size'] = $headerArray['Content-Length'];
                $info['type'] = $headerArray['Content-Type'];
                $info['time'] = time();
                $info['image_type'] = 4;
                break;
            default:
                return '上传类型错误，请先选择文件上传类型';
        }
        return $info;
    }

    /**
     * 获取图片转为base64
     * @param string $avatar
     * @return bool|string
     */
    public static function setImageBase64($avatar = '',$timeout=15){
        try{
            $url=parse_url($avatar);
            $url=$url['host'];
            $header = [
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
                'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding: gzip, deflate',
                'Host:'.$url
            ];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $avatar);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            $data = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code == 200)  return "data:image/jpeg;base64," . base64_encode($data);
            else return false;
        }catch (\Exception $e){
            return false;
        }

    }


}