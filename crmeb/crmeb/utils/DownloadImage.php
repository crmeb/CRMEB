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

namespace crmeb\utils;


use app\services\other\UploadService;
use crmeb\exceptions\AdminException;
use think\Image;

/**
 * 下载图片到本地
 * Class DownloadImage
 * @package crmeb\utils
 * @method $this thumb(bool $thumb) 是否生成缩略图
 * @method $this thumbWidth(int $thumbWidth) 缩略图宽度
 * @method $this thumHeight(int $thumHeight) 缩略图宽度
 * @method $this path(int $path) 存储位置
 */
class DownloadImage
{
    //是否生成缩略图
    protected $thumb = false;
    //缩略图宽度
    protected $thumbWidth = 300;
    //缩略图高度
    protected $thumHeight = 300;
    //存储位置
    protected $path = 'attach';

    /**
     * @var string[]
     */
    protected $rules = ['thumb', 'thumbWidth', 'thumHeight', 'path'];

    /**
     * 获取即将要下载的图片扩展名
     * @param string $url
     * @param string $ex
     * @return array|string[]
     */
    public function getImageExtname($url = '', $ex = 'jpg')
    {
        $_empty = ['file_name' => '', 'ext_name' => $ex];
        if (!$url) return $_empty;
        if (strpos($url, '?')) {
            $_tarr = explode('?', $url);
            $url = trim($_tarr[0]);
        }
        $arr = explode('.', $url);
        if (!is_array($arr) || count($arr) <= 1) return $_empty;
        $ext_name = trim($arr[count($arr) - 1]);
        $ext_name = !$ext_name ? $ex : $ext_name;
        return ['file_name' => md5($url) . '.' . $ext_name, 'ext_name' => $ext_name];
    }

    /**
     * 下载图片
     * @param string $url
     * @param string $name
     * @param int $upload_type
     * @return mixed
     */
    public function downloadImage(string $url, $name = '')
    {
        if (!$name) {
            //TODO 获取要下载的文件名称
            $downloadImageInfo = $this->getImageExtname($url);
            $ext = $downloadImageInfo['ext_name'];
            $name = $downloadImageInfo['file_name'];
            if (!$name) throw new AdminException(400725);
        } else {
            $ext = $this->getImageExtname($name)['ext_name'];
        }
        if (in_array($ext, ['php', 'js', 'html'])) {
            throw new AdminException(400558);
        }
        if (strstr($url, 'http://') === false && strstr($url, 'https://') === false) {
            $url = 'http:' . $url;
        }
        $url = str_replace('https://', 'http://', $url);
        if ($this->path == 'attach') {
            $date_dir = date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
            $to_path = $this->path . '/' . $date_dir;
        } else {
            $to_path = $this->path;
        }
        $upload = UploadService::init(1);
        if (!file_exists($upload->uploadDir($to_path) . '/' . $name)) {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
            $size = strlen(trim($content));
            if (!$content || $size <= 2) throw new AdminException(400726);
            if ($upload->to($to_path)->down($content, $name) === false) {
                throw new AdminException(400727);
            }
            $imageInfo = $upload->getDownloadInfo();
            $path = $imageInfo['dir'];
            if ($this->thumb) {
                Image::open(root_path() . 'public' . $path)->thumb($this->thumbWidth, $this->thumHeight)->save(root_path() . 'public' . $path);
                $this->thumb = false;
            }
        } else {
            $path = '/uploads/' . $to_path . '/' . $name;
            $imageInfo['name'] = $name;
        }
        $date['path'] = $path;
        $date['name'] = $imageInfo['name'];
        $date['size'] = $imageInfo['size'] ?? '';
        $date['mime'] = $imageInfo['type'] ?? '';
        $date['image_type'] = 1;
        $date['is_exists'] = false;
        return $date;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, $this->rules)) {
            if ($name === 'path') {
                $this->{$name} = $arguments[0] ?? 'attach';
            } else {
                $this->{$name} = $arguments[0] ?? null;
            }
            return $this;
        } else {
            throw new \RuntimeException('Method does not exist' . __CLASS__ . '->' . $name . '()');
        }
    }

}
