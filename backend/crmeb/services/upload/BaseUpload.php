<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\upload;

use crmeb\basic\BaseStorage;

/**
 * Class BaseUpload
 * @package crmeb\services\upload
 */
abstract class BaseUpload extends BaseStorage
{

    /**
     * 文件名称
     * @var string
     */
    protected $fileName;

    /**
     * 文件信息
     * @var object
     */
    protected $fileInfo;

    /**
     * 下载文件信息
     * @var object
     */
    protected $downFileInfo;

    /**
     * 当前实例所属空间的非敏感地址信息
     * @var array
     */
    protected $storageInfo = [];

    /**
     * 文件路径
     * @var string
     */
    protected $filePath;

    /**
     * 上传文件保存目录
     * @var string
     */
    protected $path;

    /**
     * 验证配置
     * @var array
     */
    protected $validate = [];

    /**
     * 水印配置
     * @var array
     */
    protected $waterConfig = [];

    /**
     * 缩略图配置
     * @var array
     */
    protected $thumbConfig = [];

    /**
     * 缩略图尺寸列表
     * @var array
     */
    protected $thumb = [];

    /**
     * 是否生成缩略图
     * @var bool
     */
    protected $authThumb = true;

    /**
     * 初始化公共配置（水印、缩略图）
     * @param array $config
     */
    protected function initialize(array $config)
    {
        $this->fileInfo = new \StdClass();
        $this->downFileInfo = new \StdClass();
        $this->storageInfo = [
            'driver_key' => $this->name,
            'name' => $config['storageName'] ?? '',
            'region' => $config['storageRegion'] ?? '',
            'domain' => $config['uploadUrl'] ?? '',
            'cdn' => $config['cdn'] ?? '',
        ];
        $this->waterConfig = $config['water'] ?? [];
        $this->thumbConfig = $config['thumb'] ?? [];
        $this->thumb = ['big', 'mid', 'small'];
    }

    /**
     * 上传文件
     * @param string $filePath
     * @param string $key
     * @return mixed
     */
    abstract public function upload(string $filePath, string $key);

    /**
     * 删除文件
     * @param string $key
     * @return mixed
     */
    abstract public function delete(string $key);

    /**
     * 获取文件路径
     * @param string $key
     * @return mixed
     */
    abstract public function getFilePath(string $key);

    /**
     * 获取文件信息
     * @return mixed
     */
    abstract public function getFileInfo();

    /**
     * 设置文件信息
     * @param array $fileInfo
     * @return $this
     */
    public function setFileInfo(array $fileInfo)
    {
        $this->fileInfo = (object)$fileInfo;
        return $this;
    }

    /**
     * 获取匹配文件原属空间的非敏感配置
     * @return array
     */
    public function getStorageInfo(): array
    {
        return $this->storageInfo;
    }

    /**
     * 设置文件名称
     * @param string $fileName
     * @return $this
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * 设置文件路径
     * @param string $filePath
     * @return $this
     */
    public function setFilePath(string $filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * 设置本次操作是否生成缩略图
     * @param bool $enabled
     * @return $this
     */
    public function authThumb(bool $enabled)
    {
        $this->authThumb = $enabled;
        return $this;
    }

    /**
     * 设置二维码和海报等上传流程的缩略图开关
     * @param bool $enabled
     * @return $this
     */
    public function setAuthThumb(bool $enabled)
    {
        return $this->authThumb($enabled);
    }

    /**
     * 从本地路径或 URL 路径读取文件名和扩展名
     * @param string $path
     * @return array
     */
    protected function getFileName(string $path): array
    {
        $path = parse_url($path, PHP_URL_PATH);
        $info = pathinfo(rawurldecode(is_string($path) ? $path : ''));
        return [$info['filename'] ?? '', strtolower($info['extension'] ?? '')];
    }

    /**
     * 检测文件是否为图片
     * @param string $filePath
     * @return bool
     */
    protected function checkImage(string $filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        $imageInfo = @getimagesize($filePath);
        return $imageInfo !== false;
    }

    /**
     * 获取上传后的文件信息
     * @return array
     */
    public function getUploadInfo()
    {
        if (!$this->fileInfo || !isset($this->fileInfo->filePath)) {
            return [];
        }

        $uploadInfo = $this->fileInfo->uploadInfo ?? null;
        $size = $this->fileInfo->size ?? 0;
        $type = $this->fileInfo->mimeType ?? '';

        if (is_object($uploadInfo)) {
            $size = method_exists($uploadInfo, 'getSize') ? $uploadInfo->getSize() : $size;
            $type = method_exists($uploadInfo, 'getMime') ? $uploadInfo->getMime() : $type;
        } elseif (is_array($uploadInfo)) {
            $size = $uploadInfo['size'] ?? $size;
            $type = $uploadInfo['mimeType'] ?? $uploadInfo['mime_type'] ?? $type;
        }

        return [
            'name' => $this->fileInfo->fileName ?? '',
            'real_name' => $this->fileInfo->realName ?? '',
            'dir' => $this->fileInfo->filePath ?? '',
            'thumb_path' => $this->fileInfo->filePathMid ?? $this->fileInfo->filePath,
            'size' => $size,
            'type' => $type,
            'time' => time(),
        ];
    }

    /**
     * 获取下载后的文件信息
     * @return array
     */
    public function getDownloadInfo(): array
    {
        if (!isset($this->downFileInfo->downloadFilePath)) {
            return [];
        }
        $file = $this->downFileInfo->downloadInfo ?? null;
        return [
            'name' => $this->downFileInfo->downloadFileName ?? '',
            'dir' => $this->downFileInfo->downloadFilePath,
            'size' => is_object($file) && method_exists($file, 'getSize') ? $file->getSize() : 0,
            'type' => is_object($file) && method_exists($file, 'getMime') ? $file->getMime() : '',
        ];
    }

    /**
     * 检查上传 URL 并补充 /
     * @param string $url
     * @return string
     */
    protected function checkUploadUrl(string $url)
    {
        if ($url && substr($url, -1) !== '/') {
            $url .= '/';
        }
        return $url;
    }

    /**
     * 生成唯一的文件名
     * @param string|null $filePath
     * @param string|null $ext
     * @return string
     */
    protected function saveFileName(?string $filePath = null, ?string $ext = null)
    {
        if ($ext === null && $filePath) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        }
        return md5(uniqid(true)) . ($ext ? '.' . $ext : '');
    }

    /**
     * 获取上传路径（$this->path 已包含日期目录，这里仅拼接文件名）
     * 子驱动可重写此方法实现自定义路径逻辑
     * @param string $key
     * @return string
     */
    protected function getUploadPath(string $key)
    {
        $path = trim((string)$this->path, '/');
        return $path === '' ? $key : $path . '/' . $key;
    }

}
