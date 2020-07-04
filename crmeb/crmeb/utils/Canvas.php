<?php

namespace crmeb\utils;

use crmeb\traits\LogicTrait;

/**
 * Class Canvas
 * @package crmeb\utils
 * @method $this setFileName(string $fileName) 设置文件名
 * @method $this setPath(string $path) 设置存放路径
 * @method $this setImageType(string $imageType) 设置图片类型
 * @method $this setBackgroundHeight(int $backgroundHeight) 设置背景高
 * @method $this setBackgroundWidth(int $backgroundWidth) 设置背景宽
 * @method $this setFontSize(int $fontSize) 设置字体大小
 * @method $this setFontColor($fontColor) 设置字体颜色
 * @method $this setFontLeft(int $fontLeft) 设置字体距离左侧位置
 * @method $this setFontTop(int $fontTop) 设置字体距离顶部位置
 * @method $this setFontText(string $fontText) 设置文字
 * @method $this setFontPath(string $fontPath) 设置字体文件路径
 * @method $this setFontAngle(int $fontAngle) 设置字体角度
 * @method $this setImageUrl(string $imageUrl) 设置图片路径
 * @method $this setImageLeft(int $imageLeft) 设置图片距离左侧位置
 * @method $this setImageTop(int $imageTop) 设置图片距离顶部位置
 * @method $this setImageRight(int $imageRight) 设置图片距离左侧位置
 * @method $this setImageStream(bool $imageStream) 设置图片是否未流文件
 * @method $this setImageBottom(int $imageBottom) 设置图片距离底部位置
 * @method $this setImageWidth(int $imageWidth) 设置图片宽
 * @method $this setImageHeight(int $imageHeight) 设置图片高
 * @method $this setImageOpacity(int $imageOpacity) 设置图片透明度
 */
class Canvas
{
    use LogicTrait;

    const FONT = 'static/font/Alibaba-PuHuiTi-Regular.otf';

    /**
     * 背景宽
     * @var int
     */
    protected $backgroundWidth = 600;

    /**
     * 背景高
     * @var int
     */
    protected $backgroundHeight = 1000;

    /**
     * 图片类型
     * @var string
     */
    protected $imageType = 'jpeg';

    /**
     * 保存地址
     * @var string
     */
    protected $path = 'uploads/routine/';

    /**
     * 文件名
     * @var string
     */
    protected $fileName;

    /**
     * 规则
     * @var array
     */
    protected $propsRule = ['fileName', 'path', 'imageType', 'backgroundHeight', 'backgroundWidth'];

    /**
     * 字体数据集
     * @var array
     */
    protected $fontValue = [];

    /**
     * 字体默认可设置vlaue
     * @var array
     */
    protected $defaultFontValue = [
        'fontSize' => 0,
        'fontColor' => '231,180,52',
        'fontLeft' => 0,
        'fontTop' => 0,
        'fontText' => '',
        'fontPath' => self::FONT,
        'fontAngle' => 0,
    ];

    protected $defaultFont;
    /**
     * 图片数据集
     * @var array
     */
    protected $imageValue = [];

    /**
     * 图片可设置属性
     * @var array
     */
    protected $defaultImageValue = [
        'imageUrl' => '',
        'imageLeft' => 0,
        'imageTop' => 0,
        'imageRight' => 0,
        'imageBottom' => 0,
        'imageWidth' => 0,
        'imageHeight' => 0,
        'imageOpacity' => 0,
        'imageStream' => false,
    ];

    protected $defaultImage;

    protected function __construct()
    {
        $this->defaultImage = $this->defaultImageValue;
        $this->defaultFont = $this->defaultFontValue;
    }

    /**
     * 创建一个新图象
     * @param string $file
     * @return array
     */
    public function createFrom(string $file): array
    {
        $imagesize = getimagesize($file);
        $type = image_type_to_extension($imagesize[2], true);
        switch ($type) {
            case '.png':
                $canvas = imagecreatefrompng($file);
                break;
            case '.jpeg':
                $canvas = imagecreatefromjpeg($file);
                break;
            case '.jpg':
                $canvas = imagecreatefromjpeg($file);
                break;
            case '.gif':
                $canvas = imagecreatefromgif($file);
                break;
        }
        return [$canvas, $imagesize];

    }

    /**
     * 放入字体
     * @return $this
     */
    public function pushFontValue()
    {
        array_push($this->fontValue, $this->defaultFontValue);
        $this->defaultFontValue = $this->defaultFont;
        return $this;
    }

    /**
     * 放入图片
     * @return $this
     */
    public function pushImageValue()
    {
        array_push($this->imageValue, $this->defaultImageValue);
        $this->defaultImageValue = $this->defaultImage;
        return $this;
    }

    /**
     * 创建背景
     * @param int $w
     * @param int $h
     * @return false|resource
     */
    public function createTrueColor(int $w = 0, int $h = 0)
    {
        return imagecreatetruecolor($w ? $w : $this->backgroundWidth, $h ? $h : $this->backgroundHeight);
    }


    /**
     * 开始画图
     * @param bool $force 生成错误时是否抛出异常
     * @return string
     * @throws \Exception
     */
    public function starDrawChart(bool $force = false): string
    {
        try {
            $image = $this->createTrueColor();

            foreach ($this->imageValue as $item) {
                if ($item['imageUrl']) {
                    if ($item['imageStream']) {
                        $res = getimagesizefromstring($item['imageUrl']);
                        $mer = imagecreatefromstring($item['imageUrl']);
                    } else {
                        [$mer, $res] = $this->createFrom($item['imageUrl']);
                    }
                    if ($mer && $res) {
                        $scrW = $res[0] ?? 0;
                        $scrH = $res[1] ?? 0;
                        $imageWidth = $item['imageWidth'] ?: $scrW;
                        $imageHeight = $item['imageHeight'] ?: $scrH;
                        imagecopyresampled($image, $mer, $item['imageLeft'], $item['imageTop'], $item['imageRight'], $item['imageBottom'], $imageWidth, $imageHeight, $scrW, $scrH);
                        unset($scrW, $scrH, $imageWidth, $imageHeight, $res, $mer);
                    }

                }
            }

            foreach ($this->fontValue as $val) {
                if (!is_array($val['fontColor']))
                    $fontColor = explode(',', $val['fontColor']);
                else
                    $fontColor = $val['fontColor'];
                if (count($fontColor) < 3)
                    throw new \RuntimeException('fontColor Separation of thousand bits');
                [$r, $g, $b] = $fontColor;
                $fontColor = imagecolorallocate($image, $r, $g, $b);
                $val['fontLeft'] = $val['fontLeft'] < 0 ? $this->backgroundWidth - abs($val['fontLeft']) : $val['fontLeft'];
                $val['fontTop'] = $val['fontTop'] < 0 ? $this->backgroundHeight - abs($val['fontTop']) : $val['fontTop'];
                imagettftext($image, $val['fontSize'], $val['fontAngle'], $val['fontLeft'], $val['fontTop'], $fontColor, $val['fontPath'], $val['fontText']);
                unset($r, $g, $b, $fontColor);
            }
            if (is_null($this->fileName)) {
                $this->fileName = md5(time());
            }

            $strlen = stripos($this->path, 'uploads');
            $path = $this->path;
            if ($strlen !== false) {
                $path = substr($this->path, 8);
            }
            make_path($path, 4, true);

            $save_file = $this->path . $this->fileName . '.' . $this->imageType;
            switch ($this->imageType) {
                case 'jpeg':
                case 'jpg':
                    imagejpeg($image, $save_file, 70);
                    break;
                case 'png':
                    imagepng($image, $save_file, 70);
                    break;
                case 'gif':
                    imagegif($image, $save_file, 70);
                    break;
                default:
                    throw new \RuntimeException('Incorrect type set:' . $this->imageType);
            }
            imagedestroy($image);

            return $save_file;
        } catch (\Throwable $e) {
            if ($force || $e instanceof \RuntimeException)
                throw new \Exception($e->getMessage());
            return '';
        }
    }

    /**
     * Magic access..
     *
     * @param $method
     * @param $args
     * @return $this
     */
    public function __call($method, $args): self
    {

        if (0 === stripos($method, 'set') && strlen($method) > 3) {
            $method = lcfirst(substr($method, 3));
        }

        $imageValueKes = array_keys($this->defaultImageValue);
        $fontValueKes = array_keys($this->defaultFontValue);

        if (in_array($method, $imageValueKes)) {
            $this->defaultImageValue[$method] = array_shift($args);
        }

        if (in_array($method, $fontValueKes)) {
            $this->defaultFontValue[$method] = array_shift($args);
        }

        if (in_array($method, $this->propsRule)) {
            $this->{$method} = array_shift($args);
        }

        return $this;
    }


}
