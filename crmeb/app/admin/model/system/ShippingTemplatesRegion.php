<?php

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\models\system\SystemCity;

/**
 * 菜单  model
 * Class SystemMenus
 * @package app\admin\model\system
 */
class ShippingTemplatesRegion extends BaseModel
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
    protected $name = 'shipping_templates_region';

    use ModelTrait;

    /**
     * 添加运费信息
     * @param array $regionInfo
     * @param int $type
     * @param int $tempId
     * @return bool
     * @throws \Exception
     */
    public static function saveRegion(array $regionInfo, int $type = 0, $tempId = 0)
    {
        $res = true;
        if ($tempId) {
            if (self::where('temp_id', $tempId)->count()) {
                $res = self::where('temp_id', $tempId)->delete();
            }
        }
        $regionList = [];
        foreach ($regionInfo as $item) {
            if (isset($item['region']) && is_array($item['region'])) {
                $uniqid = uniqid(true);
                foreach ($item['region'] as $value) {
                    if (isset($value['children']) && is_array($value['children'])) {
                        foreach ($value['children'] as $vv) {
                            if (!isset($vv['city_id'])) {
                                return self::setErrorInfo('缺少城市id无法保存');
                            }
                            $regionList[] = [
                                'temp_id' => $tempId,
                                'province_id' => $value['city_id'] ?? 0,
                                'city_id' => $vv['city_id'] ?? 0,
                                'first' => $item['first'] ?? 0,
                                'first_price' => $item['price'] ?? 0,
                                'continue' => $item['continue'] ?? 0,
                                'continue_price' => $item['continue_price'] ?? 0,
                                'type' => $type,
                                'uniqid' => $uniqid,
                            ];
                        }
                    } else {
                        $regionList[] = [
                            'temp_id' => $tempId,
                            'province_id' => 0,
                            'city_id' => 0,
                            'first' => $item['first'] ?? 0,
                            'first_price' => $item['price'] ?? 0,
                            'continue' => $item['continue'] ?? 0,
                            'continue_price' => $item['continue_price'] ?? 0,
                            'type' => $type,
                            'uniqid' => $uniqid,
                        ];
                    }
                }
            }
        }
        return $res && self::insertAll($regionList);
    }

    public static function getRegionList(int $tempId)
    {
        $regionList = self::where('temp_id', $tempId)->group('uniqid')->column('uniqid');
        $regionData = [];
        foreach ($regionList as $uniqid) {
            $info = self::where(['uniqid' => $uniqid, 'temp_id' => $tempId])->find();
            if ($info['province_id'] == 0) {
                $regionData[] = [
                    'region' => [
                        'city_id' => 0,
                        'name' => '默认全国',
                    ],
                    'regionName' => '默认全国',
                    'first' => $info['first'],
                    'price' => $info['first_price'],
                    'continue' => $info['continue'],
                    'continue_price' => $info['continue_price'],
                    'uniqid' => $info['uniqid'],
                ];
            } else {
                $regionData[] = [
                    'region' => self::getRegionTemp($uniqid, $info['province_id']),
                    'regionName' => '',
                    'first' => $info['first'],
                    'price' => $info['first_price'],
                    'continue' => $info['continue'],
                    'continue_price' => $info['continue_price'],
                    'uniqid' => $info['uniqid'],
                ];
            }
        }

        foreach ($regionData as &$item) {
            if (!$item['regionName']) {
                $item['regionName'] = array_map(function ($val) {
                    return $val['name'];
                }, $item['region']);
            }
        }

        return $regionData;
    }

    public static function getRegionTemp(string $uniqid, int $provinceId)
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