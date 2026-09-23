<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template\Shape;

use Fastknife\Utils\ImageUtils;

class JigsawShapeDrawer implements ShapeDrawerInterface
{
    private $sliderL = 42;
    private $sliderR = 9;

    public function getOriginalSize(): array
    {
        $L = $this->sliderL + $this->sliderR * 2 + 3;
        return [$L, $L];
    }

    public function createMask(int $scale)
    {
        $L = $this->getOriginalSize()[0];
        $bigL = $L * $scale;

        // 1. 创建放大画布
        $big = ImageUtils::create($bigL, $bigL);
        imageantialias($big, true);

        // 2. 绘制参数
        $sliderL = $this->sliderL;
        $sliderR = $this->sliderR;

        // 浅灰半透明内容占位
        $contentColor = imagecolorallocatealpha($big, 220, 220, 220, 40);
        // 白色半透明阴影
        $shadowColor = imagecolorallocatealpha($big, 255, 255, 255, 50);

        // 3. 计算路径点
        $px = 3 * $scale;
        $py = ($sliderR * 2 + 1) * $scale;
        $PI = M_PI;
        $points = [];

        // 起点
        $points[] = $px; $points[] = $py;

        // 上方凸弧
        $cx = $px + ($sliderL / 2) * $scale;
        $cy = $py - $sliderR * $scale + 2 * $scale;
        for ($a = 0.72*$PI; $a <= 2.26*$PI; $a += 0.03 / $scale) {
            $points[] = $cx + $sliderR * $scale * cos($a);
            $points[] = $cy + $sliderR * $scale * sin($a);
        }

        // 右上角
        $points[] = $px + $sliderL * $scale;
        $points[] = $py;

        // 右侧凸弧
        $cx = $px + $sliderL * $scale + $sliderR * $scale - 2 * $scale;
        $cy = $py + ($sliderL / 2) * $scale;
        for ($a = 1.21*$PI; $a <= 2.78*$PI; $a += 0.03 / $scale) {
            $points[] = $cx + $sliderR * $scale * cos($a);
            $points[] = $cy + $sliderR * $scale * sin($a);
        }

        // 右下 -> 左下
        $points[] = $px + $sliderL * $scale; $points[] = $py + $sliderL * $scale;
        $points[] = $px;                    $points[] = $py + $sliderL * $scale;

        // 下方凹弧 (逆向)
        $cx = $px + $sliderR * $scale - 2 * $scale;
        $cy = $py + ($sliderL / 2) * $scale;
        for ($a = 2.76*$PI; $a >= 1.24*$PI; $a -= 0.03 / $scale) {
            $points[] = $cx + ($sliderR + 0.4) * $scale * cos($a);
            $points[] = $cy + ($sliderR + 0.4) * $scale * sin($a);
        }

        // 闭合
        $points[] = $px; $points[] = $py;

        // 4. 绘制多边形
        ImageUtils::filledPolygon($big, $points, $contentColor);
        ImageUtils::polygon($big, $points, $shadowColor);

        return $big;
    }
}
