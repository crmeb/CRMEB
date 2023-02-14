<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\services\upload\storage;

use crmeb\services\upload\BaseUpload;
use crmeb\exceptions\AdminException;
use crmeb\utils\DownloadImage;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Filesystem;
use think\File;
use think\Image;

/**
 * 本地上传
 * Class Local
 * @package crmeb\services\upload\storage
 */
class Local extends BaseUpload
{

    /**
     * 默认存放路径
     * @var string
     */
    protected $defaultPath;

    /**
     * 缩略图、水印图存放位置
     * @var string
     */
    public $thumbWaterPath = 'thumb_water';

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->defaultPath = Config::get('filesystem.disks.' . Config::get('filesystem.default') . '.url');
        $this->waterConfig['watermark_text_font'] = app()->getRootPath() . 'public' . '/statics/font/simsunb.ttf';
    }

    protected function app()
    {
        // TODO: Implement app() method.
    }

    public function getTempKeys()
    {
        // TODO: Implement getTempKeys() method.
        return $this->setError('请检查您的上传配置，视频默认oss上传');
    }

    /**
     * 生成上传文件目录
     * @param $path
     * @param null $root
     * @return string
     */
    public function uploadDir($path, $root = null)
    {
        if ($root === null) $root = app()->getRootPath() . 'public/';
        return str_replace('\\', '/', $root . 'uploads/' . $path);
    }

    /**
     * 检查上传目录不存在则生成
     * @param $dir
     * @return bool
     */
    protected function validDir($dir)
    {
        return is_dir($dir) == true || mkdir($dir, 0777, true) == true;
    }

    /**
     * 检测filepath是否是远程地址
     * @param string $filePath
     * @return bool
     */
    public function checkFilePathIsRemote(string $filePath)
    {
        return strpos($filePath, 'https:') !== false || strpos($filePath, 'http:') !== false || substr($filePath, 0, 2) === '//';
    }

    /**
     * 生成与配置相关的文件名称以及路径
     * @param string $filePath 原地址
     * @param string $toPath 保存目录
     * @param array $config 配置相关参数
     * @param string $root
     * @return string
     */
    public function createSaveFilePath(string $filePath, string $toPath, array $config = [], $root = '/')
    {
        [$path, $ext] = $this->getFileName($filePath);
        $fileName = md5(json_encode($config) . $filePath);
        return $this->uploadDir($toPath, $root) . '/' . $fileName . '.' . $ext;
    }

    /**
     * 文件上传
     * @param string $file
     * @return array|bool|mixed|\StdClass
     */
    public function move(string $file = 'file', $realName = false)
    {
        $fileHandle = app()->request->file($file);
        if (!$fileHandle) {
            return $this->setError('Upload file does not exist');
        }
        if ($this->validate) {
            if (!in_array(pathinfo($fileHandle->getOriginalName(), PATHINFO_EXTENSION), $this->validate['fileExt'])) {
                return $this->setError('Upload fileExt error');
            }
            if (filesize($fileHandle) > $this->validate['filesize']) {
                return $this->setError('Upload filesize error');
            }
            if (!in_array($fileHandle->getOriginalMime(), $this->validate['fileMime'])) {
                return $this->setError('Upload fileMine error');
            }
        }
        if ($realName) {
            $fileName = Filesystem::putFileAs($this->path, $fileHandle, $fileHandle->getOriginalName());
        } else {
            $fileName = Filesystem::putFile($this->path, $fileHandle);
        }
        if (!$fileName)
            return $this->setError('Upload failure');
        $filePath = Filesystem::path($fileName);
        $this->fileInfo->uploadInfo = new File($filePath);
        $this->fileInfo->realName = $fileHandle->getOriginalName();
        $this->fileInfo->fileName = $this->fileInfo->uploadInfo->getFilename();
        $this->fileInfo->filePath = $this->defaultPath . '/' . str_replace('\\', '/', $fileName);
        if ($this->checkImage(public_path() . $this->fileInfo->filePath) && $this->authThumb && pathinfo($fileName, PATHINFO_EXTENSION) != 'ico' && pathinfo($fileName, PATHINFO_EXTENSION) != 'gif') {
            try {
                $this->thumb($this->fileInfo->filePath, $this->fileInfo->fileName);
            } catch (\Throwable $e) {
                return $this->setError($e->getMessage());
            }
        }
        return $this->fileInfo;
    }

    /**
     * 文件流上传
     * @param $fileContent
     * @param string|null $key
     * @return array|bool|mixed|\StdClass
     */
    public function stream($fileContent, string $key = null)
    {
        if (!$key) {
            $key = $this->saveFileName();
        }
        $dir = $this->uploadDir($this->path);
        if (!$this->validDir($dir)) {
            return $this->setError('Failed to generate upload directory, please check the permission!');
        }
        $fileName = $dir . '/' . $key;
        file_put_contents($fileName, $fileContent);
        $this->fileInfo->uploadInfo = new File($fileName);
        $this->fileInfo->realName = $key;
        $this->fileInfo->fileName = $key;
        $this->fileInfo->filePath = $this->defaultPath . '/' . $this->path . '/' . $key;
        if ($this->checkImage(public_path() . $this->fileInfo->filePath) && $this->authThumb) {
            try {
                $this->thumb($this->fileInfo->filePath, $this->fileInfo->fileName);
            } catch (\Throwable $e) {
                return $this->setError($e->getMessage());
            }
        }
        return $this->fileInfo;
    }

    /**
     * 文件流下载保存图片
     * @param string $fileContent
     * @param string|null $key
     * @return array|bool|mixed|\StdClass
     */
    public function down(string $fileContent, string $key = null)
    {
        if (!$key) {
            $key = $this->saveFileName();
        }
        $dir = $this->uploadDir($this->path);
        if (!$this->validDir($dir)) {
            return $this->setError('Failed to generate upload directory, please check the permission!');
        }
        $fileName = $dir . '/' . $key;
        file_put_contents($fileName, $fileContent);
        $this->downFileInfo->downloadInfo = new File($fileName);
        $this->downFileInfo->downloadRealName = $key;
        $this->downFileInfo->downloadFileName = $key;
        $this->downFileInfo->downloadFilePath = $this->defaultPath . '/' . $this->path . '/' . $key;
        return $this->downFileInfo;
    }

    /**
     * 生成缩略图
     * @param string $filePath
     * @param string $fileName
     * @param string $type
     * @return array|mixed|string[]
     */
    public function thumb(string $filePath = '', string $fileName = '', string $type = 'all')
    {
        $config = $this->thumbConfig;
        $data = ['big' => $filePath, 'mid' => $filePath, 'small' => $filePath];
        $this->fileInfo->filePathBig = $this->fileInfo->filePathMid = $this->fileInfo->filePathSmall = $this->fileInfo->filePathWater = $filePath;
        //地址存在且不是远程地址
        $filePath = str_replace(sys_config('site_url'), '', $filePath);
        if ($filePath && !$this->checkFilePathIsRemote($filePath)) {
            $findPath = str_replace($fileName, $type . '_' . $fileName, $filePath);
            if (file_exists('.' . $findPath)) {
                return [
                    'big' => str_replace($fileName, 'big_' . $fileName, $filePath),
                    'mid' => str_replace($fileName, 'mid_' . $fileName, $filePath),
                    'small' => str_replace($fileName, 'small_' . $fileName, $filePath)
                ];
            }
            try {
                $this->water($filePath);
                foreach ($this->thumb as $v) {
                    if ($type == 'all' || $type == $v) {
                        $height = 'thumb_' . $v . '_height';
                        $width = 'thumb_' . $v . '_width';
                        $savePath = str_replace($fileName, $v . '_' . $fileName, $filePath);
                        //防止重复生成
                        if (!file_exists('.' . $savePath)) {
                            $Image = Image::open(app()->getRootPath() . 'public' . $filePath);
                            $Image->thumb($config[$width], $config[$height])->save(root_path() . 'public' . $savePath);
                        }
                        $key = 'filePath' . ucfirst($v);
                        $data[$v] = $this->fileInfo->$key = $savePath;
                    }
                }
            } catch (\Throwable $e) {
                throw new AdminException($e->getMessage());
            }
        }
        return $data;
    }

    /**
     * 添加水印
     * @param string $filePath
     * @return mixed|string
     */
    public function water(string $filePath = '')
    {
        $waterConfig = $this->waterConfig;
        if ($waterConfig['image_watermark_status'] && $filePath) {

            switch ($waterConfig['watermark_type']) {
                case 1:
                    if ($waterConfig['watermark_image']) $filePath = $this->image($filePath, $waterConfig);
                    break;
                case 2:
                    $filePath = $this->text($filePath, $waterConfig);
                    break;
            }
        }
        return $filePath;
    }

    /**
     * 图片水印
     * @param string $filePath
     * @param array $waterConfig
     * @param string $waterPath
     * @return string
     */
    public function image(string $filePath, array $waterConfig = [])
    {
        if (!$waterConfig) {
            $waterConfig = $this->waterConfig;
        }
        $watermark_image = $waterConfig['watermark_image'];
        //远程图片
        $filePath = str_replace(sys_config('site_url'), '', $filePath);
        if ($watermark_image && $this->checkFilePathIsRemote($watermark_image)) {
            //看是否在本地
            $pathName = $this->getFilePath($watermark_image, true);
            if ($pathName == $watermark_image) {//不再本地  继续下载
                [$p, $e] = $this->getFileName($watermark_image);
                $name = 'water_image_' . md5($watermark_image) . '.' . $e;
                $watermark_image = '.' . $this->defaultPath . '/' . $this->thumbWaterPath . '/' . $name;
                if (!file_exists($watermark_image)) {
                    try {
                        /** @var DownloadImage $down */
                        $down = app()->make(DownloadImage::class);
                        $data = $down->path($this->thumbWaterPath)->downloadImage($waterConfig['watermark_image'], $name);
                        $watermark_image = $data['path'] ?? '';
                    } catch (\Throwable $e) {
                        throw new AdminException(400724);
                    }
                }
            } else {
                $watermark_image = '.' . $pathName;
            }
        }
        if (!$watermark_image) {
            throw new AdminException(400722);
        }
        $savePath = public_path() . $filePath;
        try {
            $Image = Image::open(app()->getRootPath() . 'public' . $filePath);
            $Image->water($watermark_image, $waterConfig['watermark_position'] ?: 1, $waterConfig['watermark_opacity'])->save($savePath);
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage());
        }
        return $savePath;
    }

    /**
     * 文字水印
     * @param string $filePath
     * @param array $waterConfig
     * @return string
     */
    public function text(string $filePath, array $waterConfig = [])
    {
        if (!$waterConfig) {
            $waterConfig = $this->waterConfig;
        }
        if (!$waterConfig['watermark_text']) {
            throw new AdminException(400723);
        }
        $savePath = public_path() . $filePath;
        try {
            $Image = Image::open(app()->getRootPath() . 'public' . $filePath);
            if (strlen($waterConfig['watermark_text_color']) < 7) {
                $waterConfig['watermark_text_color'] = substr($waterConfig['watermark_text_color'], 1);
                $waterConfig['watermark_text_color'] = '#' . $waterConfig['watermark_text_color'] . $waterConfig['watermark_text_color'];
            }
            if (strlen($waterConfig['watermark_text_color']) > 7) {
                $waterConfig['watermark_text_color'] = substr($waterConfig['watermark_text_color'], 0, 7);
            }
            $Image->text($waterConfig['watermark_text'], $waterConfig['watermark_text_font'], $waterConfig['watermark_text_size'], $waterConfig['watermark_text_color'], $waterConfig['watermark_position'], [$waterConfig['watermark_x'], $waterConfig['watermark_y'], $waterConfig['watermark_text_angle']])->save($savePath);
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage() . $e->getLine());
        }
        return $savePath;
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return bool|mixed
     */
    public function delete(string $filePath)
    {
        if (file_exists($filePath)) {
            try {
                $fileArr = explode('/', $filePath);
                $fileName = end($fileArr);
                unlink($filePath);
                unlink(str_replace($fileName, 'big_' . $fileName, $filePath));
                unlink(str_replace($fileName, 'mid_' . $fileName, $filePath));
                unlink(str_replace($fileName, 'small_' . $fileName, $filePath));
                return true;
            } catch (\Exception $e) {
                return $this->setError($e->getMessage());
            }
        }
        return false;
    }

    public function listbuckets(string $region, bool $line = false, bool $shared = false)
    {
        return [];
    }

    public function createBucket(string $name, string $region)
    {
        return null;
    }

    public function deleteBucket(string $name)
    {
        return null;
    }

    public function setBucketCors(string $name, string $region)
    {
        return true;
    }

    public function getRegion()
    {
        return [];
    }

    public function bindDomian(string $name, string $domain, string $region = null)
    {
        return true;
    }

    public function getDomian(string $name, string $region = null)
    {
        return [];
    }
}
