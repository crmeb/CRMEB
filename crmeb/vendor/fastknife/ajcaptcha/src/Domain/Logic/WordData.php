<?php
declare(strict_types=1);

namespace Fastknife\Domain\Logic;

use Fastknife\Domain\Vo\PointVo;
use Fastknife\Exception\WordException;
use Fastknife\Utils\RandomUtils;

/**
 * 文字码数据处理
 * Class WordDataEntity
 * @package Fastknife\Domain\Entity
 */
class WordData extends BaseData
{
    protected $defaultBackgroundPath = '/resources/defaultImages/pic-click/';


    /**
     * @param $width
     * @param $height
     * @param $index
     * @param $wordCount
     * @return PointVo
     */
    protected function getPoint($width, $height, $index, $wordCount)
    {
        $avgWidth = $width / ($wordCount + 1);
        if ($avgWidth < self::FONTSIZE) {
            $x = RandomUtils::getRandomInt(1 + self::FONTSIZE, $width);
        } else {
            if ($index == 0) {
                $x = RandomUtils::getRandomInt(1 + self::FONTSIZE, $avgWidth * ($index + 1) - self::FONTSIZE);
            } else {
                $x = RandomUtils::getRandomInt($avgWidth * $index + self::FONTSIZE, $avgWidth * ($index + 1) - self::FONTSIZE);
            }
        }
        $y = RandomUtils::getRandomInt(self::FONTSIZE, $height - self::FONTSIZE);
        return new PointVo($x, $y);
    }

    /**
     * @param $width
     * @param $height
     * @param int $number
     * @return array
     */
    public function getPointList($width, $height, $number = 3): array
    {
        $pointList = [];
        for ($i = 0; $i < $number; $i++) {
            $pointList[] = $this->getPoint($width, $height, $i, $number);
        }
        shuffle($pointList); //随机排序
        return $pointList;
    }


    /**
     * @param $list
     * @return array
     */
    public function array2Point($list): array
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = new PointVo($item['x'], $item['y']);
        }
        return $result;
    }

    public function getWordList($number): array
    {
        return RandomUtils::getRandomChar($number);
    }

    /**
     * 校验
     * @param array $originPointList
     * @param array $targetPointList
     */
    public function check(array $originPointList, array $targetPointList)
    {
        foreach ($originPointList as $key => $originPoint) {
            $targetPoint = $targetPointList[$key];
            if ($targetPoint->x - self::FONTSIZE > $originPoint->x
                || $targetPoint->x > $originPoint->x + self::FONTSIZE
                || $targetPoint->y - self::FONTSIZE > $originPoint->y
                || $targetPoint->y > $originPoint->y + self::FONTSIZE) {
                throw new WordException('验证失败!');
            }
        }
    }
}
