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
declare (strict_types=1);

namespace app\services\shipping;

use app\services\BaseServices;
use app\dao\shipping\ShippingTemplatesDao;
use crmeb\exceptions\AdminException;

/**
 * 运费模板
 * Class ShippingTemplatesServices
 * @package app\services\shipping
 * @method getSelectList() 获取下拉选择列表
 * @method get($id) 获取一条数据
 * @method getShippingColumn(array $where, string $field, string $key) 获取运费模板指定条件下的数据
 */
class ShippingTemplatesServices extends BaseServices
{

    /**
     * 构造方法
     * ShippingTemplatesServices constructor.
     * @param ShippingTemplatesDao $dao
     */
    public function __construct(ShippingTemplatesDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取运费模板列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getShippingList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getShippingList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('data', 'count');
    }

    /**
     * 获取需要修改的运费模板
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getShipping(int $id)
    {
        $templates = $this->dao->get($id);
        if (!$templates) {
            throw new AdminException(400592);
        }
        /** @var ShippingTemplatesFreeServices $freeServices */
        $freeServices = app()->make(ShippingTemplatesFreeServices::class);
        /** @var ShippingTemplatesRegionServices $regionServices */
        $regionServices = app()->make(ShippingTemplatesRegionServices::class);
        /** @var ShippingTemplatesNoDeliveryServices $noDeliveryServices */
        $noDeliveryServices = app()->make(ShippingTemplatesNoDeliveryServices::class);
        $data['appointList'] = $freeServices->getFreeList($id);
        $data['templateList'] = $regionServices->getRegionList($id);
        $data['noDeliveryList'] = $noDeliveryServices->getNoDeliveryList($id);
        if (!isset($data['templateList'][0]['region'])) {
            $data['templateList'][0]['region'] = ['city_id' => 0, 'name' => '默认全国'];
        }
        $data['formData'] = [
            'name' => $templates->name,
            'type' => $templates->getData('type'),
            'appoint_check' => intval($templates->getData('appoint')),
            'no_delivery_check' => intval($templates->getData('no_delivery')),
            'sort' => intval($templates->getData('sort')),
        ];
        return $data;
    }

    /**
     * 保存或者修改运费模板
     * @param int $id
     * @param array $temp
     * @param array $data
     * @return mixed
     */
    public function save(int $id, array $temp, array $data)
    {
        if ($id) {
            $res = $this->dao->update($id, $temp);
        } else {
            $id = $this->dao->insertGetId($temp);
            $res = true;
        }

        /** @var ShippingTemplatesRegionServices $regionServices */
        $regionServices = app()->make(ShippingTemplatesRegionServices::class);


        return $this->transaction(function () use ($regionServices, $data, $id, $res) {
            //设置区域配送
            $res = $res && $regionServices->saveRegion($data['region_info'], (int)$data['type'], (int)$id);
            if (!$res) {
                throw new AdminException(400593);
            }
            //设置指定包邮
            if ($data['appoint']) {
                /** @var ShippingTemplatesFreeServices $freeServices */
                $freeServices = app()->make(ShippingTemplatesFreeServices::class);
                $res = $res && $freeServices->saveFree($data['appoint_info'], (int)$data['type'], (int)$id);
            }

            //设置不送达
            if ($data['no_delivery']) {
                /** @var ShippingTemplatesNoDeliveryServices $noDeliveryServices */
                $noDeliveryServices = app()->make(ShippingTemplatesNoDeliveryServices::class);
                $res = $res && $noDeliveryServices->saveNoDelivery($data['no_delivery_info'], (int)$id);
            }

            if ($res) {
                return true;
            } else {
                throw new AdminException(100006);
            }
        });
    }

    /**
     * 删除运费模板
     * @param int $id
     */
    public function detete(int $id)
    {
        $this->dao->delete($id);
        /** @var ShippingTemplatesFreeServices $freeServices */
        $freeServices = app()->make(ShippingTemplatesFreeServices::class);
        /** @var ShippingTemplatesRegionServices $regionServices */
        $regionServices = app()->make(ShippingTemplatesRegionServices::class);
        /** @var ShippingTemplatesNoDeliveryServices $noDeliveryServices */
        $noDeliveryServices = app()->make(ShippingTemplatesNoDeliveryServices::class);
        $freeServices->delete($id, 'temp_id');
        $regionServices->delete($id, 'temp_id');
        $noDeliveryServices->delete($id, 'temp_id');
    }
}
