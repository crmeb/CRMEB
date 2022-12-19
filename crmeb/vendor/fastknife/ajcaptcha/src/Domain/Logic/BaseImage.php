<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;


use Fastknife\Domain\Vo\BackgroundVo;
use Intervention\Image\AbstractFont as Font;
use Intervention\Image\Image;

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
     * @return WordImage
     */
    public function setPoint($point):self
    {
        $this->point = $point;
        return $this;
    }


    protected function makeWatermark(Image $image)
    {
        if (! empty($this->watermark)) {
            $info = imagettfbbox($this->watermark['fontsize'], 0, $this->fontFile, $this->watermark['text']);
            $minX = min($info[0], $info[2], $info[4], $info[6]);
            $minY = min($info[1], $info[3], $info[5], $info[7]);
            $maxY = max($info[1], $info[3], $info[5], $info[7]);
            $x = $minX;
            $y = abs($minY);
            /* 计算文字初始坐标和尺寸 */
            $h = $maxY - $minY;
            $x += $image->getWidth() - $this->watermark['fontsize']/2; //留出半个单位字体像素的余白，不至于水印紧贴着右边
            $y += $image->getHeight() - $h;
            $image->text($this->watermark['text'], $x, $y, function (Font $font) {
                $font->file($this->fontFile);
                $font->size($this->watermark['fontsize']);
                $font->color($this->watermark['color']);
                $font->align('right');
                $font->valign('bottom');
            });
        }
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
