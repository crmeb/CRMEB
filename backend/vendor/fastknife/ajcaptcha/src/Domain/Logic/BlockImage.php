<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;


use Fastknife\Domain\Vo\BackgroundVo;
use Fastknife\Domain\Vo\ImageVo;
use Fastknife\Domain\Vo\PointVo;
use Fastknife\Domain\Vo\TemplateVo;
use Fastknife\Utils\ImageUtils;

class BlockImage extends BaseImage
{
    const WHITE = [255, 255, 255, 1]; // 1 means opaque in Intervention, but in ImageVo we convert to GD.

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
        // 核心改造：使用 Alpha Blending 替代硬性 cut
        $this->cutByTemplate($this->templateVo, $this->backgroundVo, function ($param) use (&$flag) {
            if (!$flag) {
                //记录第一个点
                $this->setPoint(new PointVo($param[0], 5));//前端已将y值写死
                $flag = true;
            }
        });
        
        if (!empty($this->interfereVo)) {
            // 干扰图也使用同样的逻辑
            $this->cutByTemplate($this->interfereVo, $this->backgroundVo);
        }
        $this->makeWatermark($this->backgroundVo->image);
    }


    public function cutByTemplate(TemplateVo $templateVo, BackgroundVo $backgroundVo, $callable = null)
    {
        $template = $templateVo->image;
        $width = imagesx($template);
        $height = imagesy($template);
        
        $offsetX = $templateVo->offset->x;
        $offsetY = $templateVo->offset->y;

        // 记录坐标 (仅针对主模板，即有回调的情况)
        if ($callable instanceof \Closure) {
            $callable([$offsetX, $offsetY]);
        }

        // 遍历模板像素
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                // 1. 获取模板当前像素的 Alpha 透明度
                $maskColor = $templateVo->getPickColor($x, $y);
                $maskAlpha = $maskColor[3]; // 0.0 (transparent) ~ 1.0 (opaque)

                if ($maskAlpha <= 0.01) {
                    continue; // 几乎完全透明，跳过
                }

                // 背景图对应的坐标
                $bgX = $x + $offsetX;
                $bgY = $y + $offsetY;
                
                // 2. 从背景图取色
                $bgColor = $backgroundVo->getPickColor($bgX, $bgY);

                // 3. 混合 (Alpha Blending) - 生成滑块
                // 最终滑块像素的 Alpha = MaskAlpha * BackgroundAlpha
                $finalAlpha = $maskAlpha * $bgColor[3];
                $templateVo->setPixel([$bgColor[0], $bgColor[1], $bgColor[2], $finalAlpha], $x, $y);

                // 4. 处理背景图的凹槽 (Digging the hole)
                // 视觉效果：将背景图对应区域变暗 (Darken)，模拟凹槽阴影
                // 仅当 maskAlpha 足够大时才处理 (避免边缘过度黑化)
                if ($maskAlpha > 0.2) {
                    // 变暗系数：0.8 (越小越黑，0.8意味着亮度只剩20%)
                    // 结合 MaskAlpha，边缘渐变变暗
                    $darkenFactor = 1.0 - (0.8 * $maskAlpha);
                    
                    $newR = $bgColor[0] * $darkenFactor;
                    $newG = $bgColor[1] * $darkenFactor;
                    $newB = $bgColor[2] * $darkenFactor;
                    
                    // 也可以叠加一层半透明黑，这里直接修改 RGB 简单有效
                    // Alpha 保持不变 (通常背景是不透明的)
                    $backgroundVo->setPixel([$newR, $newG, $newB, $bgColor[3]], $bgX, $bgY);
                    
                    // 可选：叠加轻微模糊 (vagueImage)，如果需要更柔和的效果
                    // $backgroundVo->vagueImage($bgX, $bgY); 
                }
            }
        }
    }

    /**
     * 把$source的颜色复制到$target上
     * @deprecated 新逻辑已内联到 cutByTemplate
     */
    protected function copyPickColor(ImageVo $source, $sourceX, $sourceY, ImageVo $target, $targetX, $targetY)
    {
        $bgRgba = $source->getPickColor($sourceX, $sourceY);
        $target->setPixel($bgRgba, $targetX, $targetY);
    }

    /**
     * 返回前端需要的格式
     * @return string Base64
     */
    public function response($type = 'background')
    {
        $image = $type == 'background' ? $this->backgroundVo->image : $this->templateVo->image;
        return ImageUtils::outputBase64($image, 'png');
    }

    /**
     * 用来调试
     */
    public function echo($type = 'background')
    {
        $image = $type == 'background' ? $this->backgroundVo->image : $this->templateVo->image;
        header('Content-Type: image/png');
        imagepng($image);
        die;
    }
}
