<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;


use Fastknife\Domain\Vo\BackgroundVo;
use Fastknife\Domain\Vo\ImageVo;
use Fastknife\Domain\Vo\PointVo;
use Fastknife\Domain\Vo\TemplateVo;

class BlockImage extends BaseImage
{
    const WHITE = [255, 255, 255, 1];

    /**
     * @var TemplateVo
     */
    protected $templateVo;

    /**
     * @var TemplateVo
     */
    protected $interfereVo;


    /**
     * @return TemplateVo
     */
    public function getTemplateVo(): TemplateVo
    {
        return $this->templateVo;
    }

    /**
     * @param TemplateVo $templateVo
     * @return self
     */
    public function setTemplateVo(TemplateVo $templateVo): self
    {
        $this->templateVo = $templateVo;
        return $this;
    }

    /**
     * @return TemplateVo
     */
    public function getInterfereVo(): TemplateVo
    {
        return $this->interfereVo;
    }

    /**
     * @param TemplateVo $interfereVo
     * @return static
     */
    public function setInterfereVo(TemplateVo $interfereVo): self
    {

        $this->interfereVo = $interfereVo;
        return $this;
    }

    public function run()
    {
        $flag = false;
        $this->cutByTemplate($this->templateVo, $this->backgroundVo, function ($param) use (&$flag) {
            if (!$flag) {
                //记录第一个点
                $this->setPoint(new PointVo($param[0], 5));//前端已将y值写死
                $flag = true;
            }
        });
        if (!empty($this->interfereVo)) {
            $this->cutByTemplate($this->interfereVo, $this->backgroundVo);
        }
        $this->makeWatermark($this->backgroundVo->image);
    }


    public function cutByTemplate(TemplateVo $templateVo, BackgroundVo $backgroundVo, $callable = null)
    {
        $template = $templateVo->image;
        $width = $template->getWidth();
        $height = $template->getHeight();
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                //背景图对应的坐标
                $bgX = $x + $templateVo->offset->x;
                $bgY = $y + $templateVo->offset->y;
                //是否不透明
                $isOpacity = $templateVo->isOpacity($x, $y);
                if ($isOpacity) {    //如果不透明
                    if ($callable instanceof \Closure) {
                        $callable([$bgX, $bgY]);
                    }
                    $backgroundVo->vagueImage($bgX, $bgY);//模糊背景图选区

                    $this->copyPickColor($backgroundVo, $bgX, $bgY, $templateVo, $x, $y);
                }
                if ($templateVo->isBoundary($isOpacity, $x, $y)) {
                    $backgroundVo->setPixel(self::WHITE, $bgX, $bgY);
                    $templateVo->setPixel(self::WHITE, $x, $y);
                }
            }
        }
    }

    /**
     * 把$source的颜色复制到$target上
     * @param ImageVo $source
     * @param ImageVo $target
     */

    protected function copyPickColor(ImageVo $source, $sourceX, $sourceY, ImageVo $target, $targetX, $targetY)
    {
        $bgRgba = $source->getPickColor($sourceX, $sourceY);
        $target->setPixel($bgRgba, $targetX, $targetY);//复制背景图片给模板
    }

    /**
     * 返回前端需要的格式
     * @return false|string[]
     */
    public function response($type = 'background')
    {
        $image = $type == 'background' ? $this->backgroundVo->image : $this->templateVo->image;
        $result = $image->encode('data-url')->getEncoded();
        //返回图片base64的第二部分
        return explode(',', $result)[1];
    }

    /**
     * 用来调试
     */
    public function echo($type = 'background')
    {
        $image = $type == 'background' ? $this->backgroundVo->image : $this->templateVo->image;
        die($image->response());
    }
}
