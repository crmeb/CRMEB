<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template\Shape;

use Fastknife\Utils\ImageUtils;

class SpadeShapeDrawer implements ShapeDrawerInterface
{
    private $width = 50;
    private $height = 56; // 黑桃带柄，稍微高一点

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

        $centerX = $w / 2;
        $centerY = $h / 2 - 5 * $scale; // 心形主体稍微上移
        
        // 放大系数 R
        $R = min($w, $h) / 38; 

        $points = [];
        
        // 1. 倒置心形主体
        // t 从 PI 到 -PI (或者 0 到 2PI)
        // 倒置：y 取反
        for ($t = 0; $t <= 2 * M_PI; $t += 0.02) {
            $x = 16 * pow(sin($t), 3);
            $y = 13 * cos($t) - 5 * cos(2*$t) - 2 * cos(3*$t) - cos(4*$t);
            
            // 倒置心形
            $y = -$y;
            
            $px = $centerX + $x * $R;
            $py = $centerY - $y * $R; 
            
            $points[] = $px;
            $points[] = $py;
        }

        // 2. 底部柄 (三角形/梯形)
        // 倒置心形的尖端在上方，凹陷在下方
        // 这里的方程生成的倒置心形，尖端(0, 0)其实是在坐标系原点。
        // 原方程 t=0 时 y=13-5-2-1=5 (最高点)。倒置后 y=-5 (最低点)。
        // 实际上我们需要根据图形调整。
        // 为了简化，我们直接绘制一个倒置心形，然后再画一个三角形柄。
        
        // 重新计算主体点集，这次我们要更精细控制，以便和柄融合。
        // 但为了简单，我们可以分别画两个形状。
        // 不过为了描边(stroke)连续，最好合并点集。这比较复杂。
        // 简单的做法：画两个 filledpolygon，然后画两个 polygon 描边。虽然重叠处会有双重描边，但在 Mask 缩放后不太明显。
        
        // 绘制主体
        ImageUtils::filledPolygon($big, $points, $contentColor);
        ImageUtils::polygon($big, $points, $shadowColor);
        
        // 绘制柄 (梯形)
        $stemW = 6 * $scale;
        $stemH = 10 * $scale;
        $stemTopY = $centerY + 10 * $scale; // 这里的数值需要微调以接合心形底部
        $stemBottomY = $h - 2 * $scale;
        
        // 计算心形底部凹陷点的大致位置
        // 倒置心形底部是两个圆弧的交界？不，原方程心形尖端在下。倒置后尖端在上。
        // 那么底部就是两个圆弧。
        // 实际上黑桃是：尖端在上，底部两个圆弧，中间伸出一个柄。
        // 等等，扑克牌黑桃 ♠ 是尖端朝上的。
        // 原心形方程：尖端朝下。
        // 所以倒置后，尖端朝上。正确。
        // 底部是两个圆弧的凹陷处。
        
        // 绘制柄
        $stemPoints = [
            $centerX, $stemTopY - 5 * $scale, // 插入心形内部一点
            $centerX + $stemW, $stemBottomY,
            $centerX - $stemW, $stemBottomY
        ];
        
        ImageUtils::filledPolygon($big, $stemPoints, $contentColor);
        ImageUtils::polygon($big, $stemPoints, $shadowColor);
        
        // 修复：为了遮盖重叠处的描边，再次填充一次主体和柄的内部（无描边）
        ImageUtils::filledPolygon($big, $points, $contentColor);
        ImageUtils::filledPolygon($big, $stemPoints, $contentColor);

        return $big;
    }
}
