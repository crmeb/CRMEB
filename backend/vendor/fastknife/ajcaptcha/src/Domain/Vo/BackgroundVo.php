<?php

namespace Fastknife\Domain\Vo;

use Fastknife\Utils\MathUtils;

class BackgroundVo extends ImageVo
{
    private $blurAreas = [];


    public function __construct($src)
    {
        parent::__construct($src);
        // 设置消除锯齿
        imageantialias($this->image, true);
    }



    /**
     * 记录模糊图片
     * @param $targetX
     * @param $targetY
     */
    public function recordBlurImage($targetX, $targetY)
    {
        $blurColor = $this->getBlurValue($targetX, $targetY);
        $this->setBlurPick($targetX, $targetY, $blurColor);
    }

    /**
     * 重写父类方法，优先从模糊缓存获取颜色
     */
    public function getPickColor($x, $y): array
    {
        // 如果有模糊缓存，使用缓存值
        if (isset($this->blurAreas[$x][$y])) {
            return $this->blurAreas[$x][$y];
        }
        // 否则调用父类方法从图像读取
        return parent::getPickColor($x, $y);
    }

    /**
     * 获取模糊值（BackgroundVo 专用）
     * @param int $x
     * @param int $y
     * @return array
     */
    private function getBlurValue(int $x, int $y): array
    {
        $image = $this->image;
        $red = [];
        $green = [];
        $blue = [];
        $alpha = [];
        $w = imagesx($image);
        $h = imagesy($image);
        
        foreach ([
                     [0, 1], [0, -1],
                     [1, 0], [-1, 0],
                     [1, 1], [1, -1],
                     [-1, 1], [-1, -1],
                 ] as $distance) //边框取5个点，4个角取3个点，其余取8个点
        {
            $pointX = $x + $distance[0];
            $pointY = $y + $distance[1];
            if ($pointX < 0 || $pointX >= $w || $pointY < 0 || $pointY >= $h) {
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

    public function setBlurPick($x, $y, $color)
    {
        $this->blurAreas[$x][$y] = $color;
    }

    public function blur(int $num = 1)
    {
        while ($num > 1 && $num <= 10) {
            foreach ($this->blurAreas as $x => $arr) {
                foreach ($arr as $y => $color) {
                    $this->recordBlurImage($x, $y);
                }
            }
            $num--;
        }
        $this->flush();
    }

    private function flush()
    {
        foreach ($this->blurAreas as $x => $arr) {
            foreach ($arr as $y => $color) {
                $this->setPixel($color, $x, $y);
            }
        }
    }


}