<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\shipping;


use app\dao\shipping\ShippingTemplatesFreeDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

/**
 * 包邮
 * Class ShippingTemplatesFreeServices
 * @package app\services\shipping
 * @method  delete($id, ?string $key = null) 删除数据
 * @method isFree($tempId, $cityid, $number, $price) 是否可以满足包邮
 */
class ShippingTemplatesFreeServices extends BaseServices
{
    /**
     * 构造方法
     * ShippingTemplatesFreeServices constructor.
     * @param ShippingTemplatesDao $dao
     */
    public function __construct(ShippingTemplatesFreeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 添加包邮信息
     * @param array $appointInfo
     * @param int $type
     * @param int $tempId
     * @return bool
     * @throws \Exception
     */
    public function saveFree(array $appointInfo, int $type = 0, int $tempId = 0)
    {
        $res = true;
        if ($tempId) {
            if ($this->dao->count(['temp_id' => $tempId])) {
                $res = $this->dao->delete($tempId, 'temp_id');
            }
        }
        $placeList = [];
        foreach ($appointInfo as $item) {
            if (isset($item['place']) && is_array($item['place'])) {
                $uniqid = uniqid('adminapi') . rand(1000, 9999);
                foreach ($item['place'] as $value) {
                    if (isset($value['children']) && is_array($value['children'])) {
                        foreach ($value['children'] as $vv) {
                            if (!isset($vv['city_id'])) {
                                throw new AdminException('缺少城市id无法保存');
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
    public function getFreeList(int $tempId)
    {
        $freeIdList = $this->dao->getShippingGroupArray(['temp_id' => $tempId], 'uniqid', 'uniqid', '');
        $freeData = [];
        $infos = $this->dao->getShippingArray(['uniqid' => $freeIdList, 'temp_id' => $tempId], '*', 'uniqid');
        foreach ($freeIdList as $uniqid) {
            $info = $infos[$uniqid];
            $freeData[] = [
                'place' => $this->getFreeTemp($uniqid, $info['province_id']),
                'a_num' => $info['number'] ? floatval($info['number']) : 0,
                'a_price' => $info['price'] ? floatval($info['price']) : 0,
            ];
        }
        foreach ($freeData as &$item) {
            $item['placeName'] = implode(';', array_column($item['place'], 'name'));
        }
        return $freeData;
    }

    /**
     * 获取包邮的省份
     * @param string $uniqid
     * @param int $provinceId
     * @return array
     */
    public function getFreeTemp(string $uniqid, int $provinceId)
    {
        /** @var ShippingTemplatesFreeCityServices $service */
        $service = app()->make(ShippingTemplatesFreeCityServices::class);
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
        /** @var ShippingTemplatesFreeCityServices $service */
        $service = app()->make(ShippingTemplatesFreeCityServices::class);
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
