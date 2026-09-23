<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template\Shape;

interface ShapeDrawerInterface
{
    /**
     * 生成高分辨率的形状 Mask (未缩放)
     * @param int $scale 超采样倍数
     * @return resource|\GdImage 返回绘制好的大图资源
     */
    public function createMask(int $scale);

    /**
     * 获取形状的原始尺寸 (缩放前)
     * @return array [width, height]
     */
    public function getOriginalSize(): array;
}
