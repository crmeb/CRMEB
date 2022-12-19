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

namespace app\dao\shipping;


use app\dao\BaseDao;
use app\model\shipping\ShippingTemplatesNoDelivery;

/**
 * 不送达
 * Class ShippingTemplatesNoDeliveryDao
 * @package app\dao\shipping
 */
class ShippingTemplatesNoDeliveryDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return ShippingTemplatesNoDelivery::class;
    }

    /**
     * 获取运费模板列表并按照指定字段进行分组
     * @param array $where
     * @param string $group
     * @param string $field
     * @param string $key
     * @return mixed
     */
    public function getShippingGroupArray(array $where, string $group, string $field, string $key)
    {
        return $this->search($where)->group($group)->column($field, $key);
    }

    /**
     * 获取运费模板列表
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getShippingArray(array $where, string $field, string $key)
    {
        return $this->search($where)->column($field, $key);
    }

    /**
     * 是否不送达
     * @param $tempId
     * @param $cityid
     * @return int
     */
    public function isNoDelivery($tempId, $cityid)
    {
        if (is_array($tempId)) {
            return $this->getModel()->where('temp_id', 'in', $tempId)->where('city_id', $cityid)->column('temp_id');
        } else {
            return $this->getModel()->where('temp_id', $tempId)->where('city_id', $cityid)->count();
        }
    }

}
