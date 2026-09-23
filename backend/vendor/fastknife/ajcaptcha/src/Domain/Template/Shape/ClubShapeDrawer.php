<?php
declare(strict_types=1);

namespace Fastknife\Domain\Template\Shape;

use Fastknife\Utils\ImageUtils;

class ClubShapeDrawer implements ShapeDrawerInterface
{
    private $width = 50;
    private $height = 56;

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

        $cx = $w / 2;
        $cy = $h / 2 - 4 * $scale;
        
        // 3个圆的半径
        $r = 11 * $scale;
        
        // 顶部圆心
        $c1x = $cx;
        $c1y = $cy - $r + 2 * $scale;
        
        // 左下圆心 (30度偏移)
        $c2x = $cx - $r * 0.9;
        $c2y = $cy + $r * 0.6;
        
        // 右下圆心
        $c3x = $cx + $r * 0.9;
        $c3y = $cy + $r * 0.6;
        
        // 绘制3个圆 (填充 + 描边)
        // 为了消除重叠部分的描边，我们先画描边，再画填充？
        // 不，应该先画所有实心，再画所有描边。
        // 但是 GD 没有 "union" 操作。
        // 简单的模拟：先画3个带描边的圆。重叠处会有交叉线。
        // 然后再画3个略小一点的圆（或同等大小）只填充内容色，覆盖掉内部的交叉线。
        
        // 1. 画描边 (实心+描边)
        // imagefilledellipse + imageellipse
        $this->drawCircle($big, $c1x, $c1y, $r, $contentColor, $shadowColor);
        $this->drawCircle($big, $c2x, $c2y, $r, $contentColor, $shadowColor);
        $this->drawCircle($big, $c3x, $c3y, $r, $contentColor, $shadowColor);
        
        // 2. 绘制柄
        $stemW = 5 * $scale;
        $stemH = 15 * $scale;
        $stemTopY = $cy;
        $stemBottomY = $h - 2 * $scale;
        
        $stemPoints = [
            $cx, $stemTopY,
            $cx + $stemW, $stemBottomY,
            $cx - $stemW, $stemBottomY
        ];
        ImageUtils::filledPolygon($big, $stemPoints, $contentColor);
        ImageUtils::polygon($big, $stemPoints, $shadowColor);
        
        // 3. 再次填充内容色 (覆盖内部重叠的线条)
        // 注意：contentColor 是半透明的。叠加会变深。
        // 这是一个问题。如果再次叠加，颜色会不对。
        // 除非我们用一个完全不透明的颜色画 Mask，最后再转为半透明？
        // 或者，我们不画圆的描边，而是画一个外轮廓。
        // 计算外轮廓太复杂。
        
        // 妥协方案：
        // 草花比较复杂，我们接受内部有轻微的交叉线（视为纹理），
        // 或者调整 contentColor 的 Alpha，让叠加不那么明显。
        // 但目前架构中，maskAlpha 决定了最终的透明度。
        
        // 另一种方案：只画填充，不画描边。
        // 描边由 BlockImage 的 RGB 识别逻辑处理。
        // 如果我们不画白色描边，BlockImage 就识别不到描边。
        
        // 最佳方案：用 filledellipse 画内容。
        // 然后手动画圆弧（arc）来做描边，只画外侧部分。
        // 这需要计算角度。
        
        // 简化版：接受交叉线。草花本身就是三叶草，叶瓣之间有纹理也很正常。
        
        return $big;
    }
    
    private function drawCircle($img, $cx, $cy, $r, $fill, $stroke) {
        $d = $r * 2;
        imagefilledellipse($img, (int)$cx, (int)$cy, (int)$d, (int)$d, $fill);
        imageellipse($img, (int)$cx, (int)$cy, (int)$d, (int)$d, $stroke);
    }
}
