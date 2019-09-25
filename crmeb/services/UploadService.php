<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/10/24
 */

namespace crmeb\services;

use crmeb\services\storage\COS;
use crmeb\services\storage\OSS;
use crmeb\services\storage\Qiniu;
use crmeb\services\SystemConfigService;
use think\exception\ValidateException;
use think\facade\Filesystem;
use think\File;

class UploadService
{

    private static $uploadStatus;

    //上传图片的大小 2MB 单位字节
    private static $imageValidate = 'filesize:2097152|fileExt:jpg,jpeg,png,gif|fileMime:image/jpeg,image/gif,image/png';

    /**
     * 初始化
     */
    private static function init()
    {
        self::$uploadStatus = new \StdClass();
    }

    /**
     * 返回失败信息
     * @param $error
     * @return mixed
     */
    protected static function setError($error)
    {
        self::$uploadStatus->status = false;
        self::$uploadStatus->error = $error;
        return self::$uploadStatus;
    }

    /**
     * 返回成功信息
     * @param $path
     * @return mixed
     */
    protected static function successful($path)
    {
        self::$uploadStatus->status = true;
//        $filePath = DS . $path . DS . $fileInfo->getSaveName();
        self::$uploadStatus->filePath = '/uploads/' . $path;
//        self::$uploadStatus->fileInfo = $fileInfo;
//        self::$uploadStatus->uploadPath = $path;
//        if(strpos(app()->getRootPath().'public'.DS,'public') !== false){
//        self::$uploadStatus->dir = $filePath;
//        }else{
//            self::$uploadStatus->dir = str_replace('/public','',$filePath);
//        }
//        self::$uploadStatus->status = true;
        return self::$uploadStatus;
    }

    /**
     * 检查上传目录不存在则生成
     * @param $dir
     * @return bool
     */
    protected static function validDir($dir)
    {
        return is_dir($dir) == true || mkdir($dir, 0777, true) == true;
    }

    /**
     * 开启/关闭上出文件验证
     * @param bool $bool
     */
    protected static function autoValidate($bool = false)
    {
        self::$autoValidate = $bool;
    }

    /**
     * 生成上传文件目录
     * @param $path
     * @param null $root
     * @return string
     */
    protected static function uploadDir($path, $root = null)
    {
        if ($root === null) $root = app()->getRootPath() . 'public' . DS ;
        return $root . 'uploads' .DS. $path;
    }

    /**
     * 单图上传
     * @param string $fileName 上传文件名
     * @param string $path 上传路径
     * @param bool $moveName 生成文件名
     * @param bool $autoValidate 是否开启文件验证
     * @param null $root 上传根目录路径
     * @param string $rule 文件名自动生成规则
     * @param int $type
     * @return mixed
     */
    public static function image($fileName, $path, $moveName = true, $autoValidate = true, $root = null, $rule = 'uniqid',$uploadType = null)
    {
        $uploadType = $uploadType ? $uploadType : SystemConfigService::get('upload_type');
        //TODO 没有选择默认使用本地上传
        if (!$uploadType) $uploadType = 1;
        $info = [];
        stream_context_set_default( [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        switch ($uploadType) {
            case 1 :
                self::init();
//                $path = self::uploadDir($path, $root);
//                $dir = app()->getRootPath() . $path;
//                if (!self::validDir($dir)) return '生成上传目录失败,请检查权限!';
//                if (!isset($_FILES[$fileName])) return '上传文件不存在!';
                $file = request()->file($fileName);
                if (!$file) return '上传文件不存在!';
                if ($autoValidate) {
                    try {
                        validate([$fileName => self::$imageValidate])->check([$fileName => $file]);
                    } catch (ValidateException $e) {
                        return $e->getMessage();
                    }
                }
                $fileName = Filesystem::putFile($path, $file);
                if (!$fileName)
                    return '图片上传失败!';
                $filePath = Filesystem::path($fileName);
                $fileInfo = new File($filePath);
                $info["code"] = 200;
                $info["name"] = $fileInfo->getFilename();
                $info["dir"] = str_replace('\\','/', '/uploads/' . $fileName);
                $info["time"] = time();
                $info["size"] = $fileInfo->getSize();
                $info["type"] = $fileInfo->getMime();
                $info["image_type"] = 1;
                $info['thumb_path'] = str_replace('\\','/',self::thumb('.'.$info["dir"]));
                break;
            case 2 :
                $keys = Qiniu::uploadImage($fileName);
                if (is_array($keys)) {
                    foreach ($keys as $key => &$item) {
                        if (is_array($item)) {
                            $info = Qiniu::imageUrl($item['key']);
                            $info['dir'] = UtilService::setHttpType($info['dir']);
                            $info['thumb_path'] = UtilService::setHttpType($info['thumb_path']);
                            $headerArray = get_headers(UtilService::setHttpType($info['dir'], 1), true);
                            $info['size'] = is_array($headerArray['Content-Type']) && count($headerArray['Content-Length']) == 2 ? $headerArray['Content-Length'][1] : (string)$headerArray['Content-Length'];
                            $info['type'] = is_array($headerArray['Content-Type']) && count($headerArray['Content-Type']) == 2 ? $headerArray['Content-Type'][1] : (string)$headerArray['Content-Type'];
                            $info['image_type'] = 2;
                        }
                    }
                } else return $keys;
                break;
            case 3 :
                $serverImageInfo = OSS::uploadImage($fileName);
                if (!is_array($serverImageInfo)) return $serverImageInfo;
                $info['code'] = 200;
                $info['name'] = substr(strrchr($serverImageInfo['info']['url'], '/'), 1);
                $serverImageInfo['info']['url'] = UtilService::setHttpType($serverImageInfo['info']['url']);
                $info['dir'] = $serverImageInfo['info']['url'];
                $info['thumb_path'] = $serverImageInfo['info']['url'];
                $headerArray = get_headers(UtilService::setHttpType($serverImageInfo['info']['url'], 1), true);
                $info['size'] = $headerArray['Content-Length'];
                $info['type'] = $headerArray['Content-Type'];
                $info['time'] = time();
                $info['image_type'] = 3;
                break;
            case 4 :
                list($imageUrl,$serverImageInfo) = COS::uploadImage($fileName);
                if (!is_array($serverImageInfo) && !is_object($serverImageInfo)) return $serverImageInfo;
                if (is_object($serverImageInfo)) $serverImageInfo = $serverImageInfo->toArray();
                $serverImageInfo['ObjectURL'] = $imageUrl;
                $info['code'] = 200;
                $info['name'] = substr(strrchr($serverImageInfo['ObjectURL'], '/'), 1);
                $info['dir'] = $serverImageInfo['ObjectURL'];
                $info['thumb_path'] = $serverImageInfo['ObjectURL'];
                $headerArray = get_headers(UtilService::setHttpType($serverImageInfo['ObjectURL'], 1), true);
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
     * TODO 单图上传 内容
     * @param $key
     * @param $content
     * @param $path
     * @param null $root
     * @return array|string
     * @throws \Exception
     */
    public static function imageStream($key, $content, $path, $root = '')
    {
        $uploadType = SystemConfigService::get('upload_type');
        //TODO 没有选择默认使用本地上传
        if (!$uploadType) $uploadType = 1;
        $siteUrl = SystemConfigService::get('site_url') . '/';
        $info = [];
        switch ($uploadType) {
            case 1 :
                self::init();
                $path = self::uploadDir($path, $root);
                $path = str_replace('\\', DS, $path);
                $path = str_replace('/', DS, $path);
                $dir = $path;
                if (!self::validDir($dir)) return '生成上传目录失败,请检查权限!';
                $name = $path . DS . $key;
                file_put_contents($name, $content);
                $info["code"] = 200;
                $info["name"] = $key;
                $path = str_replace('\\', '/', $path);
                $info["dir"] = $path . '/' .$key;
                $info["time"] = time();
                $headerArray = get_headers(str_replace('\\', '/', UtilService::setHttpType($siteUrl, 1) . $info['dir']), true);
                $info['size'] = $headerArray['Content-Length'];
                $info['type'] = $headerArray['Content-Type'];
                $info["image_type"] = 1;
                $info['thumb_path'] = '/' . $info['dir'];
                $info['dir'] = str_replace('\\','/','/' . $info['dir']);
                break;
            case 2 :
                $keys = Qiniu::uploadImageStream($key, $content);
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
                $serverImageInfo = OSS::uploadImageStream($key, $content);
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
                list($imageUrl,$serverImageInfo) = COS::uploadImageStream($key, $content);
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
     * 文件上传
     * @param string $fileName 上传文件名
     * @param string $path 上传路径
     * @param bool $moveName 生成文件名
     * @param string $autoValidate 验证规则 [size:1024,ext:[],type:[]]
     * @param null $root 上传根目录路径
     * @param string $rule 文件名自动生成规则
     * @return mixed
     */
    public static function file($fileName, $path, $moveName = true, $autoValidate = '', $root = null, $rule = 'uniqid')
    {
        self::init();
//        $path = self::uploadDir($path, $root);
//        $dir = $path;
//        if (!self::validDir($dir)) return self::setError('生成上传目录失败,请检查权限!');
        if (!isset($_FILES[$fileName])) return self::setError('上传文件不存在!');
        $extension = strtolower(pathinfo($_FILES[$fileName]['name'], PATHINFO_EXTENSION));
        if (strtolower($extension) == 'php' || !$extension)
            return self::setError('上传文件非法!');
        $file = request()->file($fileName);
        if ($autoValidate) {
            try {
                validate([$fileName => self::$imageValidate])->check([$fileName => $file]);
            } catch (ValidateException $e) {
                return self::setError($e->getMessage());
            }
        };
        $fileName = Filesystem::putFile($path, $file);
        if (!$fileName)
            return self::setError('图片上传失败!');
        return self::successful($fileName);
    }

    public static function pathToUrl($path)
    {
        return trim(str_replace(DS, '/', $path), '.');
    }

    public static function openImage($filePath)
    {
        return \think\Image::open($filePath);
    }


    /**
     * 图片压缩
     *
     * @param string $filePath 文件路径
     * @param int $ratio 缩放比例 1-9
     * @param string $pre 前缀
     * @return string 压缩图片路径
     */
    public static function thumb($filePath, $ratio = 5, $pre = 's_')
    {
        $img = self::openImage($filePath);
        $width = $img->width() * $ratio / 10;
        $height = $img->height() * $ratio / 10;
        $dir = dirname($filePath);
        $fileName = basename($filePath);
        $savePath = $dir . DS . $pre . $fileName;
        $img->thumb($width, $height)->save($savePath);
        if(substr($savePath, 0, 2) == './') return substr($savePath, 1, strlen($savePath));
        return DS . $savePath;
    }
}