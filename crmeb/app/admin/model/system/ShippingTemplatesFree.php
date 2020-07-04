<?php

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use lib\help\Arr;

/**
 * 菜单  model
 * Class SystemMenus
 * @package app\admin\model\system
 */
class ShippingTemplatesFree extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'shipping_templates_free';

    use ModelTrait;

    /**
     * 添加包邮信息
     * @param array $appointInfo
     * @param int $type
     * @param int $tempId
     * @return bool
     * @throws \Exception
     */
    public static function saveFree(array $appointInfo, int $type = 0, int $tempId = 0)
    {
        $res = true;
        if ($tempId) {
            if (self::where('temp_id', $tempId)->count()) {
                $res = self::where('temp_id', $tempId)->delete();
            }
        }
        $placeList = [];
        foreach ($appointInfo as $item) {
            if (isset($item['place']) && is_array($item['place'])) {
                $uniqid = uniqid(true);
                foreach ($item['place'] as $value) {
                    if (isset($value['children']) && is_array($value['children'])) {
                        foreach ($value['children'] as $vv) {
                            if (!isset($vv['city_id'])) {
                                return self::setErrorInfo('缺少城市id无法保存');
                            }
                            $placeList [] = [
                                'temp_id' => $tempId,
                                'province_id' => $value['city_id'] ?? 0,
                                'city_id' => $vv['city_id'] ?? 0,
                                'number' => $item['a_num'] ?? 0,
                                'price' => $item['a_price'] ?? 0,
                                'type' => $type,
                                'uniqid' => $uniqid,
                            ];
                        }
                    }
                }
            }
        }
        if (count($placeList)) {
            return $res && self::insertAll($placeList);
        } else {
            return $res;
        }
    }

    public static function getFreeList(int $tempId)
    {
        $freeIdList = self::where('temp_id', $tempId)->group('uniqid')->column('uniqid');
        $freeData = [];
        foreach ($freeIdList as $uniqid) {
            $info = self::where(['uniqid' => $uniqid, 'temp_id' => $tempId])->find();
            $freeData[] = [
                'place' => self::getFreeTemp($uniqid, $info['province_id']),
                'a_num' => $info['number'],
                'a_price' => $info['price'],
            ];
        }
        foreach ($freeData as &$item) {
            $item['placeName'] = array_map(function ($val) {
                return $val['name'];
            }, $item['place']);
        }
        return $freeData;
    }

    public static function getFreeTemp(string $uniqid, int $provinceId)
    {
        $infoList = self::where(['a.uniqid' => $uniqid])->group('a.province_id')->field('a.province_id,c.name')->alias('a')->join('system_city c', 'a.province_id = c.city_id', 'left')->select();
        $infoList = count($infoList) ? $infoList->toArray() : [];
        $childrenData = [];
        foreach ($infoList as $item) {
            $childrenData[] = [
                'city_id' => $item['province_id'],
                'name' => $item['name'] ?? '全国',
                'children' => self::getCityTemp($uniqid, $provinceId)
            ];
        }
        return $childrenData;
    }

    public static function getCityTemp(string $uniqid, int $provinceId)
    {
        $infoList = self::where(['a.uniqid' => $uniqid, 'province_id' => $provinceId])->field('a.city_id,c.name')->alias('a')->join('system_city c', 'a.city_id = c.city_id', 'left')->select();
        $childrenData = [];
        foreach ($infoList as $item) {
            $childrenData[] = [
                'city_id' => $item['city_id'],
                'name' => $item['name'] ?? '全国',
            ];
        }
        return $childrenData;
    }
}