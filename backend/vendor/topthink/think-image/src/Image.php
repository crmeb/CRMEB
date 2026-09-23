<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think;

use think\image\Exception as ImageException;
use think\image\gif\Gif;

class Image
{

    /* 缩略图相关常量定义 */
    const THUMB_SCALING   = 1; //常量，标识缩略图等比例缩放类型
    const THUMB_FILLED    = 2; //常量，标识缩略图缩放后填充类型
    const THUMB_CENTER    = 3; //常量，标识缩略图居中裁剪类型
    const THUMB_NORTHWEST = 4; //常量，标识缩略图左上角裁剪类型
    const THUMB_SOUTHEAST = 5; //常量，标识缩略图右下角裁剪类型
    const THUMB_FIXED     = 6; //常量，标识缩略图固定尺寸缩放类型
    /* 水印相关常量定义 */
    const WATER_NORTHWEST = 1; //常量，标识左上角水印
    const WATER_NORTH     = 2; //常量，标识上居中水印
    const WATER_NORTHEAST = 3; //常量，标识右上角水印
    const WATER_WEST      = 4; //常量，标识左居中水印
    const WATER_CENTER    = 5; //常量，标识居中水印
    const WATER_EAST      = 6; //常量，标识右居中水印
    const WATER_SOUTHWEST = 7; //常量，标识左下角水印
    const WATER_SOUTH     = 8; //常量，标识下居中水印
    const WATER_SOUTHEAST = 9; //常量，标识右下角水印
    /* 翻转相关常量定义 */
    const FLIP_X = 1; //X轴翻转
    const FLIP_Y = 2; //Y轴翻转

    /**
     * 图像资源对象
     *
     * @var resource
     */
    protected $im;

    /** @var  Gif */
    protected $gif;

    /**
     * 图像信息，包括 width, height, type, mime, size
     *
     * @var array
     */
    protected $info;

    protected function __construct(\SplFileInfo $file)
    {
        //获取图像信息
        $info = @getimagesize($file->getPathname());

        //检测图像合法性
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            throw new ImageException('Illegal image file');
        }

        //设置图像信息
        $this->info = [
            'width'  => $info[0],
            'height' => $info[1],
            'type'   => image_type_to_extension($info[2], false),
            'mime'   => $info['mime'],
        ];

        //打开图像
        if ('gif' == $this->info['type']) {
            $this->gif = new Gif($file->getPathname());
            $this->im  = @imagecreatefromstring($this->gif->image());
        } else {
            $fun      = "imagecreatefrom{$this->info['type']}";
            $this->im = @$fun($file->getPathname());
        }

        if (empty($this->im)) {
            throw new ImageException('Failed to create image resources!');
        }

    }

    /**
     * 打开一个图片文件
     * @param \SplFileInfo|string $file
     * @return Image
     */
    public static function open($file)
    {
        if (is_string($file)) {
            $file = new \SplFileInfo($file);
        }
        if (!$file->isFile()) {
            throw new ImageException('image file not exist');
        }
        return new self($file);
    }

    /**
     * 保存图像
     * @param string      $pathname  图像保存路径名称
     * @param null|string $type      图像类型
     * @param int         $quality   图像质量
     * @param bool        $interlace 是否对JPEG类型图像设置隔行扫描
     * @return $this
     */
    public function save($pathname, $type = null, $quality = 80, $interlace = true)
    {
        //自动获取图像类型
        if (is_null($type)) {
            $type = $this->info['type'];
        } else {
            $type = strtolower($type);
        }
        //保存图像
        if ('jpeg' == $type || 'jpg' == $type) {
            //JPEG图像设置隔行扫描
            imageinterlace($this->im, $interlace);
            imagejpeg($this->im, $pathname, $quality);
        } elseif ('gif' == $type && !empty($this->gif)) {
            $this->gif->save($pathname);
        } elseif ('png' == $type) {
            //设定保存完整的 alpha 通道信息
            imagesavealpha($this->im, true);
            //ImagePNG生成图像的质量范围从0到9的
            imagepng($this->im, $pathname, min((int) ($quality / 10), 9));
        } else {
            $fun = 'image' . $type;
            $fun($this->im, $pathname);
        }

        return $this;
    }

    /**
     * 返回图像宽度
     * @return int 图像宽度
     */
    public function width()
    {
        return $this->info['width'];
    }

    /**
     * 返回图像高度
     * @return int 图像高度
     */
    public function height()
    {
        return $this->info['height'];
    }

    /**
     * 返回图像类型
     * @return string 图像类型
     */
    public function type()
    {
        return $this->info['type'];
    }

    /**
     * 返回图像MIME类型
     * @return string 图像MIME类型
     */
    public function mime()
    {
        return $this->info['mime'];
    }

    /**
     * 返回图像尺寸数组 0 - 图像宽度，1 - 图像高度
     * @return array 图像尺寸
     */
    public function size()
    {
        return [$this->info['width'], $this->info['height']];
    }

    /**
     * 旋转图像
     * @param int $degrees 顺时针旋转的度数
     * @return $this
     */
    public function rotate($degrees = 90)
    {
        do {
            $img = imagerotate($this->im, -$degrees, imagecolorallocatealpha($this->im, 0, 0, 0, 127));
            imagedestroy($this->im);
            $this->im = $img;
        } while (!empty($this->gif) && $this->gifNext());

        $this->info['width']  = imagesx($this->im);
        $this->info['height'] = imagesy($this->im);

        return $this;
    }

    /**
     * 翻转图像
     * @param integer $direction 翻转轴,X或者Y
     * @return $this
     */
    public function flip($direction = self::FLIP_X)
    {
        //原图宽度和高度
        $w = $this->info['width'];
        $h = $this->info['height'];

        do {

            $img = imagecreatetruecolor($w, $h);

            switch ($direction) {
                case self::FLIP_X:
                    for ($y = 0; $y < $h; $y++) {
                        imagecopy($img, $this->im, 0, $h - $y - 1, 0, $y, $w, 1);
                    }
                    break;
                case self::FLIP_Y:
                    for ($x = 0; $x < $w; $x++) {
                        imagecopy($img, $this->im, $w - $x - 1, 0, $x, 0, 1, $h);
                    }
                    break;
                default:
                    throw new ImageException('不支持的翻转类型');
            }

            imagedestroy($this->im);
            $this->im = $img;

        } while (!empty($this->gif) && $this->gifNext());

        return $this;
    }

    /**
     * 裁剪图像
     *
     * @param  integer $w      裁剪区域宽度
     * @param  integer $h      裁剪区域高度
     * @param  integer $x      裁剪区域x坐标
     * @param  integer $y      裁剪区域y坐标
     * @param  integer $width  图像保存宽度
     * @param  integer $height 图像保存高度
     *
     * @return $this
     */
    public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null)
    {
        //设置保存尺寸
        empty($width) && $width   = $w;
        empty($height) && $height = $h;
        do {
            //创建新图像
            $img = imagecreatetruecolor($width, $height);
            // 调整默认颜色
            $color = imagecolorallocate($img, 255, 255, 255);
            imagefill($img, 0, 0, $color);
            //裁剪
            imagecopyresampled($img, $this->im, 0, 0, $x, $y, $width, $height, $w, $h);
            imagedestroy($this->im); //销毁原图
            //设置新图像
            $this->im = $img;
        } while (!empty($this->gif) && $this->gifNext());
        $this->info['width']  = (int) $width;
        $this->info['height'] = (int) $height;
        return $this;
    }

    /**
     * 生成缩略图
     *
     * @param  integer $width  缩略图最大宽度
     * @param  integer $height 缩略图最大高度
     * @param int      $type   缩略图裁剪类型
     *
     * @return $this
     */
    public function thumb($width, $height, $type = self::THUMB_SCALING)
    {
        //原图宽度和高度
        $w = $this->info['width'];
        $h = $this->info['height'];
        /* 计算缩略图生成的必要参数 */
        switch ($type) {
            /* 等比例缩放 */
            case self::THUMB_SCALING:
                //原图尺寸小于缩略图尺寸则不进行缩略
                if ($w < $width && $h < $height) {
                    return $this;
                }
                //计算缩放比例
                $scale = min($width / $w, $height / $h);
                //设置缩略图的坐标及宽度和高度
                $x      = $y      = 0;
                $width  = $w * $scale;
                $height = $h * $scale;
                break;
            /* 居中裁剪 */
            case self::THUMB_CENTER:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);
                //设置缩略图的坐标及宽度和高度
                $w = $width / $scale;
                $h = $height / $scale;
                $x = ($this->info['width'] - $w) / 2;
                $y = ($this->info['height'] - $h) / 2;
                break;
            /* 左上角裁剪 */
            case self::THUMB_NORTHWEST:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);
                //设置缩略图的坐标及宽度和高度
                $x = $y = 0;
                $w = $width / $scale;
                $h = $height / $scale;
                break;
            /* 右下角裁剪 */
            case self::THUMB_SOUTHEAST:
                //计算缩放比例
                $scale = max($width / $w, $height / $h);
                //设置缩略图的坐标及宽度和高度
                $w = $width / $scale;
                $h = $height / $scale;
                $x = $this->info['width'] - $w;
                $y = $this->info['height'] - $h;
                break;
            /* 填充 */
            case self::THUMB_FILLED:
                //计算缩放比例
                if ($w < $width && $h < $height) {
                    $scale = 1;
                } else {
                    $scale = min($width / $w, $height / $h);
                }
                //设置缩略图的坐标及宽度和高度
                $neww = $w * $scale;
                $newh = $h * $scale;
                $x    = $this->info['width'] - $w;
                $y    = $this->info['height'] - $h;
                $posx = ($width - $w * $scale) / 2;
                $posy = ($height - $h * $scale) / 2;
                do {
                    //创建新图像
                    $img = imagecreatetruecolor($width, $height);
                    // 调整默认颜色
                    $color = imagecolorallocate($img, 255, 255, 255);
                    imagefill($img, 0, 0, $color);
                    //裁剪
                    imagecopyresampled($img, $this->im, $posx, $posy, $x, $y, $neww, $newh, $w, $h);
                    imagedestroy($this->im); //销毁原图
                    $this->im = $img;
                } while (!empty($this->gif) && $this->gifNext());
                $this->info['width']  = (int) $width;
                $this->info['height'] = (int) $height;
                return $this;
            /* 固定 */
            case self::THUMB_FIXED:
                $x = $y = 0;
                break;
            default:
                throw new ImageException('不支持的缩略图裁剪类型');
        }
        /* 裁剪图像 */
        return $this->crop($w, $h, $x, $y, $width, $height);
    }

    /**
     * 添加水印
     *
     * @param  string $source 水印图片路径
     * @param int     $locate 水印位置
     * @param int     $alpha  透明度
     * @return $this
     */
    public function water($source, $locate = self::WATER_SOUTHEAST, $alpha = 100)
    {
        if (!is_file($source)) {
            throw new ImageException('水印图像不存在');
        }
        //获取水印图像信息
        $info = getimagesize($source);
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            throw new ImageException('非法水印文件');
        }
        //创建水印图像资源
        $fun   = 'imagecreatefrom' . image_type_to_extension($info[2], false);
        $water = $fun($source);
        //设定水印图像的混色模式
        imagealphablending($water, true);
        /* 设定水印位置 */
        switch ($locate) {
            /* 右下角水印 */
            case self::WATER_SOUTHEAST:
                $x = $this->info['width'] - $info[0];
                $y = $this->info['height'] - $info[1];
                break;
            /* 左下角水印 */
            case self::WATER_SOUTHWEST:
                $x = 0;
                $y = $this->info['height'] - $info[1];
                break;
            /* 左上角水印 */
            case self::WATER_NORTHWEST:
                $x = $y = 0;
                break;
            /* 右上角水印 */
            case self::WATER_NORTHEAST:
                $x = $this->info['width'] - $info[0];
                $y = 0;
                break;
            /* 居中水印 */
            case self::WATER_CENTER:
                $x = ($this->info['width'] - $info[0]) / 2;
                $y = ($this->info['height'] - $info[1]) / 2;
                break;
            /* 下居中水印 */
            case self::WATER_SOUTH:
                $x = ($this->info['width'] - $info[0]) / 2;
                $y = $this->info['height'] - $info[1];
                break;
            /* 右居中水印 */
            case self::WATER_EAST:
                $x = $this->info['width'] - $info[0];
                $y = ($this->info['height'] - $info[1]) / 2;
                break;
            /* 上居中水印 */
            case self::WATER_NORTH:
                $x = ($this->info['width'] - $info[0]) / 2;
                $y = 0;
                break;
            /* 左居中水印 */
            case self::WATER_WEST:
                $x = 0;
                $y = ($this->info['height'] - $info[1]) / 2;
                break;
            default:
                /* 自定义水印坐标 */
                if (is_array($locate)) {
                    list($x, $y) = $locate;
                } else {
                    throw new ImageException('不支持的水印位置类型');
                }
        }
        do {
            //添加水印
            $src = imagecreatetruecolor($info[0], $info[1]);
            // 调整默认颜色
            $color = imagecolorallocate($src, 255, 255, 255);
            imagefill($src, 0, 0, $color);
            imagecopy($src, $this->im, 0, 0, $x, $y, $info[0], $info[1]);
            imagecopy($src, $water, 0, 0, 0, 0, $info[0], $info[1]);
            imagecopymerge($this->im, $src, $x, $y, 0, 0, $info[0], $info[1], $alpha);
            //销毁零时图片资源
            imagedestroy($src);
        } while (!empty($this->gif) && $this->gifNext());
        //销毁水印资源
        imagedestroy($water);
        return $this;
    }

    /**
     * 图像添加文字
     *
     * @param  string  $text   添加的文字
     * @param  string  $font   字体路径
     * @param  integer $size   字号
     * @param  string  $color  文字颜色
     * @param int      $locate 文字写入位置
     * @param  integer $offset 文字相对当前位置的偏移量
     * @param  integer $angle  文字倾斜角度
     *
     * @return $this
     * @throws ImageException
     */
    public function text($text, $font, $size, $color = '#00000000',
        $locate = self::WATER_SOUTHEAST, $offset = 0, $angle = 0) {

        if (!is_file($font)) {
            throw new ImageException("不存在的字体文件：{$font}");
        }
        //获取文字信息
        $info = imagettfbbox($size, $angle, $font, $text);
        $minx = min($info[0], $info[2], $info[4], $info[6]);
        $maxx = max($info[0], $info[2], $info[4], $info[6]);
        $miny = min($info[1], $info[3], $info[5], $info[7]);
        $maxy = max($info[1], $info[3], $info[5], $info[7]);
        /* 计算文字初始坐标和尺寸 */
        $x = $minx;
        $y = abs($miny);
        $w = $maxx - $minx;
        $h = $maxy - $miny;
        /* 设定文字位置 */
        switch ($locate) {
            /* 右下角文字 */
            case self::WATER_SOUTHEAST:
                $x += $this->info['width'] - $w;
                $y += $this->info['height'] - $h;
                break;
            /* 左下角文字 */
            case self::WATER_SOUTHWEST:
                $y += $this->info['height'] - $h;
                break;
            /* 左上角文字 */
            case self::WATER_NORTHWEST:
                // 起始坐标即为左上角坐标，无需调整
                break;
            /* 右上角文字 */
            case self::WATER_NORTHEAST:
                $x += $this->info['width'] - $w;
                break;
            /* 居中文字 */
            case self::WATER_CENTER:
                $x += ($this->info['width'] - $w) / 2;
                $y += ($this->info['height'] - $h) / 2;
                break;
            /* 下居中文字 */
            case self::WATER_SOUTH:
                $x += ($this->info['width'] - $w) / 2;
                $y += $this->info['height'] - $h;
                break;
            /* 右居中文字 */
            case self::WATER_EAST:
                $x += $this->info['width'] - $w;
                $y += ($this->info['height'] - $h) / 2;
                break;
            /* 上居中文字 */
            case self::WATER_NORTH:
                $x += ($this->info['width'] - $w) / 2;
                break;
            /* 左居中文字 */
            case self::WATER_WEST:
                $y += ($this->info['height'] - $h) / 2;
                break;
            default:
                /* 自定义文字坐标 */
                if (is_array($locate)) {
                    list($posx, $posy) = $locate;
                    $x += $posx;
                    $y += $posy;
                } else {
                    throw new ImageException('不支持的文字位置类型');
                }
        }
        /* 设置偏移量 */
        if (is_array($offset)) {
            $offset        = array_map('intval', $offset);
            list($ox, $oy) = $offset;
        } else {
            $offset = intval($offset);
            $ox     = $oy     = $offset;
        }
        /* 设置颜色 */
        if (is_string($color) && 0 === strpos($color, '#')) {
            $color = str_split(substr($color, 1), 2);
            $color = array_map('hexdec', $color);
            if (empty($color[3]) || $color[3] > 127) {
                $color[3] = 0;
            }
        } elseif (!is_array($color)) {
            throw new ImageException('错误的颜色值');
        }
        do {
            /* 写入文字 */
            $col = imagecolorallocatealpha($this->im, $color[0], $color[1], $color[2], $color[3]);
            imagettftext($this->im, $size, $angle, $x + $ox, $y + $oy, $col, $font, $text);
        } while (!empty($this->gif) && $this->gifNext());
        return $this;
    }

    /**
     * 切换到GIF的下一帧并保存当前帧
     */
    protected function gifNext()
    {
        ob_start();
        ob_implicit_flush(0);
        imagegif($this->im);
        $img = ob_get_clean();
        $this->gif->image($img);
        $next = $this->gif->nextImage();
        if ($next) {
            imagedestroy($this->im);
            $this->im = imagecreatefromstring($next);
            return $next;
        } else {
            imagedestroy($this->im);
            $this->im = imagecreatefromstring($this->gif->image());
            return false;
        }
    }

    /**
     * 析构方法，用于销毁图像资源
     */
    public function __destruct()
    {
        empty($this->im) || imagedestroy($this->im);
    }

}
