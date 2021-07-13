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

namespace app\dao\order;


use app\dao\BaseDao;
use app\model\order\StoreOrder;
use app\model\order\StoreOrderStatus;

/**
 * Class StoreOrderStoreOrderStatusDao
 * @package app\dao\order
 */
class StoreOrderStoreOrderStatusDao extends BaseDao
{
    protected $alias = 'o';

    protected $joinAlis = 's';

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreOrder::class;
    }

    /**
     * 设置链表模型
     * @return string
     */
    protected function setJoinModel(): string
    {
        return StoreOrderStatus::class;
    }

    /**
     * 设置模型
     * @return \crmeb\basic\BaseModel
     */
    protected function getModel()
    {
        $name = app()->make($this->setJoinModel())->getName();
        return parent::getModel()->join($name . ' ' . $this->joinAlis, $this->joinAlis . '.oid = ' . $this->alias . '.id')->alias($this->alias);
    }

    /**
     * 搜索
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    protected function search(array $where = [])
    {
        return $this->getModel()->when(isset($where['paid']), function ($query) use ($where) {
            $query->where($this->alias . '.paid', $where['paid']);
        })->when(isset($where['status']), function ($query) use ($where) {
            $query->where($this->alias . '.status', $where['status']);
        })->when(isset($where['refund_status']), function ($query) use ($where) {
            $query->where($this->alias . '.refund_status', $where['refund_status']);
        })->when(isset($where['is_del']), function ($query) use ($where) {
            $query->where($this->alias . '.is_del', $where['is_del']);
        })->when(isset($where['change_type']), function ($query) use ($where) {
            $query->whereIn($this->joinAlis . '.change_type', $where['change_type']);
        })->when(isset($where['change_time']), function ($query) use ($where) {
            $query->where($this->joinAlis . '.change_time', '<', $where['change_time']);
        });
    }

    /**
     * 获取确认收货订单id
     * @param array $where
     * @return array
     */
    public function getTakeOrderIds(array $where)
    {
        return $this->search($where)->whereIn('refund_status', [0, 1])->field([$this->alias . '.*'])->select()->toArray();
    }
}
