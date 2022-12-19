<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;

use Fastknife\Domain\Vo\PointVo;
use Intervention\Image\ImageManagerStatic as ImageManager;
use Intervention\Image\AbstractFont as Font;
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



    public function run()
    {
        $this->inputWords();
        $this->makeWatermark($this->backgroundVo->image);
    }

    /**
     * 写入文字
     */
    protected function inputWords(){
        foreach ($this->wordList as $key => $word) {
            $point = $this->point[$key];
            $this->backgroundVo->image->text($word, $point->x, $point->y, function (Font $font) {
                $font->file($this->fontFile);
                $font->size(BaseData::FONTSIZE);
                $font->color(RandomUtils::getRandomColor());
                $font->angle(RandomUtils::getRandomAngle());
                $font->align('center');
                $font->valign('center');
            });
        }
    }

    /**
     * 返回前端需要的格式
     * @return false|string[]
     */
    public function response()
    {
        $result = $this->getBackgroundVo()->image->encode('data-url')->getEncoded();
        //返回图片base64的第二部分
        return explode(',', $result)[1];
    }

    /**
     * 用来调试
     */
    public function echo()
    {
        die($this->getBackgroundVo()->image->response());
    }

}
