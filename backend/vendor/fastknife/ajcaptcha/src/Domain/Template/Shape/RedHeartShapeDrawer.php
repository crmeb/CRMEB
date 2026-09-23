<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template\Shape;

use Fastknife\Utils\ImageUtils;

class RedHeartShapeDrawer implements ShapeDrawerInterface
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

        // 绘制心形 (贝塞尔曲线模拟)
        // 心形方程：
        // x = 16 * sin^3(t)
        // y = 13 * cos(t) - 5 * cos(2t) - 2 * cos(3t) - cos(4t)
        
        // 归一化并缩放到画布
        $centerX = $w / 2;
        $centerY = $h / 2; // 稍微偏上一点？心形尖端在下
        // 心形高度约 34单位 (-17~+17)，宽度约 32单位 (-16~+16)
        // 我们需要把它撑满 $w, $h
        // 放大系数 R
        $R = min($w, $h) / 35; 

        $points = [];
        for ($t = 0; $t <= 2 * M_PI; $t += 0.01) {
            $x = 16 * pow(sin($t), 3);
            $y = 13 * cos($t) - 5 * cos(2*$t) - 2 * cos(3*$t) - cos(4*$t);
            
            // 翻转Y轴 (GD y向下增加)
            $px = $centerX + $x * $R;
            $py = $centerY - $y * $R; 
            
            $points[] = $px;
            $points[] = $py;
        }

        ImageUtils::filledPolygon($big, $points, $contentColor);
        ImageUtils::polygon($big, $points, $shadowColor);

        return $big;
    }
}
