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
use crmeb\traits\LogicTrait;
use think\exception\ValidateException;
use think\facade\Filesystem;
use Guzzle\Http\EntityBody;
use think\File;

/**
 * Class UploadService
 * @package crmeb\services
 * @method $this setReturnErr(bool $returnErr) 设置发生错误时是否返回错误信息
 * @method $this setAutoValidate(bool $autoValidate) 设置是否校验上传文件
 * @method $this setUploadType(int $uploadType) 设置上传类型
 * @method $this setImageValidate(string $imageValidate) 设置上传图片大小等验证信息
 * @method $this setUploadPath(string $uploadPath) 设置文件上传路径
 */
class UploadService
{
    use LogicTrait;
    /**
     * 文件校验
     * @var bool
     */
    protected $autoValidate = false;

    /**
     * 上传路径
     * @var string
     */
    protected $uploadPath;

    /**
     * 上传类型
     * @var int
     */
    protected $uploadType;

    /**
     * 发生错误时是否返回错误信息
     * @var bool
     */
    protected $returnErr = false;

    /**
     * 上传文件返回数组初始值
     * @var array
     */
    protected $uploadInfo = [
        'name' => '',
        'size' => 0,
        'type' => 'image/jpeg',
        'dir' => '',
        'thumb_path' => '',
        'image_type' => '',
        'time' => 0,
    ];

    /**
     * 上传信息
     * @var object
     */
    private static $uploadStatus;

    /**
     * 上传图片的大小 2MB 单位字节
     * @var string
     */
    protected $imageValidate = null;

    /**
     * 上传规则
     * @var array
     */
    protected $imageValidateArray = [
        'filesize' => 2097152,
        'fileExt' => ['jpg', 'jpeg', 'png', 'gif', 'pem', 'mp3', 'wma', 'wav', 'amr', 'mp4'],
        'fileMime' => ['image/jpeg', 'image/gif', 'image/png', 'text/plain', 'audio/mpeg'],
    ];

    protected $propsRule = [
        'returnErr' => false,
        'autoValidate' => false,
        'uploadPath' => null,
        'uploadType' => null,
    ];

    protected function __construct()
    {
        $this->init();
    }

    /**
     * 初始化
     */
    protected function init()
    {
        self::$uploadStatus = new \StdClass();
        $this->extractValidate();
    }

    /**
     * 提取上传验证
     */
    protected function extractValidate()
    {
        $imageValidate = [];
        foreach ($this->imageValidateArray as $key => $value) {
            $imageValidate[] = $key . ':' . (is_array($value) ? implode(',', $value) : $value);
        }
        $this->imageValidate = implode('|', $imageValidate);
        unset($imageValidate);
    }

    /**
     * 设置上传验证
     * @param array $imageValidateArray
     * @return $this
     */
    public function setImageValidateArray(array $imageValidateArray)
    {
        if (isset($imageValidateArray['filesize']) && !is_int($imageValidateArray['filesize'])) {
            $imageValidateArray['filesize'] = 2097152;
        }
        $this->imageValidateArray = array_merge($this->imageValidateArray, $imageValidateArray);
        $this->extractValidate();
        return $this;
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
        self::$uploadStatus->filePath = '/uploads/' . $path;
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
     * 生成上传文件目录
     * @param $path
     * @param null $root
     * @return string
     */
    protected static function uploadDir($path, $root = null)
    {
        if ($root === null) $root = app()->getRootPath() . 'public' . DS;
        return $root . 'uploads' . DS . $path;
    }

    /**
     * 单图上传
     * @param string $fileName 上传文件名
     * @return mixed
     */
    public function image($fileName)
    {
        $info = [];
        try {
            $uploadType = $this->uploadType ?: sys_config('upload_type');
            //TODO 没有选择默认使用本地上传
            if (!$uploadType)
                $uploadType = 1;
            if (!is_int($uploadType))
                $uploadType = (int)$uploadType;
            switch ($uploadType) {
                case 1 :
                    $info = $this->uploadLocaFile($fileName);
                    if (is_string($info)) return $info;
                    break;
                case 2 :
                    $keys = Qiniu::uploadImage($fileName);
                    if (is_array($keys)) {
                        foreach ($keys as $key => &$item) {
                            if (is_array($item)) {
                                $info = Qiniu::imageUrl($item['key']);
                                $info = $this->setUploadInfo($info['dir'], 2, $item['key'], UtilService::setHttpType($info['thumb_path']));
                            }
                        }
                    } else return $keys;
                    break;
                case 3 :
                    $serverImageInfo = OSS::uploadImage($fileName);
                    if (!is_array($serverImageInfo)) return $serverImageInfo;
                    $info = $this->setUploadInfo(UtilService::setHttpType($serverImageInfo['info']['url']), 3, substr(strrchr($serverImageInfo['info']['url'], '/'), 1));
                    break;
                case 4 :
                    list($imageUrl, $serverImageInfo) = COS::uploadImage($fileName);
                    if (!is_array($serverImageInfo) && !is_object($serverImageInfo)) return $serverImageInfo;
                    $info = $this->setUploadInfo($imageUrl, 4, substr(strrchr($imageUrl, '/'), 1));
                    break;
                default:
                    $info = $this->uploadLocaFile($fileName);
                    if (is_string($info)) return $info;
                    break;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $info;
    }

    /**
     * 获取图片类型和大小
     * @param string $url 图片地址
     * @param int $type 类型
     * @param bool $isData 是否真实获取图片信息
     * @return array
     */
    public static function getImageHeaders(string $url, $type = 1, $isData = true)
    {
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $header['Content-Length'] = 0;
        $header['Content-Type'] = 'image/jpeg';
        if (!$isData) return $header;
        try {
            $header = get_headers(str_replace('\\', '/', UtilService::setHttpType($url, $type)), true);
            if (!isset($header['Content-Length'])) $header['Content-Length'] = 0;
            if (!isset($header['Content-Type'])) $header['Content-Type'] = 'image/jpeg';
        } catch (\Exception $e) {
        }
        return $header;
    }

    /**
     * 本地文件上传
     * @param $fileName
     * @return array|string
     */
    public function uploadLocaFile($fileName)
    {
        $file = request()->file($fileName);
        if (!$file) return '上传文件不存在!';
        if ($this->autoValidate) {
            try {
                validate([$fileName => $this->imageValidate])->check([$fileName => $file]);
            } catch (ValidateException $e) {
                return $e->getMessage();
            }
        }
        $fileName = Filesystem::putFile($this->uploadPath, $file);
        if (!$fileName) return '图片上传失败!';
        $filePath = Filesystem::path($fileName);
        $fileInfo = new File($filePath);
        $url = '/uploads/' . $fileName;
        $this->uploadPath = '';
        $this->autoValidate = false;
        return $this->setUploadInfo($url, 1, $fileInfo->getFilename(), self::thumb('.' . $url), [
            'Content-Length' => $fileInfo->getSize(),
            'Content-Type' => $fileInfo->getMime()
        ]);
    }

    /**
     * 本地文件流上传
     * @param $key
     * @param $content
     * @param string $root
     * @return array|string
     */
    public function uploadLocalStream($key, $content, $root = '')
    {
        $siteUrl = sys_config('site_url') . '/';
        $path = self::uploadDir($this->uploadPath, $root);
        $path = str_replace('\\', DS, $path);
        $path = str_replace('/', DS, $path);
        $dir = $path;
        if (!self::validDir($dir)) return '生成上传目录失败,请检查权限!';
        $name = $path . DS . $key;
        file_put_contents($name, $content);
        $path = str_replace('\\', '/', $path);
        $headerArray = self::getImageHeaders($siteUrl . $path . '/' . $key);
        $url = '/' . $path . '/' . $key;
        return $this->setUploadInfo($url, 1, $key, $url, $headerArray);
    }

    /**
     * TODO 文件流上传
     * @param $key
     * @param $content
     * @param null $root
     * @return array|string
     * @throws \Exception
     */
    public function imageStream($key, $content, $root = '')
    {
        $uploadType = sys_config('upload_type');
        //TODO 没有选择默认使用本地上传
        if (!$uploadType) $uploadType = 1;
        $info = [];
        switch ($uploadType) {
            case 1 :
                $info = $this->uploadLocalStream($key, $content, $root);
                if (is_string($info)) return $info;
                break;
            case 2 :
                $keys = Qiniu::uploadImageStream($key, $content);
                if (is_array($keys)) {
                    foreach ($keys as $key => &$item) {
                        if (is_array($item)) {
                            $info = Qiniu::imageUrl($item['key']);
                            $info['dir'] = UtilService::setHttpType($info['dir']);
                            $info = $this->setUploadInfo($info['dir'], 2, $item['key'], $info['thumb_path']);
                        }
                    }
                    if (!count($info)) return '七牛云文件上传失败';
                } else return $keys;
                break;
            case 3 :
                $content = COS::resourceStream($content);
                $serverImageInfo = OSS::uploadImageStream($key, $content);
                if (!is_array($serverImageInfo)) return $serverImageInfo;
                $info = $this->setUploadInfo(UtilService::setHttpType($serverImageInfo['info']['url']), 3, substr(strrchr($serverImageInfo['info']['url'], '/'), 1));
                break;
            case 4 :
                list($imageUrl, $serverImageInfo) = COS::uploadImageStream($key, $content);
                if (!is_array($serverImageInfo) && !is_object($serverImageInfo)) return $serverImageInfo;
                $info = $this->setUploadInfo($imageUrl, 4, substr(strrchr($imageUrl, '/'), 1));
                break;
            default:
                $info = $this->uploadLocalStream($key, $content, $root);
                if (is_string($info)) return $info;
                break;
        }
        $this->uploadPath = '';
        $this->autoValidate = true;
        return $info;
    }

    /**
     * 设置返回的数据信息
     * @param string $url
     * @param int $imageType
     * @param string $name
     * @param string $thumbPath
     * @return array
     */
    protected function setUploadInfo(string $url, int $imageType, string $name = '', string $thumbPath = '', array $headerArray = [])
    {
        $headerArray = count($headerArray) ? $headerArray : self::getImageHeaders($url);
        if (is_array($headerArray['Content-Type']) && count($headerArray['Content-Length']) == 2) {
            $headerArray['Content-Length'] = $headerArray['Content-Length'][1];
        }
        if (is_array($headerArray['Content-Type']) && count($headerArray['Content-Type']) == 2) {
            $headerArray['Content-Type'] = $headerArray['Content-Type'][1];
        }
        $info = [
            'name' => str_replace('\\', '/', $name ?: $url),
            'dir' => str_replace('\\', '/', $url),
            'size' => $headerArray['Content-Length'],
            'type' => $headerArray['Content-Type'],
            'time' => time(),
            'thumb_path' => str_replace('\\', '/', $thumbPath ?: $url),
            'image_type' => $imageType,
        ];
        $uploadInfo = array_merge($this->uploadInfo, $info);
        return $uploadInfo;
    }

    /**
     * 文件上传
     * @param string $fileName 上传文件名
     * @return mixed
     */
    public function file($fileName)
    {
        $file = request()->file($fileName);
        if (!$file) return self::setError('上传文件不存在!');
        if (strtolower($file->getOriginalExtension()) === 'php' || !$file->getOriginalExtension())
            return self::setError('上传文件非法!');
        if ($this->autoValidate) {
            try {
                validate([$fileName => $this->imageValidate])->check([$fileName => $file]);
            } catch (ValidateException $e) {
                return self::setError($e->getMessage());
            }
        };
        $fileName = Filesystem::putFile($this->uploadPath, $file);
        if (!$fileName) return self::setError('图片上传失败!');
        return self::successful(str_replace('\\', '/', $fileName));
    }

    /**
     * 打开图片
     * @param $filePath
     * @return \think\Image
     */
    public function openImage($filePath)
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
    public function thumb($filePath, $ratio = 5, $pre = 's_')
    {
        try {
            $img = $this->openImage($filePath);
        } catch (\Throwable $e) {
            $dir = dirname($filePath);
            $fileName = basename($filePath);
            return $dir . DS . $fileName;
        }
        $width = $img->width() * $ratio / 10;
        $height = $img->height() * $ratio / 10;
        $dir = dirname($filePath);
        $fileName = basename($filePath);
        $savePath = $dir . DS . $pre . $fileName;
        $img->thumb($width, $height)->save($savePath);
        if (substr($savePath, 0, 2) == './') return substr($savePath, 1, strlen($savePath));
        return DS . $savePath;
    }

    /**
     * TODO 转为文件流
     * @param $resource
     * @return EntityBody
     */
    public static function resourceStream($resource)
    {
        return EntityBody::factory($resource)->__toString();
    }
}