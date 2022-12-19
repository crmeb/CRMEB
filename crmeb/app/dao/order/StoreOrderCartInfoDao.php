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

namespace app\dao\order;


use app\dao\BaseDao;
use app\model\order\StoreOrderCartInfo;

/**
 * 订单详情
 * Class StoreOrderCartInfoDao
 * @package app\dao\order
 */
class StoreOrderCartInfoDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreOrderCartInfo::class;
    }

    /**
     * 获取购物车详情列表
     * @param array $where
     * @param array $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCartInfoList(array $where, array $field)
    {
        return $this->search($where)->field($field)->select()->toArray();
    }

    /**
     * 获取购物车信息以数组返回
     * @param array $where
     * @param string $field
     * @param string $key
     */
    public function getCartColunm(array $where, string $field, string $key = '')
    {
        return $this->search($where)->column($field, $key);
    }

    public function getSplitCartNum($cart_ids)
    {
        $res = $this->getModel()->whereIn('old_cart_id', $cart_ids)->field('sum(cart_num) as num,old_cart_id')->group('old_cart_id')->select()->toArray();
        $data = [];
        foreach ($res as $value) {
            $data[$value['old_cart_id']] = $value['num'];
        }
        return $data;
    }
}
