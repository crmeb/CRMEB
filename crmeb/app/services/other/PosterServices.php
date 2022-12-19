<?php

namespace app\services\other;

use dh2y\qrcode\QRcode;
use think\facade\Config;

class PosterServices
{
    /**
     * TODO 砍价 拼团 分享海报生成
     * @param array $data
     * @param $path
     * @return array|bool|string
     * @throws \Exception
     */
    public static function setShareMarketingPoster($data = array(), $path)
    {
        $config = array(
            'text' => array(
                array(
                    'text' => $data['price'],//TODO 价格
                    'left' => 116,
                    'top' => 200,
                    'fontPath' => app()->getRootPath() . 'public/statics/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                    'fontSize' => 50,             //字号
                    'fontColor' => '255,0,0',       //字体颜色
                    'angle' => 0,
                ),
                array(
                    'text' => $data['label'],//TODO 标签
                    'left' => 450,
                    'top' => 188,
                    'fontPath' => app()->getRootPath() . 'public/statics/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                    'fontSize' => 24,             //字号
                    'fontColor' => '255,255,255',       //字体颜色
                    'angle' => 0,
                ),
                array(
                    'text' => $data['msg'],//TODO 简述
                    'left' => 80,
                    'top' => 270,
                    'fontPath' => app()->getRootPath() . 'public/statics/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                    'fontSize' => 22,             //字号
                    'fontColor' => '40,40,40',       //字体颜色
                    'angle' => 0,
                )
            ),
            'image' => array(
                array(
                    'url' => $data['image'],     //图片
                    'stream' => 0,
                    'left' => 120,
                    'top' => 340,
                    'right' => 0,
                    'bottom' => 0,
                    'width' => 450,
                    'height' => 450,
                    'opacity' => 100
                ),
                array(
                    'url' => $data['url'],     //二维码资源
                    'stream' => 0,
                    'left' => 260,
                    'top' => 890,
                    'right' => 0,
                    'bottom' => 0,
                    'width' => 160,
                    'height' => 160,
                    'opacity' => 100
                )
            ),
            'background' => 'statics/poster/poster.jpg'
        );
        if (!file_exists($config['background'])) exception('缺少系统预设背景图片');
        if (strlen($data['title']) < 36) {
            $text = array(
                'text' => $data['title'],//TODO 标题
                'left' => 76,
                'top' => 100,
                'fontPath' => app()->getRootPath() . 'public/statics/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                'fontSize' => 32,         //字号
                'fontColor' => '0,0,0',       //字体颜色
                'angle' => 0,
            );
            array_push($config['text'], $text);
        } else {
            $titleOne = array(
                'text' => mb_strimwidth($data['title'], 0, 24),//TODO 标题
                'left' => 76,
                'top' => 70,
                'fontPath' => app()->getRootPath() . 'public/statics/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                'fontSize' => 32,         //字号
                'fontColor' => '0,0,0',       //字体颜色
                'angle' => 0,
            );
            $titleTwo = array(
                'text' => mb_strimwidth($data['title'], mb_strlen(mb_strimwidth($data['title'], 0, 24)), 24),//TODO 标题
                'left' => 76,
                'top' => 120,
                'fontPath' => app()->getRootPath() . 'public/statics/font/Alibaba-PuHuiTi-Regular.otf',     //字体文件
                'fontSize' => 32,         //字号
                'fontColor' => '0,0,0',       //字体颜色
                'angle' => 0,
            );
            array_push($config['text'], $titleOne);
            array_push($config['text'], $titleTwo);
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
    public static function setSharePoster($config = array(), $path, $name = '')
    {
        $imageDefault = array(
            'left' => 0,
            'top' => 0,
            'right' => 0,
            'bottom' => 0,
            'width' => 100,
            'height' => 100,
            'opacity' => 100
        );
        $textDefault = array(
            'text' => '',
            'left' => 0,
            'top' => 0,
            'fontSize' => 32,       //字号
            'fontColor' => '255,255,255', //字体颜色
            'angle' => 0,
        );
        $background = $config['background'];//海报最底层得背景
        if (substr($background, 0, 1) === '/') {
            $background = substr($background, 1);
        }
        $background = str_replace('https://', 'http://', $background);
        $backgroundInfo = getimagesize($background);
        $background = imagecreatefromstring(file_get_contents($background));
        $backgroundWidth = $backgroundInfo[0];  //背景宽度
        $backgroundHeight = $backgroundInfo[1];  //背景高度
        $imageRes = imageCreatetruecolor($backgroundWidth, $backgroundHeight);
        $color = imagecolorallocate($imageRes, 0, 0, 0);
        imagefill($imageRes, 0, 0, $color);
        imagecopyresampled($imageRes, $background, 0, 0, 0, 0, imagesx($background), imagesy($background), imagesx($background), imagesy($background));
        if (!empty($config['image'])) {
            foreach ($config['image'] as $key => $val) {
                $val = array_merge($imageDefault, $val);
                $val['url'] = str_replace('https', 'http', $val['url']);
                $info = getimagesize($val['url']);
                $function = 'imagecreatefrom' . image_type_to_extension($info[2], false);
                if ($val['stream']) {
                    $info = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res = $function($val['url']);
                $resWidth = $info[0];
                $resHeight = $info[1];
                $canvas = imagecreatetruecolor($val['width'], $val['height']);
                imagefill($canvas, 0, 0, $color);
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'], $resWidth, $resHeight);
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) - $val['width'] : $val['left'];
                $val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) - $val['height'] : $val['top'];
                imagecopymerge($imageRes, $canvas, $val['left'], $val['top'], $val['right'], $val['bottom'], $val['width'], $val['height'], $val['opacity']);//左，上，右，下，宽度，高度，透明度
            }
        }
        if (isset($config['text']) && !empty($config['text'])) {
            foreach ($config['text'] as $key => $val) {
                $val = array_merge($textDefault, $val);
                list($R, $G, $B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) : $val['left'];
                $val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) : $val['top'];
                imagettftext($imageRes, $val['fontSize'], $val['angle'], $val['left'], $val['top'], $fontColor, $val['fontPath'], $val['text']);
            }
        }
        ob_start();
        imagejpeg($imageRes);
        imagedestroy($imageRes);
        $res = ob_get_contents();
        ob_end_clean();
        if ($name == '') {
            $key = substr(md5(rand(0, 9999)), 0, 5) . date('YmdHis') . rand(0, 999999) . '.jpg';
        } else {
            $key = $name;
        }
        $uploadType = (int)sys_config('upload_type', 1);
        $upload = UploadService::init();
        $res = $upload->to($path)->validate()->setAuthThumb(false)->stream($res, $key);
        if ($res === false) {
            return $upload->getError();
        } else {
            $info = $upload->getUploadInfo();
            $info['image_type'] = $uploadType;
            return $info;
        }
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
        $result = json_decode($result, true);
        if (is_array($result)) return ['status' => false, 'msg' => $result['errcode'] . '---' . $result['errmsg']];
        return ['status' => true];
    }

    /**
     * TODO 修改 https 和 http 移动到common
     * @param $url $url 域名
     * @param int $type 0 返回https 1 返回 http
     * @return string
     */
    public static function setHttpType($url, $type = 0)
    {
        $domainTop = substr($url, 0, 5);
        if ($type) {
            if ($domainTop == 'https') $url = 'http' . substr($url, 5, strlen($url));
        } else {
            if ($domainTop != 'https') $url = 'https:' . substr($url, 5, strlen($url));
        }
        return $url;
    }


    /**
     * 获取二维码
     * @param $url
     * @param $name
     * @return array|bool|string
     */
    public static function getQRCodePath($url, $name)
    {
        if (!strlen(trim($url)) || !strlen(trim($name))) return false;
        try {
            $uploadType = sys_config('upload_type');
            //TODO 没有选择默认使用本地上传
            if (!$uploadType) $uploadType = 1;
            $uploadType = (int)$uploadType;
            $siteUrl = sys_config('site_url');
            if (!$siteUrl) return '请前往后台设置->系统设置->网站域名 填写您的域名格式为：http://域名';
            $info = [];
            $outfiles = Config::get('qrcode.cache_dir');
            $code = new QRcode();
            if (!file_exists($outfiles)) mkdir($outfiles, 0775, true);
            $wapCodePath = $code->png($url, $outfiles . '/' . $name)->getPath(); //获取二维码生成的地址
            $content = file_get_contents('.' . $wapCodePath);
            if ($uploadType === 1) {
                $info["code"] = 200;
                $info["name"] = $name;
                $info["dir"] = '/' . $outfiles . '/' . $name;
                $info["time"] = time();
                $info['size'] = 0;
                $info['type'] = 'image/png';
                $info["image_type"] = 1;
                $info['thumb_path'] = '/' . $outfiles . '/' . $name;
                return $info;
            } else {
                $upload = UploadService::init($uploadType);
                $res = $upload->to($outfiles)->validate()->setAuthThumb(false)->stream($content, $name);
                if ($res === false) {
                    return $upload->getError();
                }
                $info = $upload->getUploadInfo();
                $info['image_type'] = $uploadType;
                return $info;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**分级返回下级所有分类ID
     * @param $data
     * @param string $children
     * @param string $field
     * @param string $pk
     * @return string
     */
    public static function getChildrenPid($data, $pid, $field = 'pid', $pk = 'id')
    {
        static $pids = '';
        foreach ($data as $k => $res) {
            if ($res[$field] == $pid) {
                $pids .= ',' . $res[$pk];
                self::getChildrenPid($data, $res[$pk], $field, $pk);
            }
        }
        return $pids;
    }
}
