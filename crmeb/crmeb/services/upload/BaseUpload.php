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

namespace crmeb\services\upload;

use crmeb\basic\BaseStorage;
use think\facade\Config;

/**
 * Class BaseUpload
 * @package crmeb\basic
 */
abstract class BaseUpload extends BaseStorage
{
    /**
     * 缩略图
     * @var string[]
     */
    protected $thumb = ['big', 'mid', 'small'];

    /**
     * 缩略图配置
     * @var array
     */
    protected $thumbConfig = [
        'thumb_big_height' => 800,
        'thumb_big_width' => 800,
        'thumb_mid_height' => 300,
        'thumb_mid_width' => 300,
        'thumb_small_height' => 100,
        'thumb_small_width' => 100,
    ];
    /**
     * 水印配置
     * @var array
     */
    protected $waterConfig = [
        'image_watermark_status' => 0,
        'watermark_type' => 1,
        'watermark_image' => '',
        'watermark_opacity' => 0,
        'watermark_position' => 1,
        'watermark_rotate' => 0,
        'watermark_text' => '',
        'watermark_text_angle' => "",
        'watermark_text_color' => '#000000',
        'watermark_text_size' => '5',
        'watermark_text_font' => '',
        'watermark_x' => 0,
        'watermark_y' => 0
    ];
    /**
     * 图片信息
     * @var array
     */
    protected $fileInfo;
    /**
     * 下载图片信息
     */
    protected $downFileInfo;

    /**
     * 要生成缩略图、水印的图片地址
     * @var string
     */
    protected $filePath;

    /**
     * 验证配置
     * @var string
     */
    protected $validate;

    /**
     * 保存路径
     * @var string
     */
    protected $path = '';

    /**
     * 是否自动裁剪
     * @var bool
     */
    protected $authThumb = true;

    protected function initialize(array $config)
    {
        $this->fileInfo = $this->downFileInfo = new \StdClass();
        $thumbConfig = $this->thumbConfig;
        $config['thumb'] = $config['thumb'] ?? [];
        $this->thumbConfig = $config['thumb'] ?? [];
        foreach ($config['thumb'] as $item) {
            if ($item == '' || $item == 0) {
                $this->thumbConfig = $thumbConfig;
            }
        }
        $this->waterConfig = array_merge($this->waterConfig, $config['water'] ?? []);
    }

    /**
     * 设置处理缩略图、水印图片路径
     * @param string $filePath
     * @return $this
     */
    public function setFilepath(string $filePath)
    {
        $this->filePath = substr($filePath, 0, 1) === '.' ? substr($filePath, 1) : $filePath;
        return $this;
    }

    /**
     * 是否自动裁剪
     * @param bool $auth
     * @return $this
     */
    public function setAuthThumb(bool $auth)
    {
        $this->authThumb = $auth;
        return $this;
    }

    /**
     * 上传文件路径
     * @param string $path
     * @return $this
     */
    public function to(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 获取文件信息
     * @return array
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * 检测是否是图片
     * @param $filePath
     * @return bool
     */
    protected function checkImage($filePath)
    {
        //获取图像信息
        $info = @getimagesize($filePath);
        //检测图像合法性
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            return false;
        }
        return true;
    }

    /**
     * 验证合法上传域名
     * @param string $url
     * @return string
     */
    protected function checkUploadUrl(string $url)
    {
        if ($url && strstr($url, 'http') === false) {
            $url = 'http://' . $url;
        }
        return $url;
    }

    /**
     * 获取系统配置
     * @return mixed
     */
    protected function getConfig()
    {
        $config['filesize'] = Config::get($this->configFile . '.filesize', []);
        $config['fileExt'] = Config::get($this->configFile . '.fileExt', []);
        $config['fileMime'] = Config::get($this->configFile . '.fileMime', []);
        return $config;
    }

    /**
     * 设置验证规则
     * @param array|null $validate
     * @return $this
     */
    public function validate(?array $validate = null)
    {
        if (is_null($validate)) {
            $validate = $this->getConfig();
        }
        $this->validate = $validate;
        return $this;
    }

    /**
     * 验证目录是否正确
     * @param string $key
     * @return false|string
     */
    protected function getUploadPath(string $key)
    {
        $path = ($this->path ? $this->path . '/' : '') . $key;
        if ($path && $path[0] === '/') {
            $path = substr($path, 1);
        }
        return $path;
    }

    /**
     * 提取文件名
     * @param string $path
     * @param string $ext
     * @return string
     */
    protected function saveFileName(string $path = null, string $ext = 'jpg')
    {
        return ($path ? substr(md5($path), 0, 5) : '') . date('YmdHis') . rand(0, 9999) . '.' . $ext;
    }

    /**
     * 提取文件后缀以及之前部分
     * @param string $path
     * @return false|string[]
     */
    protected function getFileName(string $path)
    {
        $_empty = ['', ''];
        if (!$path) return $_empty;
        if (strpos($path, '?')) {
            $_tarr = explode('?', $path);
            $path = trim($_tarr[0]);
        }
        $arr = explode('.', $path);
        if (!is_array($arr) || count($arr) <= 1) return $_empty;
        $ext_name = trim($arr[count($arr) - 1]);
        $ext_name = !$ext_name ? 'jpg' : $ext_name;
        return [explode('.' . $ext_name, $path)[0], $ext_name];
    }

    /**
     * 获取图片地址
     * @param string $filePath
     * @param bool $is_parse_url
     * @return string
     */
    protected function getFilePath(string $filePath = '', bool $is_parse_url = false)
    {
        $path = $filePath ?: $this->filePath;
        if ($is_parse_url) {
            $data = parse_url($path);
            //远程地址处理
            if (isset($data['host']) && isset($data['path'])) {
                if (file_exists(app()->getRootPath() . 'public' . $data['path'])) {
                    $path = $data['path'];
                }
            }
        }
        return $path;
    }

    /**
     * 获取文件类型和大小
     * @param string $url
     * @param bool $isData
     * @return array
     */
    protected function getFileHeaders(string $url, $isData = true)
    {
        stream_context_set_default(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $header['size'] = 0;
        $header['type'] = 'image/jpeg';
        if (!$isData) {
            return $header;
        }
        try {
            $headerArray = get_headers(str_replace('\\', '/', $url), true);
            if (!isset($headerArray['Content-Length'])) {
                $header['size'] = 0;
            }
            if (!isset($headerArray['Content-Type'])) {
                $header['type'] = 'image/jpeg';
            }
            if (is_array($headerArray['Content-Length']) && count($headerArray['Content-Length']) == 2) {
                $header['size'] = $headerArray['Content-Length'][1];
            }
            if (is_array($headerArray['Content-Type']) && count($headerArray['Content-Type']) == 2) {
                $header['type'] = $headerArray['Content-Type'][1];
            }
        } catch (\Exception $e) {
        }
        return $header;
    }

    /**
     * 获取上传信息
     * @return array
     */
    public function getUploadInfo()
    {
        if (isset($this->fileInfo->filePath)) {
            if (strstr($this->fileInfo->filePath, 'http') === false) {
                $url = request()->domain() . $this->fileInfo->filePath;
            } else {
                $url = $this->fileInfo->filePath;
            }
            $headers = $this->getFileHeaders($url);
            return [
                'name' => $this->fileInfo->fileName,
                'real_name' => $this->fileInfo->realName ?? '',
                'size' => $headers['size'] ?? 0,
                'type' => $headers['type'] ?? 'image/jpeg',
                'dir' => $this->fileInfo->filePath,
                'thumb_path' => $this->fileInfo->filePath,
                'thumb_path_big' => $this->fileInfo->filePathBig ?? '',
                'thumb_path_mid' => $this->fileInfo->filePathMid ?? '',
                'thumb_path_small' => $this->fileInfo->filePathSmall ?? '',
                'thumb_path_water' => $this->fileInfo->filePathWater ?? '',
                'time' => time(),
            ];
        } else {
            return [];
        }
    }

    /**
     * 获取下载信息
     * @return array
     */
    public function getDownloadInfo()
    {
        if (isset($this->downFileInfo->downloadFilePath)) {
            if (strstr($this->downFileInfo->downloadFilePath, 'http') === false) {
                $url = request()->domain() . $this->downFileInfo->downloadFilePath;
            } else {
                $url = $this->downFileInfo->downloadFilePath;
            }
            $headers = $this->getFileHeaders($url);
            return [
                'name' => $this->downFileInfo->downloadFileName,
                'real_name' => $this->downFileInfo->downloadRealName ?? '',
                'size' => $headers['size'] ?? 0,
                'type' => $headers['type'] ?? 'image/jpeg',
                'dir' => $this->downFileInfo->downloadFilePath ?? '',
                'thumb_path' => $this->downFileInfo->downloadFilePath ?? '',
                'time' => time(),
            ];
        } else {
            return [];
        }
    }

    /**
     * 文件上传
     * @return mixed
     */
    abstract public function move(string $file = 'file');

    /**
     * 文件流上传
     * @return mixed
     */
    abstract public function stream(string $fileContent, string $key = null);

    /**
     * 删除文件
     * @return mixed
     */
    abstract public function delete(string $filePath);

    /**
     * 实例化上传
     * @return mixed
     */
    abstract protected function app();

    /**
     * 拉取空间
     * @param string $region
     * @param bool $line
     * @param bool $shared
     * @return mixed
     */
    abstract public function listbuckets(string $region, bool $line = false, bool $shared = false);

    /**
     * 创建空间
     * @param string $name
     * @param string $region
     * @return mixed
     */
    abstract public function createBucket(string $name, string $region);

    /**
     * 获得区域
     * @return mixed
     */
    abstract public function getRegion();

    /**
     * 删除空间
     * @param string $name
     * @return mixed
     */
    abstract public function deleteBucket(string $name);

    /**
     * 绑定自定义域名
     * @param string $name
     * @param string $domain
     * @param string|null $region
     * @return mixed
     */
    abstract public function bindDomian(string $name, string $domain, string $region = null);

    /**
     * 设置跨域
     * @param string $name
     * @param string $region
     * @return mixed
     */
    abstract public function setBucketCors(string $name, string $region);

    /**
     * 获取上传密钥
     * @return mixed
     */
    abstract public function getTempKeys();

    /**
     * 获取缩略图
     * @return mixed
     */
    abstract public function thumb(string $filePath = '');

    /**
     * 添加水印
     * @return mixed
     */
    abstract public function water(string $filePath = '');

}
