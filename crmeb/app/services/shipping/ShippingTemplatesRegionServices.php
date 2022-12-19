<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\shipping;


use app\dao\shipping\ShippingTemplatesRegionDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

/**
 * 指定邮费
 * Class ShippingTemplatesRegionServices
 * @package app\services\shipping
 * @method  delete($id, ?string $key = null) 删除数据
 * @method getTempRegionList(array $tempIds, array $cityId) 根据运费模板id和城市id获得包邮数据列表
 */
class ShippingTemplatesRegionServices extends BaseServices
{
    /**
     * 构造方法
     * ShippingTemplatesRegionServices constructor.
     * @param ShippingTemplatesRegionDao $dao
     */
    public function __construct(ShippingTemplatesRegionDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 添加运费信息
     * @param array $regionInfo
     * @param int $type
     * @param int $tempId
     * @return bool
     * @throws \Exception
     */
    public function saveRegion(array $regionInfo, int $type = 0, $tempId = 0)
    {
        $res = true;
        if ($tempId) {
            if ($this->dao->count(['temp_id' => $tempId])) {
                $res = $this->dao->delete($tempId, 'temp_id');
            }
        }
        $regionList = [];
        foreach ($regionInfo as $item) {
            if (isset($item['region']) && is_array($item['region'])) {
                $uniqid = uniqid('adminapi') . rand(1000, 9999);
                foreach ($item['region'] as $value) {
                    if (isset($value['children']) && is_array($value['children'])) {
                        foreach ($value['children'] as $vv) {
                            if (!isset($vv['city_id'])) {
                                throw new AdminException(400591);
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
                        $regionList[0] = [
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
        return $res && $this->dao->saveAll($regionList);
    }

    /**
     * 获取某个运费模板下的城市数据
     * @param int $tempId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRegionList(int $tempId)
    {
        $regionList = $this->dao->getShippingGroupArray(['temp_id' => $tempId], 'uniqid', 'uniqid', '');
        $regionData = [];
        $infos = $this->dao->getShippingArray(['uniqid' => $regionList, 'temp_id' => $tempId], '*', 'uniqid');
        foreach ($regionList as $uniqid) {
            $info = $infos[$uniqid];
            if ($info['province_id'] == 0) {
                $regionData[] = [
                    'region' => [
                        'city_id' => 0,
                        'name' => '默认全国',
                    ],
                    'regionName' => '默认全国',
                    'first' => $info['first'] ? floatval($info['first']) : 0,
                    'price' => $info['first_price'] ? floatval($info['first_price']) : 0,
                    'continue' => $info['continue'] ? floatval($info['continue']) : 0,
                    'continue_price' => $info['continue_price'] ? floatval($info['continue_price']) : 0,
                    'uniqid' => $info['uniqid'],
                ];
            } else {
                $regionData[] = [
                    'region' => $this->getRegionTemp($uniqid, $info['province_id']),
                    'regionName' => '',
                    'first' => $info['first'] ? floatval($info['first']) : 0,
                    'price' => $info['first_price'] ? floatval($info['first_price']) : 0,
                    'continue' => $info['continue'] ? floatval($info['continue']) : 0,
                    'continue_price' => $info['continue_price'] ? floatval($info['continue_price']) : 0,
                    'uniqid' => $info['uniqid'],
                ];
            }
        }

        foreach ($regionData as &$item) {
            if (!$item['regionName']) {
                $item['regionName'] = implode(';', array_map(function ($val) {
                    return $val['name'];
                }, $item['region']));
            }
        }

        return $regionData;
    }

    /**
     * 获取省份下运费模板
     * @param string $uniqid
     * @param int $provinceId
     * @return array
     */
    public function getRegionTemp(string $uniqid, int $provinceId)
    {
        /** @var ShippingTemplatesRegionCityServices $services */
        $services = app()->make(ShippingTemplatesRegionCityServices::class);
        $infoList = $services->getUniqidList(['uniqid' => $uniqid]);
        $childrenData = [];
        foreach ($infoList as $item) {
            $childrenData[] = [
                'city_id' => $item['province_id'],
                'name' => $item['name'] ?? '全国',
                'children' => $this->getCityTemp($uniqid, $item['province_id'])
            ];
        }
        return $childrenData;
    }

    /**
     * 获取市区下的数据
     * @param string $uniqid
     * @param int $provinceId
     * @return array
     */
    public function getCityTemp(string $uniqid, int $provinceId)
    {
        /** @var ShippingTemplatesRegionCityServices $services */
        $services = app()->make(ShippingTemplatesRegionCityServices::class);
        $infoList = $services->getUniqidList(['uniqid' => $uniqid, 'province_id' => $provinceId], false);
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
