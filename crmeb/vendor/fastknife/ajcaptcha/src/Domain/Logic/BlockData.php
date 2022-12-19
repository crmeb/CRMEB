<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;


use Fastknife\Domain\Vo\BackgroundVo;
use Fastknife\Domain\Vo\OffsetVo;
use Fastknife\Domain\Vo\TemplateVo;
use Fastknife\Exception\BlockException;
use Fastknife\Utils\RandomUtils;

class BlockData extends BaseData
{

    protected $defaultBackgroundPath = '/resources/defaultImages/jigsaw/original/';

    protected $faultOffset;

    /**
     * @return mixed
     */
    public function getFaultOffset()
    {
        return $this->faultOffset;
    }

    /**
     * @param mixed $faultOffset
     */
    public function setFaultOffset($faultOffset): self
    {
        $this->faultOffset = $faultOffset;
        return $this;
    }


    /**
     * 获取剪切模板Vo
     * @param BackgroundVo $backgroundVo
     * @param array $templates
     * @return TemplateVo
     */
    public function getTemplateVo(BackgroundVo $backgroundVo, array $templates = []): TemplateVo
    {
        $background = $backgroundVo->image;
        //初始偏移量，让模板图在背景的右1/2位置
        $bgWidth = intval($background->getWidth() / 2);
        //随机获取一张图片
        $src = $this->getRandImage($this->getTemplateImages($templates));

        $templateVo = new TemplateVo($src);

        //随机获取偏移量
        $offset = RandomUtils::getRandomInt(0, $bgWidth - $templateVo->image->getWidth() - 1);

        $templateVo->setOffset(new OffsetVo($offset + $bgWidth, 0));
        return $templateVo;
    }



    public function getInterfereVo(BackgroundVo $backgroundVo, TemplateVo $templateVo, $templates = []): TemplateVo
    {
        //背景
        $background = $backgroundVo->image;
        //模板库去重
        $templates = $this->exclude($this->getTemplateImages($templates), $templateVo->src);

        //随机获取一张模板图
        $src = $this->getRandImage($templates);

        $interfereVo = new TemplateVo($src);

        $maxOffsetX = intval($templateVo->image->getWidth()/2);
        do {
            //随机获取偏移量
            $offsetX = RandomUtils::getRandomInt(0, $background->getWidth() - $templateVo->image->getWidth() - 1);

            //不与原模板重复
            if (
                abs($templateVo->offset->x - $offsetX) > $maxOffsetX
            ) {
                $offsetVO = new OffsetVo($offsetX, 0);
                $interfereVo->setOffset($offsetVO);
                return $interfereVo;
            }
        } while (true);
    }


    protected function getTemplateImages(array $templates = [])
    {
        $dir = dirname(__DIR__, 3) . '/resources/defaultImages/jigsaw/slidingBlock/';
        return $this->getDefaultImage($dir, $templates);
    }

    /**
     * 排除
     * @param $templates
     * @param $exclude
     * @return array
     */
    protected function exclude($templates, $exclude): array
    {
        if (false !== ($key = array_search($exclude, $templates))) {
            array_splice($templates,$key,1);
        }
        return $templates;
    }



    /**
     * @param $originPoint
     * @param $targetPoint
     * @return void
     */
    public function check($originPoint, $targetPoint)
    {
        if (
            abs($originPoint->x - $targetPoint->x) <= $this->faultOffset
            && $originPoint->y == $targetPoint->y
        ) {
            return;
        }
        throw new BlockException('验证失败！');
    }

}
