<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;


use Fastknife\Domain\Template\TemplateProviderInterface;
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
     * @var TemplateProviderInterface
     */
    protected $templateProvider;

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
     * 设置模板提供者
     * @param TemplateProviderInterface $provider
     */
    public function setTemplateProvider(TemplateProviderInterface $provider)
    {
        $this->templateProvider = $provider;
    }


    /**
     * 获取剪切模板Vo
     * @param BackgroundVo $backgroundVo
     * @return TemplateVo
     */
    public function getTemplateVo(BackgroundVo $backgroundVo): TemplateVo
    {
        $background = $backgroundVo->image;
        $bgWidth = imagesx($background);
        $bgHeight = imagesy($background);

        return $this->templateProvider->getTemplateVo($bgWidth, $bgHeight);
    }

    /**
     * 获取干扰模板Vo
     * @param BackgroundVo $backgroundVo
     * @param TemplateVo $targetVo
     * @return TemplateVo
     */
    public function getInterfereVo(BackgroundVo $backgroundVo, TemplateVo $targetVo): TemplateVo
    {
        $background = $backgroundVo->image;
        $bgWidth = imagesx($background);
        $bgHeight = imagesy($background);
        
        return $this->templateProvider->getInterfereVo($bgWidth, $bgHeight, $targetVo);
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
