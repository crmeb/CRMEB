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
use app\model\shipping\SystemCity;

/**
 *
 * Class ShippingTemplatesRegionCityDao
 * @package app\dao\shipping
 */
class ShippingTemplatesRegionCityDao extends BaseDao
{

    /**
     * 当前表别名
     * @var string
     */
    protected $alias = 'a';

    /**
     * 设置join连表别名
     * @var string
     */
    protected $joinAlis = 'c';

    /**
     * 设置当前模型
     * @return string
     */
    protected function setModel(): string
    {
        return ShippingTemplatesRegion::class;
    }

    /**
     * 设置连表模型
     * @return string
     */
    protected function setJoinModel(): string
    {
        return SystemCity::class;
    }

    /**
     * 关联模型
     * @param string $alias
     * @param string $join_alias
     * @return \crmeb\basic\BaseModel
     */
    protected function getModel(string $key = 'province_id', string $join = 'LEFT')
    {
        /** @var SystemCity $city */
        $city = app()->make($this->setJoinModel());
        $name = $city->getName();
        return parent::getModel()->join($name . ' ' . $this->joinAlis, $this->alias . '.' . $key . ' = ' . $this->joinAlis . '.city_id', $join)->alias($this->alias);
    }

    /**
     * 获取指定条件下的包邮列表
     * @param array $where
     * @return mixed
     */
    public function getUniqidList(array $where, bool $group = true)
    {
        return $this->getModel($group ? 'province_id' : 'city_id')->when(isset($where['uniqid']), function ($query) use ($where) {
            $query->where($this->alias . '.uniqid', $where['uniqid']);
        })->when(isset($where['province_id']), function ($query) use ($where) {
            $query->where($this->alias . '.province_id', $where['province_id']);
        })->when($group, function ($query) {
            $query->group($this->alias . '.province_id');
        })->field($this->alias . '.province_id,' . $this->joinAlis . '.name,' . $this->alias . '.city_id')->select()->toArray();
    }
}
