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

namespace app\dao\shipping;


use app\dao\BaseDao;
use app\model\shipping\ShippingTemplatesRegion;

/**
 * 指定邮费
 * Class ShippingTemplatesRegionDao
 * @package app\dao\shipping
 */
class ShippingTemplatesRegionDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return ShippingTemplatesRegion::class;
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
     * 根据运费模板id和城市id获得包邮数据列表
     * @param array $tempIds
     * @param array $cityId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTempRegionList(array $tempIds, array $cityId)
    {
        return $this->getModel()->whereIn('temp_id', $tempIds)->whereIn('city_id', $cityId)->order('city_id asc')->select()->toArray();
    }
}
