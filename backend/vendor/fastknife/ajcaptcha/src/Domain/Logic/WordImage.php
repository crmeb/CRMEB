<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;

use Fastknife\Domain\Vo\PointVo;
use Fastknife\Utils\ImageUtils;
use Fastknife\Utils\RandomUtils;

/**
 * 文字码图片处理
 * Class WordCaptchaEntity
 * @package Fastknife\Domain\Entity
 */
class WordImage extends BaseImage
{

    /**
     * @var array
     */
    protected $wordList;
    
    /**
     * @var array 用于图片绘制的实际字符列表（含 Unicode 图标）
     */
    protected $drawWordList = [];
    
    /**
     * @var array 图标索引位置列表
     */
    protected $iconIndexes = [];
    
    /**
     * @var float 图标字体放大比例
     */
    protected $iconFontSizeScale = 1.3;
    
    /**
     * @var int 目标字数 (Service 层需要缓存前 N 个)
     */
    protected $targetCount = 0;


    /**
     * @return self
     */
    public function setWordList(array $wordList)
    {
        $this->wordList = $wordList;
        return $this;
    }

    public function getWordList()
    {
        return $this->wordList;
    }
    
    /**
     * 设置绘制列表
     * @param array $drawWordList
     * @return self
     */
    public function setDrawWordList(array $drawWordList)
    {
        $this->drawWordList = $drawWordList;
        return $this;
    }
    
    /**
     * 获取绘制列表
     * @return array
     */
    public function getDrawWordList()
    {
        return $this->drawWordList;
    }
    
    /**
     * 设置图标索引列表
     * @param array $iconIndexes
     * @return self
     */
    public function setIconIndexes(array $iconIndexes)
    {
        $this->iconIndexes = $iconIndexes;
        return $this;
    }
    
    /**
     * 获取图标索引列表
     * @return array
     */
    public function getIconIndexes()
    {
        return $this->iconIndexes;
    }
    
    /**
     * 设置图标字体放大比例
     * @param float $scale
     * @return self
     */
    public function setIconFontSizeScale(float $scale)
    {
        $this->iconFontSizeScale = $scale;
        return $this;
    }
    
    /**
     * 获取图标字体放大比例
     * @return float
     */
    public function getIconFontSizeScale()
    {
        return $this->iconFontSizeScale;
    }
    
    public function setTargetCount(int $count)
    {
        $this->targetCount = $count;
        return $this;
    }
    
    /**
     * 获取仅包含目标字的列表 (供 Service 使用)
     */
    public function getTargetWordList()
    {
        if ($this->targetCount > 0) {
            return array_slice($this->wordList, 0, $this->targetCount);
        }
        return $this->wordList;
    }
    
    /**
     * 获取仅包含目标字的坐标 (供 Service 使用)
     */
    public function getTargetPointList()
    {
        if ($this->targetCount > 0) {
            return array_slice($this->point, 0, $this->targetCount);
        }
        return $this->point;
    }



    public function run()
    {
        $this->inputWords();
        $this->makeWatermark($this->backgroundVo->image);
    }

    /**
     * 写入文字
     */
    protected function inputWords(){
        // 优先使用专门的绘制列表，未设置时兼容旧逻辑
        $words = !empty($this->drawWordList) ? $this->drawWordList : $this->wordList;
        
        foreach ($words as $key => $word) {
            $point = $this->point[$key];
            
            // 判断当前字符是否为图标，应用不同的字体大小
            $isIcon = in_array($key, $this->iconIndexes);
            $fontSize = $isIcon ? (int)(BaseData::FONTSIZE * $this->iconFontSizeScale) : BaseData::FONTSIZE;
            
            // 使用 ImageUtils 替换 Intervention
            ImageUtils::text(
                $this->backgroundVo->image,
                $word,
                $point->x,
                $point->y,
                $this->fontFile,
                $fontSize,
                RandomUtils::getRandomColor(), // [r, g, b, a]
                (float)RandomUtils::getRandomAngle(), // angle
                'left', // align (ImageUtils::text 修正逻辑基于左下角，这里我们假设 point 是左下角或者中心，原逻辑是 center/center)
                // 原逻辑 align('center') valign('center')。
                // ImageUtils::text 实现了 center/center 修正。
                'center'
            );
        }
    }

    /**
     * 返回前端需要的格式
     * @return string Base64
     */
    public function response()
    {
        return ImageUtils::outputBase64($this->getBackgroundVo()->image, 'png');
    }

    /**
     * 用来调试
     */
    public function echo()
    {
        header('Content-Type: image/png');
        imagepng($this->getBackgroundVo()->image);
        die;
    }

}
