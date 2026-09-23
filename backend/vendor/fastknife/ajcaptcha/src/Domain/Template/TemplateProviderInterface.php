<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template;

use Fastknife\Domain\Vo\TemplateVo;

interface TemplateProviderInterface
{
    /**
     * 获取模板 Vo (包含图片资源和偏移量等信息)
     * @param int $bgWidth 背景图宽度 (用于计算偏移)
     * @param int $bgHeight 背景图高度
     * @return TemplateVo
     */
    public function getTemplateVo(int $bgWidth, int $bgHeight): TemplateVo;

    /**
     * 获取干扰模板 Vo
     * @param int $bgWidth
     * @param int $bgHeight
     * @param TemplateVo $targetVo 目标模板(用于避让)
     * @return TemplateVo
     */
    public function getInterfereVo(int $bgWidth, int $bgHeight, TemplateVo $targetVo): TemplateVo;
}
