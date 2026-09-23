<?php
declare(strict_types=1);

namespace Fastknife\Utils;

use RuntimeException;

class ImageUtils
{
    /**
     * 读取图片
     * @param string $path
     * @return resource|\GdImage
     */
    public static function read($path)
    {
        // 如果已经是资源或对象，直接返回
        if (is_resource($path) || $path instanceof \GdImage) {
            return $path;
        }

        if (!is_string($path)) {
            throw new RuntimeException("Invalid image source: expected string path or GdImage/resource");
        }

        if (!file_exists($path)) {
            throw new RuntimeException("File not found: {$path}");
        }

        $info = getimagesize($path);
        if ($info === false) {
            throw new RuntimeException("Invalid image file: {$path}");
        }

        $type = $info[2];
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($path);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($path);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($path);
                break;
            default:
                throw new RuntimeException("Unsupported image type: {$type}");
        }

        if ($image === false) {
            throw new RuntimeException("Failed to create image from file: {$path}");
        }
        
        // 保持透明度
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * 创建画布
     * @param int $width
     * @param int $height
     * @return resource|\GdImage
     */
    public static function create(int $width, int $height)
    {
        $image = imagecreatetruecolor($width, $height);
        // 开启 Alpha 通道
        imagesavealpha($image, true);
        // 填充全透明背景
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        return $image;
    }

    /**
     * 写入文字
     * @param resource|\GdImage $image
     * @param string $text
     * @param int $x
     * @param int $y
     * @param string $fontFile
     * @param int $fontSize
     * @param array $color [r, g, b, a]
     * @param float $angle 角度
     * @param string $align 'left', 'center', 'right'
     * @param string $valign 'top', 'center', 'bottom'
     */
    public static function text($image, string $text, int $x, int $y, string $fontFile, int $fontSize, array $color, float $angle = 0, string $align = 'left', string $valign = 'bottom')
    {
        // 颜色分配
        $alpha = isset($color[3]) ? (int)($color[3] * 127) : 0; // GD alpha is 0-127
        // 如果输入的 alpha 是 0-1 的 float，转换一下，如果已经是 0-127 则不转。
        // Intervention v2 的 alpha 可能是 0-1，这里假设输入是 RGBA 数组，A 是 0-1
        if (isset($color[3]) && $color[3] <= 1) {
             $alpha = (int)((1 - $color[3]) * 127); // Intervention 1 is opaque, GD 0 is opaque. Wait.
             // Intervention v2: alpha 1 (opaque) -> 0 (transparent). No.
             // Intervention v2: 0 (transparent) -> 1 (opaque).
             // GD: 0 (opaque) -> 127 (transparent).
             // So: GD_Alpha = (1 - Input_Alpha) * 127
        } else {
             // 假设已经是 GD 格式或者不带 alpha
             $alpha = 0; 
        }

        $col = imagecolorallocatealpha($image, (int)$color[0], (int)$color[1], (int)$color[2], (int)$alpha);

        // 计算文字包围盒
        $bbox = imagettfbbox((float)$fontSize, (float)$angle, $fontFile, $text);
        // $bbox: 0,1 (LL), 2,3 (LR), 4,5 (UR), 6,7 (UL)
        $textWidth = abs($bbox[4] - $bbox[0]);
        $textHeight = abs($bbox[5] - $bbox[1]); // approximate

        // 水平对齐修正
        if ($align === 'center') {
            $x -= ($textWidth / 2);
        } elseif ($align === 'right') {
            $x -= $textWidth;
        }

        // 垂直对齐修正 (GD 基准线是文字底部)
        // imagettftext 的 x,y 是第一个字符的左下角
        if ($valign === 'top') {
            $y += $textHeight;
        } elseif ($valign === 'center') {
            $y += ($textHeight / 2);
        }
        // bottom 不需要修正

        imagettftext($image, (float)$fontSize, (float)$angle, (int)$x, (int)$y, $col, $fontFile, $text);
    }

    /**
     * 缩放/重采样
     * @param resource|\GdImage $srcImage
     * @param int $targetW
     * @param int $targetH
     * @return resource|\GdImage
     */
    public static function resize($srcImage, int $targetW, int $targetH)
    {
        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);
        
        $dstImage = self::create($targetW, $targetH);
        
        imagecopyresampled(
            $dstImage, $srcImage,
            0, 0, 0, 0,
            $targetW, $targetH,
            $srcW, $srcH
        );
        
        return $dstImage;
    }

    /**
     * 输出图片 Base64 (不带前缀)
     * @param resource|\GdImage $image
     * @param string $format 'png', 'jpeg'
     * @param int $quality
     * @return string
     */
    public static function outputBase64($image, string $format = 'png', int $quality = 90): string
    {
        ob_start();
        if ($format === 'jpeg' || $format === 'jpg') {
            imagejpeg($image, null, $quality);
        } else {
            imagepng($image);
        }
        $data = ob_get_clean();
        return base64_encode($data);
    }

    /**
     * 填充多边形（兼容 PHP 8.0 和 8.1+）
     * PHP 8.1+ 不再需要顶点数量参数
     * @param resource|\GdImage $image
     * @param array $points 顶点数组 [x1, y1, x2, y2, ...]
     * @param int $color 颜色
     */
    public static function filledPolygon($image, array $points, int $color)
    {
        if (PHP_VERSION_ID >= 80100) {
            // PHP 8.1+: 不需要顶点数量参数
            imagefilledpolygon($image, $points, $color);
        } else {
            // PHP 8.0: 需要顶点数量参数
            $numPoints = count($points) / 2;
            imagefilledpolygon($image, $points, (int)$numPoints, $color);
        }
    }

    /**
     * 绘制多边形边框（兼容 PHP 8.0 和 8.1+）
     * PHP 8.1+ 不再需要顶点数量参数
     * @param resource|\GdImage $image
     * @param array $points 顶点数组 [x1, y1, x2, y2, ...]
     * @param int $color 颜色
     */
    public static function polygon($image, array $points, int $color)
    {
        if (PHP_VERSION_ID >= 80100) {
            // PHP 8.1+: 不需要顶点数量参数
            imagepolygon($image, $points, $color);
        } else {
            // PHP 8.0: 需要顶点数量参数
            $numPoints = count($points) / 2;
            imagepolygon($image, $points, (int)$numPoints, $color);
        }
    }

    /**
     * 安全销毁图片资源
     * 兼容 PHP 8.0+ (GdImage) 和 PHP 8.5+ (imagedestroy deprecated)
     * @param mixed $image
     */
    public static function destroy($image)
    {
        // PHP < 8.0: resource, 必须调用 imagedestroy
        // PHP >= 8.0: GdImage object, 自动回收，imagedestroy 无效但无害 (直到 8.5)
        // PHP >= 8.5: imagedestroy deprecated
        
        if (PHP_VERSION_ID < 80000) {
            if (is_resource($image) && get_resource_type($image) === 'gd') {
                imagedestroy($image);
            }
        }
        // PHP 8.0+ 让 GC 自动处理
    }
}
