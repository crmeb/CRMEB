<?php

namespace Fastknife\Domain\Vo;

use Fastknife\Utils\MathUtils;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic;

abstract class ImageVo
{
    /**
     * @var Image
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
        $this->image = ImageManagerStatic::make($src);
    }

    /**
     * 获取图片中某一个位置的rgba值
     * @param $x
     * @param $y
     * @return array
     */
    public function getPickColor($x, $y): array
    {
        if (!isset($this->pickMaps[$x][$y])) {
            $this->pickMaps[$x][$y] = $this->image->pickColor($x, $y);
        }
        return $this->pickMaps[$x][$y];
    }


    /**
     * 设置图片指定位置的颜色值
     */
    public function setPixel($color, $x, $y)
    {
        $this->image->pixel($color, $x, $y);
    }

    /**
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getBlurValue(int $x, int $y): array
    {
        $image = $this->image;
        $red = [];
        $green = [];
        $blue = [];
        $alpha = [];
        foreach ([
                     [0, 1], [0, -1],
                     [1, 0], [-1, 0],
                     [1, 1], [1, -1],
                     [-1, 1], [-1, -1],
                 ] as $distance) //边框取5个点，4个角取3个点，其余取8个点
        {
            $pointX = $x + $distance[0];
            $pointY = $y + $distance[1];
            if ($pointX < 0 || $pointX >= $image->getWidth() || $pointY < 0 || $pointY >= $image->height()) {
                continue;
            }
            [$r, $g, $b, $a] = $this->getPickColor($pointX, $pointY);
            $red[] = $r;
            $green[] = $g;
            $blue[] = $b;
            $alpha[] = $a;
        }
        return [MathUtils::avg($red), MathUtils::avg($green), MathUtils::avg($blue), MathUtils::avg($alpha)];
    }


    /**
     * 是否不透明
     * @param $x
     * @param $y
     * @return bool
     */
    public function isOpacity($x, $y): bool
    {
        return $this->getPickColor($x, $y)[3] > 0.5;
    }

    /**
     * 是否为边框
     * @param bool $isOpacity
     * @param int $x
     * @param int $y
     * @return bool
     */
    public function isBoundary(bool $isOpacity, int $x, int $y): bool
    {
        $image = $this->image;
        if ($x >= $image->width() - 1 || $y >= $image->height() - 1) {
            return false;
        }
        $right = [$x + 1, $y];
        $down = [$x, $y + 1];
        if (
            $isOpacity && !$this->isOpacity(...$right)
            || !$isOpacity && $this->isOpacity(...$right)
            || $isOpacity && !$this->isOpacity(...$down)
            || !$isOpacity && $this->isOpacity(...$down)
        ) {
            return true;
        }
        return false;
    }

    /**
     * 模糊图片
     * @param $targetX
     * @param $targetY
     */
    public function vagueImage($targetX, $targetY)
    {
        $blur = $this->getBlurValue($targetX, $targetY);
        $this->setPixel($blur, $targetX, $targetY);
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
        $width = $this->image->getWidth();
        $height = $this->image->getHeight();
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
    }
}