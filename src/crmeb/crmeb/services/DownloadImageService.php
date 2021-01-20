<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services;


use think\exception\ValidateException;
use think\Image;

class DownloadImageService
{
    //是否生成缩略图
    protected $thumb = false;
    //缩略图宽度
    protected $thumbWidth = 300;
    //缩略图高度
    protected $thumHeight = 300;

    protected $rules = ['thumb','thumbWidth','thumHeight'];

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
     * @param $url
     * @param string $name
     * @param int $upload_type
     * @return mixed
     */
    public function downloadImage($url, $name = '', $upload_type = 1)
    {
        if (!$name) {
            //TODO 获取要下载的文件名称
            $downloadImageInfo = $this->getImageExtname($url);
            $name = $downloadImageInfo['file_name'];
            if (!$name) throw new ValidateException('上传图片不存在');
        }
        if (strstr($url, 'http://') === false && strstr($url, 'https://') === false) {
            $url = 'http:' . $url;
        }

        ob_start();
        readfile($url);
        $content = ob_get_contents();
        ob_end_clean();

        $size = strlen(trim($content));
        if (!$content || $size <= 2) throw new ValidateException('图片流获取失败');
        $date_dir = date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
        $upload = UploadService::init($upload_type);
        if ($upload->to('attach/' . $date_dir)->stream($content, $name) === false) {
            throw new ValidateException('图片下载失败');
        }
        $imageInfo = $upload->getUploadInfo();
        $path = '.' . $imageInfo['dir'];
        if ($this->thumb) {
            Image::open($path)->thumb($this->thumbWidth, $this->thumHeight)->save($path);
            $this->thumb = false;
        }

        $date['path'] = $path;
        $date['name'] = $imageInfo['name'];
        $date['size'] = $imageInfo['size'];
        $date['mime'] = $imageInfo['type'];
        $date['image_type'] = $upload_type;
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
            if ($name === 'data') {
                $this->{$name} = $arguments;
            } else {
                $this->{$name} = $arguments[0] ?? null;
            }
            return $this;
        } else {
            throw new \RuntimeException('Method does not exist' . __CLASS__ . '->' . $name . '()');
        }
    }
}
