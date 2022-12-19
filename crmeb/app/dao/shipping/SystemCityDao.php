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
use app\model\shipping\SystemCity;

/**
 * 城市数据
 * Class SystemCityDao
 * @package app\dao\shipping
 */
class SystemCityDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemCity::class;
    }

    /**
     * 获取城市数据列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCityList(array $where,string $field = '*')
    {
        return $this->search($where)->field($field)->select()->toArray();
    }

    /**
     * 获取城市数据以数组形式返回
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getCityArray(array $where, string $field, string $key)
    {
        return $this->search($where)->column($field, $key);
    }

    /**
     * 删除上级城市和当前城市id
     * @param int $cityId
     * @return bool
     * @throws \Exception
     */
    public function deleteCity(int $cityId)
    {
        return $this->getModel()->where('city_id', $cityId)->whereOr('parent_id', $cityId)->delete();
    }

    /**
     * 获取city_id的最大值
     * @return mixed
     */
    public function getCityIdMax()
    {
        return $this->getModel()->max('city_id');
    }

    /**
     * 获取运费模板城市选择
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getShippingCity()
    {
        return $this->getModel()->with('children')->where('parent_id', 0)->order('id asc')->select()->toArray();
    }
}
