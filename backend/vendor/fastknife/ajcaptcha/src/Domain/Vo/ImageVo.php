<?php

namespace Fastknife\Domain\Vo;

use Fastknife\Utils\ImageUtils;
use Fastknife\Utils\MathUtils;

abstract class ImageVo
{
    /**
     * @var resource|\GdImage
     */
    public $image;

    public $src;

    private $pickMaps = [];

    private $finishCallback;

    public function __construct($src)
    {
        $this->src = $src;
        $this->initImage($src);
    }

    public function initImage($src)
    {
        // 如果 $src 是 null (Drawing 模式手动注入) 则不处理
        if ($src !== null) {
            $this->image = ImageUtils::read($src);
        }
    }

    /**
     * 获取图片中某一个位置的rgba值
     * @param $x
     * @param $y
     * @return array [r, g, b, a] (a: 0-1 float for compat, or 0-127 int? Intervention v2 returns a as 0-1 float where 0 is transparent?)
     * Intervention v2 pickColor:
     * array(4) {
     *   [0] => int(255)
     *   [1] => int(255)
     *   [2] => int(255)
     *   [3] => float(1) // 1 is opaque, 0 is transparent? Or 0 is opaque?
     * }
     * Intervention v2: alpha 0 (transparent) -> 1 (opaque).
     * GD: 0 (opaque) -> 127 (transparent).
     *
     * 为了兼容旧代码 check 逻辑: $this->getPickColor($x, $y)[3] > 0.5 (opaque)
     * 我们需要把 GD 的 0-127 转换为 0-1 (opaque).
     * GD: 0 -> 1.0, 127 -> 0.0
     */
    public function getPickColor($x, $y): array
    {
        if (!isset($this->pickMaps[$x][$y])) {
            $rgb = imagecolorat($this->image, (int)$x, (int)$y);
            $colors = imagecolorsforindex($this->image, $rgb);
            
            // Convert GD alpha (0-127 transparent) to Intervention alpha (0-1 opaque)
            // 127 (transparent) -> 0
            // 0 (opaque) -> 1
            $alpha = 1 - ($colors['alpha'] / 127);
            
            $this->pickMaps[$x][$y] = [
                $colors['red'],
                $colors['green'],
                $colors['blue'],
                $alpha
            ];
        }
        return $this->pickMaps[$x][$y];
    }


    /**
     * 设置图片指定位置的颜色值
     * @param array $color [r, g, b, a] (Intervention format)
     * @param int $x
     * @param int $y
     */
    public function setPixel($color, $x, $y)
    {
        // Convert Intervention alpha to GD alpha
        // 1 (opaque) -> 0
        // 0 (transparent) -> 127
        $alpha = 0;
        if (isset($color[3])) {
            $alpha = (int)((1 - $color[3]) * 127);
        }
        
        $col = imagecolorallocatealpha($this->image, (int)$color[0], (int)$color[1], (int)$color[2], (int)$alpha);
        imagesetpixel($this->image, (int)$x, (int)$y, $col);
        // imagecolorallocatealpha 可能会耗尽调色板 (对于真彩色不需要担心)
    }





    /**
     * @return array
     */
    public function getPickMaps(): array
    {
        return $this->pickMaps;
    }

    /**
     * @param array $pickMaps
     */
    public function setPickMaps(array $pickMaps): void
    {
        $this->pickMaps = $pickMaps;
    }

    /**
     * 提前初始化像素
     */
    public function preparePickMaps()
    {
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $this->getPickColor($x, $y);
            }
        }
    }

    public function setFinishCallback($finishCallback){
        $this->finishCallback = $finishCallback;
    }

    public function __destruct()
    {
        if(!empty($this->finishCallback) && $this->finishCallback instanceof \Closure){
            ($this->finishCallback)($this);
        }
        // 释放 GD 资源
        ImageUtils::destroy($this->image);
    }
}
