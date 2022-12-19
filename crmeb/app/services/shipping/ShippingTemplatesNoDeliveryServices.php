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


use app\dao\shipping\ShippingTemplatesNoDeliveryDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

/**
 * 不送达
 * Class ShippingTemplatesNoDeliveryServices
 * @package app\services\shipping
 * @method isNoDelivery($tempId, $cityid) 是否不送达
 */
class ShippingTemplatesNoDeliveryServices extends BaseServices
{
    /**
     * 构造方法
     * ShippingTemplatesNoDeliveryServices constructor.
     * @param ShippingTemplatesNoDeliveryDao $dao
     */
    public function __construct(ShippingTemplatesNoDeliveryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 添加不送达信息
     * @param array $noDeliveryInfo
     * @param int $tempId
     * @return bool|mixed
     */
    public function saveNoDelivery(array $noDeliveryInfo, int $tempId = 0)
    {
        $res = true;
        if ($tempId) {
            if ($this->dao->count(['temp_id' => $tempId])) {
                $res = $this->dao->delete($tempId, 'temp_id');
            }
        }
        $placeList = [];
        mt_srand();
        foreach ($noDeliveryInfo as $item) {
            if (isset($item['place']) && is_array($item['place'])) {
                $uniqid = uniqid('adminapi') . rand(1000, 9999);
                foreach ($item['place'] as $value) {
                    if (isset($value['children']) && is_array($value['children'])) {
                        foreach ($value['children'] as $vv) {
                            if (!isset($vv['city_id'])) {
                                throw new AdminException(400591);
                            }
                            $placeList [] = [
                                'temp_id' => $tempId,
                                'province_id' => $value['city_id'] ?? 0,
                                'city_id' => $vv['city_id'] ?? 0,
                                'uniqid' => $uniqid,
                            ];
                        }
                    }
                }
            }
        }
        if (count($placeList)) {
            return $res && $this->dao->saveAll($placeList);
        } else {
            return $res;
        }
    }

    /**
     * 获得指定包邮城市地址
     * @param int $tempId
     * @return array
     */
    public function getNoDeliveryList(int $tempId)
    {
        $freeIdList = $this->dao->getShippingGroupArray(['temp_id' => $tempId], 'uniqid', 'uniqid', '');
        $freeData = [];
        $infos = $this->dao->getShippingArray(['uniqid' => $freeIdList, 'temp_id' => $tempId], '*', 'uniqid');
        foreach ($freeIdList as $uniqid) {
            $info = $infos[$uniqid];
            $freeData[] = [
                'place' => $this->getNoDeliveryTemp($uniqid, $info['province_id']),
            ];
        }
        foreach ($freeData as &$item) {
            $item['placeName'] = implode(';', array_column($item['place'], 'name'));
        }
        return $freeData;
    }

    /**
     * 获取不送达的省份
     * @param string $uniqid
     * @param int $provinceId
     * @return array
     */
    public function getNoDeliveryTemp(string $uniqid, int $provinceId)
    {
        /** @var ShippingTemplatesNoDeliveryCityServices $service */
        $service = app()->make(ShippingTemplatesNoDeliveryCityServices::class);
        $infoList = $service->getUniqidList(['uniqid' => $uniqid]);
        $childrenData = [];
        foreach ($infoList as $item) {
            $childrenData[] = [
                'city_id' => $item['province_id'],
                'name' => $item['name'] ?? '全国',
                'children' => $this->getCityTemp($uniqid, $provinceId)
            ];
        }
        return $childrenData;
    }

    /**
     * 获取市区数据
     * @param string $uniqid
     * @param int $provinceId
     * @return array
     */
    public function getCityTemp(string $uniqid, int $provinceId)
    {
        /** @var ShippingTemplatesNoDeliveryCityServices $service */
        $service = app()->make(ShippingTemplatesNoDeliveryCityServices::class);
        $infoList = $service->getUniqidList(['uniqid' => $uniqid, 'province_id' => $provinceId], false);
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
