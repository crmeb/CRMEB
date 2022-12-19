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
use app\model\shipping\ShippingTemplatesFree;

/**
 * 包邮
 * Class ShippingTemplatesFreeDao
 * @package app\dao\shipping
 */
class ShippingTemplatesFreeDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return ShippingTemplatesFree::class;
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
     * 是否可以满足包邮
     * @param $tempId
     * @param $cityid
     * @param $number
     * @param $price
     * @return int
     */
    public function isFree($tempId, $cityid, $number, $price)
    {
        return $this->getModel()->where('temp_id', $tempId)
            ->where('city_id', $cityid)
            ->where('number', '<=', $number)
            ->where('price', '<=', $price)->count();
    }

    /**
     * 是否包邮模版数据列表
     * @param $tempId
     * @param $cityid
     * @param int $price
     * @param string $field
     * @param string $key
     * @return array
     */
    public function isFreeList($tempId, $cityid, $price = 0, string $field = '*', string $key = '')
    {
        return $this->getModel()->where('city_id', $cityid)
            ->when($tempId, function ($query) use ($tempId) {
                if (is_array($tempId)) {
                    $query->whereIn('temp_id', $tempId);
                } else {
                    $query->where('temp_id', $tempId);
                }
            })->when($price, function ($query) use ($price) {
                $query->where('price', '<=', $price);
            })->column($field, $key);
    }
}
