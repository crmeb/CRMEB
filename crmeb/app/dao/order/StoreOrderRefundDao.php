<?php

namespace app\dao\order;

use app\dao\BaseDao;
use app\model\order\StoreOrderRefund;

class StoreOrderRefundDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreOrderRefund::class;
    }

    /**
     * 搜索器
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    public function search(array $where = [])
    {
        $realName = $where['real_name'] ?? '';
        $fieldKey = $where['field_key'] ?? '';
        $fieldKey = $fieldKey == 'all' ? '' : $fieldKey;
        return parent::search($where)->when(isset($where['refund_type']) && $where['refund_type'] !== '', function ($query) use ($where) {
            if ($where['refund_type'] == 0) {
                $query->where('refund_type', '>', 0);
            } else {
                if (is_array($where['refund_type'])) {
                    $query->whereIn('refund_type', $where['refund_type']);
                } else {
                    $query->where('refund_type', $where['refund_type']);
                }
            }
        })->when(isset($where['order_id']) && $where['order_id'] != '', function ($query) use ($where) {
            $query->where(function ($q) use ($where) {
                $q->whereLike('order_id', '%' . $where['order_id'] . '%')->whereOr('store_order_id', 'IN', function ($orderModel) use ($where) {
                    $orderModel->name('store_order')->field('id')->whereLike('order_id', '%' . $where['order_id'] . '%');
                });
            });
        })->when($realName && !$fieldKey, function ($query) use ($where) {
            $query->where(function ($que) use ($where) {
                $que->whereLike('order_id', '%' . $where['real_name'] . '%')->whereOr(function ($q) use ($where) {
                    $q->whereOr('uid', 'in', function ($q) use ($where) {
                        $q->name('user')->whereLike('nickname|uid|phone', '%' . $where['real_name'] . '%')->field(['uid'])->select();
                    })->whereOr('store_order_id', 'in', function ($que) use ($where) {
                        $que->name('store_order_cart_info')->whereIn('product_id', function ($q) use ($where) {
                            $q->name('store_product')->whereLike('store_name|keyword', '%' . $where['real_name'] . '%')->field(['id'])->select();
                        })->field(['oid'])->select();
                    })->whereOr('store_order_id', 'in', function ($orderModel) use ($where) {
                        $orderModel->name('store_order')->field('id')->whereLike('order_id', '%' . $where['real_name'] . '%');
                    });
                });
            });
        })->when(isset($where['refundTypes']) && $where['refundTypes'] != '', function ($query) use ($where) {
            switch ((int)$where['refundTypes']) {
                case 1:
                    $query->where('refund_type', 'in', '1,2');
                    break;
                case 2:
                    $query->where('refund_type', 4);
                    break;
                case 3:
                    $query->where('refund_type', 5);
                    break;
                case 4:
                    $query->where('refund_type', 6);
                    break;
            }
        });
    }

    /**
     * 退款订单列表
     * @param $where
     * @param int $page
     * @param int $limit
     * @param string $field
     * @param array $with
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($where, $page = 0, $limit = 0, $field = '*', $with = [])
    {
        return $this->search($where)->field($field)->with(array_merge(['user'], $with))->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id DESC')->select()->toArray();
    }

    /**
     * 根据时间获取
     * @param array $where
     * @return float|int
     */
    public function getOrderRefundMoneyByWhere(array $where, string $sum_field, string $selectType, string $group = "")
    {
        switch ($selectType) {
            case "sum" :
                return $this->getDayTotalMoney($where, $sum_field);
            case "group" :
                return $this->getDayGroupMoney($where, $sum_field, $group);
        }
    }

    /**
     * 按照支付时间统计支付金额
     * @param array $where
     * @param string $sumField
     * @return mixed
     */
    public function getDayTotalMoney(array $where, string $sumField)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('add_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->sum($sumField);
    }

    /**
     * 时间分组订单付款金额统计
     * @param array $where
     * @param string $sumField
     * @return mixed
     */
    public function getDayGroupMoney(array $where, string $sumField, string $group)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where, $sumField, $group) {
                $query->whereBetweenTime('add_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
                if ($where['timeKey']['days'] == 1) {
                    $timeUinx = "%H";
                } elseif ($where['timeKey']['days'] == 30) {
                    $timeUinx = "%Y-%m-%d";
                } elseif ($where['timeKey']['days'] == 365) {
                    $timeUinx = "%Y-%m";
                } elseif ($where['timeKey']['days'] > 1 && $where['timeKey']['days'] < 30) {
                    $timeUinx = "%Y-%m-%d";
                } elseif ($where['timeKey']['days'] > 30 && $where['timeKey']['days'] < 365) {
                    $timeUinx = "%Y-%m";
                }
                $query->field("sum($sumField) as number,FROM_UNIXTIME($group, '$timeUinx') as time");
                $query->group("FROM_UNIXTIME($group, '$timeUinx')");
            })
            ->order('add_time ASC')->select()->toArray();
    }
}
