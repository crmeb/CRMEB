<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template\Shape;

use Fastknife\Utils\ImageUtils;

class DiamondShapeDrawer implements ShapeDrawerInterface
{
    private $width = 50;
    private $height = 50;

    public function getOriginalSize(): array
    {
        return [$this->width, $this->height];
    }

    public function createMask(int $scale)
    {
        $w = $this->width * $scale;
        $h = $this->height * $scale;

        $big = ImageUtils::create($w, $h);
        imageantialias($big, true);

        $contentColor = imagecolorallocatealpha($big, 220, 220, 220, 40);
        $shadowColor = imagecolorallocatealpha($big, 255, 255, 255, 50);

        // 绘制方片 (菱形)
        $cx = $w / 2;
        $cy = $h / 2;
        
        // 留一点边距
        $margin = 2 * $scale;
        $halfW = ($w / 2) - $margin;
        $halfH = ($h / 2) - $margin;

        $points = [
            $cx, $margin,          // Top
            $w - $margin, $cy,     // Right
            $cx, $h - $margin,     // Bottom
            $margin, $cy           // Left
        ];

        ImageUtils::filledPolygon($big, $points, $contentColor);
        ImageUtils::polygon($big, $points, $shadowColor);

        return $big;
    }
}
