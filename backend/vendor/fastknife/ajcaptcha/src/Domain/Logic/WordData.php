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
     * 获取随机且不重叠的坐标列表
     * @param int $width 画布宽度
     * @param int $height 画布高度
     * @param int $number 文字数量
     * @return PointVo[]
     */
    public function getPointList($width, $height, $number = 3): array
    {
        $pointList = [];
        $maxRetries = 100; // 最大重试次数
        $fontSize = self::FONTSIZE;
        // 碰撞检测距离阈值 (增加一点 buffer)
        $collisionDistance = $fontSize * 1.2;

        for ($i = 0; $i < $number; $i++) {
            $retry = 0;
            $success = false;

            while ($retry < $maxRetries) {
                // 随机生成坐标 (考虑文字绘制基准点和边界)
                // 文字通常以左下角为基准，或者左上角，取决于 ImageUtils 实现。
                // ImageUtils::text 修正了对齐，如果 align=left, valign=bottom (默认)
                // x: 0 ~ width - fontSize
                // y: fontSize ~ height
                
                $x = RandomUtils::getRandomInt($fontSize, $width - $fontSize);
                $y = RandomUtils::getRandomInt($fontSize, $height - $fontSize);
                
                $currentPoint = new PointVo($x, $y);
                
                // 碰撞检测
                if (!$this->isColliding($currentPoint, $pointList, $collisionDistance)) {
                    $pointList[] = $currentPoint;
                    $success = true;
                    break;
                }
                $retry++;
            }
            
            // 如果重试多次仍失败（可能是字太多图太小），强制添加一个（可能会重叠），或者抛异常。
            // 为了健壮性，我们允许轻微重叠或尝试放宽条件，这里简单处理：强制添加。
            if (!$success) {
                 // 兜底：尝试网格化或直接添加
                 // 简单添加，避免死循环
                 $pointList[] = new PointVo(
                     RandomUtils::getRandomInt($fontSize, $width - $fontSize),
                     RandomUtils::getRandomInt($fontSize, $height - $fontSize)
                 );
            }
        }
        
        return $pointList;
    }
    
    /**
     * 检测是否碰撞
     * @param PointVo $current
     * @param PointVo[] $existingList
     * @param float $minDist
     * @return bool
     */
    private function isColliding(PointVo $current, array $existingList, float $minDist): bool
    {
        foreach ($existingList as $point) {
            // 计算欧氏距离
            $dist = sqrt(pow($current->x - $point->x, 2) + pow($current->y - $point->y, 2));
            if ($dist < $minDist) {
                return true;
            }
        }
        return false;
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
            if (!isset($targetPointList[$key])) {
                throw new WordException('验证失败: 坐标数量不匹配');
            }
            $targetPoint = $targetPointList[$key];
            // 校验逻辑：目标点是否在原始点的一定范围内
            // FONTSIZE 作为容错半径
            if ($targetPoint->x - self::FONTSIZE > $originPoint->x
                || $targetPoint->x > $originPoint->x + self::FONTSIZE
                || $targetPoint->y - self::FONTSIZE > $originPoint->y
                || $targetPoint->y > $originPoint->y + self::FONTSIZE) {
                throw new WordException('验证失败!');
            }
        }
    }
}
