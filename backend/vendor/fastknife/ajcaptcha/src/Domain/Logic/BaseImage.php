<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;


use Fastknife\Domain\Vo\BackgroundVo;
use Fastknife\Utils\ImageUtils;

abstract class BaseImage
{
    protected $watermark;

    /**
     * @var BackgroundVo
     */
    protected $backgroundVo;

    protected $fontFile;
    protected $point;

    /**
     * @return mixed
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param $point
     * @return self
     */
    public function setPoint($point):self
    {
        $this->point = $point;
        return $this;
    }


    /**
     * 绘制水印
     * @param resource|\GdImage $image
     */
    protected function makeWatermark($image)
    {
        if (! empty($this->watermark)) {
            // 计算文字包围盒，用于确定位置
            $fontSize = $this->watermark['fontsize'];
            $text = $this->watermark['text'];
            
            $info = imagettfbbox((float)$fontSize, 0, $this->fontFile, $text);
            $minX = min($info[0], $info[2], $info[4], $info[6]);
            $maxX = max($info[0], $info[2], $info[4], $info[6]);
            $minY = min($info[1], $info[3], $info[5], $info[7]);
            $maxY = max($info[1], $info[3], $info[5], $info[7]);
            
            $textW = $maxX - $minX;
            $textH = $maxY - $minY;

            $imgW = imagesx($image);
            $imgH = imagesy($image);

            // 目标：右下角，留出一点余白
            // 余白：fontSize / 2
            $margin = $fontSize / 2;
            
            // ImageUtils::text 默认 align=left, valign=bottom (基线)
            // 如果我们使用 align=right, valign=bottom
            // 则 x 应该是 imgW - margin
            // y 应该是 imgH - margin
            
            // 原逻辑：
            // $x += $image->getWidth() - $this->watermark['fontsize']/2;
            // $y += $image->getHeight() - $h;
            // $image->text(..., align='right', valign='bottom')
            
            // 使用 ImageUtils
            // 颜色 hex to rgb
            $color = $this->hex2rgb($this->watermark['color']);
            
            ImageUtils::text(
                $image,
                $text,
                (int)($imgW - $margin),
                (int)($imgH - $margin), // 大概位置，ImageUtils 内部 valign='bottom' 会修正为基线
                $this->fontFile,
                $fontSize,
                [$color[0], $color[1], $color[2], 1],
                0,
                'right',
                'bottom'
            );
        }
    }
    
    private function hex2rgb($hexColor)
    {
        $hex = str_replace("#", "", $hexColor);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return [$r, $g, $b];
    }


    /**
     * @param mixed $watermark
     * @return self
     */
    public function setWatermark($watermark): self
    {
        $this->watermark = $watermark;
        return $this;
    }


    /**
     * @param BackgroundVo $backgroundVo
     * @return $this
     */
    public function setBackgroundVo(BackgroundVo $backgroundVo):self
    {
        $this->backgroundVo = $backgroundVo;
        return $this;
    }

    /**
     * @return BackgroundVo
     */
    public function getBackgroundVo(): BackgroundVo
    {
        return $this->backgroundVo;
    }

    /**
     * @param $file
     * @return static
     */
    public function setFontFile($file): self
    {
        $this->fontFile = $file;
        return $this;
    }

    public abstract function run();
}
