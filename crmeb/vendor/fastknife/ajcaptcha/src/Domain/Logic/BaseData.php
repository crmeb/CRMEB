<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;


use Fastknife\Domain\Vo\BackgroundVo;
use Fastknife\Utils\RandomUtils;
use Intervention\Image\ImageManagerStatic;

class BaseData
{
    public const FONTSIZE = 25;

    protected $defaultBackgroundPath;

    protected $defaultFontPath = '/resources/fonts/WenQuanZhengHei.ttf';

    /**
     * 获取字体包文件
     * @param string $file
     * @return string
     */
    public function getFontFile(string $file = ''): string
    {
        return $file && is_file($file) ?
            $file :
            dirname(__DIR__, 3) . $this->defaultFontPath;
    }

    /**
     * 获得随机图片
     * @param $images
     * @return string
     */
    protected function getRandImage($images): string
    {
        $index = RandomUtils::getRandomInt(0, count($images) - 1);
        return $images[$index];
    }

    /**
     * 获取默认图片
     * @param $dir
     * @param $images
     * @return array|false
     */
    protected function getDefaultImage($dir, $images)
    {
        if (!empty($images)) {
            if (is_array($images)) {
                return $images;
            }
            if (is_string($images)) {
                $dir = $images;
            }
        }
        return glob($dir . '*.png');
    }

    /**
     * 获取一张背景图地址
     * @param null $backgrounds 背景图库
     * @return BackgroundVo
     */
    public function getBackgroundVo($backgrounds = null): BackgroundVo
    {
        $dir = dirname(__DIR__, 3). $this->defaultBackgroundPath;
        $backgrounds = $this->getDefaultImage($dir, $backgrounds);
        $src = $this->getRandImage($backgrounds);
        return new BackgroundVo($src);
    }
}
