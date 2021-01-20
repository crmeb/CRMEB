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

/**
 * 订单
 * Class StoreOrderDao
 * @package app\dao\order
 */
class StoreOrderDao extends BaseDao
{

    /**
     * 限制精确查询字段
     * @var string[]
     */
    protected $withField = ['uid', 'order_id', 'real_name', 'user_phone', 'title'];

    /**
     * @return string
     */
    protected function setModel(): string
    {
        return StoreOrder::class;
    }

    /**
     * 订单搜索
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    public function search(array $where = [])
    {
        $isDel = isset($where['is_del']) && $where['is_del'] !== '' && $where['is_del'] != -1;
        $realName = $where['real_name'] ?? '';
        $fieldKey = $where['field_key'] ?? '';
        $fieldKey = $fieldKey == 'all' ? '' : $fieldKey;
        return parent::search($where)->when($isDel, function ($query) use ($where) {
            $query->where('is_del', $where['is_del']);
        })->when(isset($where['is_system_del']), function ($query) {
            $query->where('is_system_del', 0);
        })->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
            switch ((int)$where['status']) {
                case 0://未支付
                    $query->where('paid', 0)->where('status', 0)->where('refund_status', 0)->where('is_del', 0);
                    break;
                case 1://已支付 未发货
                    $query->where('paid', 1)->where('status', 0)->where('refund_status', 0)->when(isset($where['shipping_type']), function ($query) {
                        $query->where('shipping_type', 1);
                    })->where('is_del', 0);
                    break;
                case 2://已支付  待收货
                    $query->where('paid', 1)->where('status', 1)->where('refund_status', 0)->where('is_del', 0);
                    break;
                case 3:// 已支付  已收货  待评价
                    $query->where('paid', 1)->where('status', 2)->where('refund_status', 0)->where('is_del', 0);
                    break;
                case 4:// 交易完成
                    $query->where('paid', 1)->where('status', 3)->where('refund_status', 0)->where('is_del', 0);
                    break;
                case 5://已支付  待核销
                    $query->where('paid', 1)->where('status', 0)->where('refund_status', 0)->where('shipping_type', 2)->where('is_del', 0);
                    break;
                case 6://已支付 已核销 没有退款
                    $query->where('paid', 1)->where('status', 2)->where('refund_status', 0)->where('shipping_type', 2)->where('is_del', 0);
                    break;
                case -1://退款中
                    $query->where('paid', 1)->where('refund_status', 1)->where('is_del', 0);
                    break;
                case -2://已退款
                    $query->where('paid', 1)->where('refund_status', 2)->where('is_del', 0);
                    break;
                case -3://退款
                    $query->where('paid', 1)->where('refund_status', 'in', '1,2')->where('is_del', 0);
                    break;
                case -4://已删除
                    $query->where('is_del', 1);
                    break;
            }
        })->when(isset($where['pay_type']), function ($query) use ($where) {
            switch ($where['pay_type']) {
                case 1:
                    $query->where('pay_type', 'weixin');
                    break;
                case 2:
                    $query->where('pay_type', 'yue');
                    break;
                case 3:
                    $query->where('pay_type', 'offline');
                    break;
            }
        })->when($realName && $fieldKey && in_array($fieldKey, $this->withField), function ($query) use ($where, $realName, $fieldKey) {
            if ($fieldKey !== 'title') {
                $query->where(trim($fieldKey), trim($realName));
            } else {
                $query->where('id', 'in', function ($que) use ($where) {
                    $que->name('store_order_cart_info')->whereIn('product_id', function ($q) use ($where) {
                        $q->name('store_product')->whereLike('store_name|keyword', '%' . $where['real_name'] . '%')->field(['id'])->select();
                    })->field(['oid'])->select();
                });
            }
        })->when($realName && !$fieldKey, function ($query) use ($where) {
            $query->where(function ($que) use ($where) {
                $que->whereLike('order_id|real_name', '%' . $where['real_name'] . '%')->whereOr('uid', 'in', function ($q) use ($where) {
                    $q->name('user')->whereLike('nickname|uid|phone', '%' . $where['real_name'] . '%')->field(['uid'])->select();
                })->whereOr('id', 'in', function ($que) use ($where) {
                    $que->name('store_order_cart_info')->whereIn('product_id', function ($q) use ($where) {
                        $q->name('store_product')->whereLike('store_name|keyword', '%' . $where['real_name'] . '%')->field(['id'])->select();
                    })->field(['oid'])->select();
                });
            });
        })->when(isset($where['store_id']) && $where['store_id'], function ($query) use ($where) {
            $query->where('store_id', $where['store_id']);
        })->when(isset($where['unique']), function ($query) use ($where) {
            $query->where('unique', $where['unique']);
        })->when(isset($where['is_remind']), function ($query) use ($where) {
            $query->where('is_remind', $where['is_remind']);
        });
    }

    /**
     * 订单搜索列表
     * @param array $where
     * @param array $field
     * @param int $page
     * @param int $limit
     * @param array $with
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderList(array $where, array $field, int $page, int $limit, array $with = [])
    {
        return $this->search($where)->field($field)->with(array_merge(['user'], $with))->page($page, $limit)->order('pay_time DESC,id DESC')->select()->toArray();
    }

    /**
     * 获取订单总数
     * @param array $where
     * @return int
     */
    public function count(array $where = []): int
    {
        return $this->search($where)->count();
    }

    /**
     * 聚合查询
     * @param array $where
     * @param string $field
     * @param string $together
     * @return int
     */
    public function together(array $where, string $field, string $together = 'sum')
    {
        if (!in_array($together, ['sum', 'max', 'min', 'avg'])) {
            return 0;
        }
        return $this->search($where)->{$together}($field);
    }

    /**
     * 查找指定条件下的订单数据以数组形式返回
     * @param array $where
     * @param string $field
     * @param string $key
     * @param string $group
     * @return array
     */
    public function column(array $where, string $field, string $key = '', string $group = '')
    {
        return $this->search($where)->when($group, function ($query) use ($group) {
            $query->group($group);
        })->column($field, $key);
    }

    /**
     * 获取订单id下没有删除的订单数量
     * @param array $ids
     * @return int
     */
    public function getOrderIdsCount(array $ids)
    {
        return $this->getModel()->whereIn('id', $ids)->where('is_del', 0)->count();
    }

    /**
     * 获取一段时间内订单列表
     * @param $datebefor
     * @param $dateafter
     * @return mixed
     */
    public function orderAddTimeList($datebefor, $dateafter, $timeType = "week")
    {
        return $this->getModel()->where('add_time', 'between time', [$datebefor, $dateafter])
            ->when($timeType, function ($query) use ($timeType) {
                switch ($timeType) {
                    case "week" :
                        $timeUnix = "%w";
                        break;
                    case "month" :
                        $timeUnix = "%d";
                        break;
                    case "year" :
                        $timeUnix = "%m";
                        break;
                    case "30" :
                        $timeUnix = "%m-%d";
                        break;
                }
                $query->field("FROM_UNIXTIME(add_time,'$timeUnix') as day,count(*) as count,sum(pay_price) as price");
                $query->group("FROM_UNIXTIME(add_time, '$timeUnix')");
            })
            ->order('add_time asc')
            ->select()->toArray();
    }

    /**
     * 统计总数上期
     * @param $pre_datebefor
     * @param $pre_dateafter
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function preTotalFind($pre_datebefor, $pre_dateafter)
    {
        return $this->getModel()->where('add_time', 'between time', [$pre_datebefor, $pre_dateafter])
            ->field("count(*) as count,sum(pay_price) as price")
            ->find();
    }

    /**
     * 获取一段时间内订单列表
     * @param $now_datebefor
     * @param $now_dateafter
     * @return mixed
     */
    public function nowOrderList($now_datebefor, $now_dateafter, $timeType = "week")
    {
        return $this->getModel()->where('add_time', 'between time', [$now_datebefor, $now_dateafter])
            ->when($timeType, function ($query) use ($timeType) {
                switch ($timeType) {
                    case "week" :
                        $timeUnix = "%w";
                        break;
                    case "month" :
                        $timeUnix = "%d";
                        break;
                    case "year" :
                        $timeUnix = "%m";
                        break;
                }
                $query->field("FROM_UNIXTIME(add_time,'$timeUnix') as day,count(*) as count,sum(pay_price) as price");
                $query->group("FROM_UNIXTIME(add_time, '$timeUnix')");
            })
            ->order('add_time asc')
            ->select()->toArray();
    }

    /**
     * 获取订单数量
     * @return int
     */
    public function storeOrderCount()
    {
        return $this->search(['paid' => 1, 'is_del' => 0, 'refund_status' => 0, 'status' => 1, 'shipping_type' => 1])->count();
    }

    /**
     * 获取特定时间内订单总价
     * @param $time
     * @return float
     */
    public function todaySales($time)
    {
        return $this->search(['paid' => 1, 'is_del' => 0, 'refund_status' => 0, 'time' => $time ?: 'today', 'timekey' => 'pay_time'])->sum('pay_price');
    }

    /**
     * 获取特定时间内订单总价
     * @param $time
     * @return float
     */
    public function thisWeekSales($time)
    {
        return $this->search(['paid' => 1, 'is_del' => 0, 'refund_status' => 0, 'time' => $time ?: 'week', 'timeKey' => 'pay_time'])->sum('pay_price');
    }

    /**
     * 总销售额
     * @return float
     */
    public function totalSales($time)
    {
        return $this->search(['paid' => 1, 'is_del' => 0, 'refund_status' => 0, 'time' => $time ?: 'today', 'timekey' => 'pay_time'])->sum('pay_price');
    }

    public function newOrderUpdates($newOrderId)
    {
        return $this->getModel()->where('order_id', 'in', $newOrderId)->update(['is_remind' => 1]);
    }

    /**
     * 获取特定时间内订单量
     * @param $time
     * @return float
     */
    public function todayOrderVisit($time, $week)
    {
        switch ($week) {
            case 1:
                return $this->search(['time' => $time ?: 'today', 'timeKey' => 'add_time'])->count();
            case 2:
                return $this->search(['time' => $time ?: 'week', 'timeKey' => 'add_time'])->count();
        }
    }

    /**
     * 获取订单详情
     * @param $uid
     * @param $key
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserOrderDetail(string $key, int $uid)
    {
        return $this->getOne(['order_id|unique' => $key, 'uid' => $uid, 'is_del' => 0]);
    }

    /**
     * 获取用户推广订单
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param array $with
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStairOrderList(array $where, string $field, int $page, int $limit, array $with = [])
    {
        return $this->search($where)->with($with)->field($field)->page($page, $limit)->order('id DESC')->select()->toArray();
    }

    /**
     * 订单每月统计数据
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getOrderDataPriceCount(array $field, int $page, int $limit)
    {
        return $this->search(['is_del' => 0, 'paid' => 1, 'refund_status' => 0])
            ->field($field)->group("FROM_UNIXTIME(add_time, '%Y-%m-%d')")
            ->order('add_time DESC')->page($page, $limit)->select()->toArray();
    }

    /**
     * 获取当前时间到指定时间的支付金额 管理员
     * @param $start 开始时间
     * @param $stop  结束时间
     * @return mixed
     */
    public function chartTimePrice($start, $stop)
    {
        return $this->search(['is_del' => 0, 'paid' => 1, 'refund_status' => 0])
            ->where('add_time', '>=', $start)
            ->where('add_time', '<', $stop)
            ->field('sum(pay_price) as num,FROM_UNIXTIME(add_time, \'%Y-%m-%d\') as time')
            ->group("FROM_UNIXTIME(add_time, '%Y-%m-%d')")
            ->order('add_time ASC')->select()->toArray();
    }

    /**
     * 获取当前时间到指定时间的支付订单数 管理员
     * @param $start 开始时间
     * @param $stop  结束时间
     * @return mixed
     */
    public function chartTimeNumber($start, $stop)
    {
        return $this->search(['is_del' => 0, 'paid' => 1, 'refund_status' => 0])
            ->where('add_time', '>=', $start)
            ->where('add_time', '<', $stop)
            ->field('count(id) as num,FROM_UNIXTIME(add_time, \'%Y-%m-%d\') as time')
            ->group("FROM_UNIXTIME(add_time, '%Y-%m-%d')")
            ->order('add_time ASC')->select()->toArray();
    }

    /**
     * 获取用户已购买此活动商品的个数
     * @param $uid
     * @param $type
     * @param $typeId
     * @return int
     */
    public function getBuyCount($uid, $type, $typeId): int
    {
        return $this->getModel()
                ->where('uid', $uid)
                ->where($type, $typeId)
                ->where(function ($query) {
                    $query->where('paid', 1)->whereOr(function ($query1) {
                        $query1->where('paid', 0)->where('is_del', 0);
                    });
                })->value('sum(total_num)') ?? 0;
    }

    /**
     * 获取没有支付的订单列表
     * @param array|string[] $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderUnPaidList(array $field = ['*'])
    {
        return $this->getModel()->where(['paid' => 0, 'is_del' => 0, 'status' => 0, 'refund_status' => 0])
            ->where('pay_type', '<>', 'offline')->field($field)->select();
    }

    /** 根据时间获取营业额
     * @param array $where
     * @return float|int
     */
    public function getOrderMoneyByTime(array $where)
    {
        if (isset($where['day'])) {
            return $this->getModel()->where(['refund_status' => 0, 'paid' => 1])->whereDay('add_time', date("Y-m-d", strtotime($where['day'])))->sum('pay_price');
        }
        return 0;
    }


    /**
     * 用户趋势数据
     * @param $time
     * @param $type
     * @param $timeType
     * @return mixed
     */
    public function getTrendData($time, $type, $timeType, $str)
    {
        return $this->getModel()->when($type != '', function ($query) use ($type) {
            $query->where('channel_type', $type);
        })->where(function ($query) use ($time) {
            if ($time[0] == $time[1]) {
                $query->whereDay('pay_time', $time[0]);
            } else {
                $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                $query->whereTime('pay_time', 'between', $time);
            }
        })->field("FROM_UNIXTIME(pay_time,'$timeType') as days,$str as num")
            ->group('days')->select()->toArray();
    }

    /**
     * 用户地域数据
     * @param $time
     * @param $userType
     * @return mixed
     */
    public function getRegion($time, $userType)
    {
        return $this->getModel()->when($userType != '', function ($query) use ($userType) {
            $query->where('channel_type', $userType);
        })->where(function ($query) use ($time) {
            if ($time[0] == $time[1]) {
                $query->whereDay('pay_time', $time[0]);
            } else {
                $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                $query->whereTime('pay_time', 'between', $time);
            }
        })->field('sum(pay_price) as payPrice,province')
            ->group('province')->select()->toArray();
    }



    /** 按照支付时间统计支付金额
     * @param array $where
     * @param string $sumField
     * @return mixed
     */
    public function getDayTotalMoney(array $where, string $sumField)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->sum($sumField);
    }

    /**时间段订单数统计
     * @param array $where
     * @param string $countField
     * @return int
     */
    public function getDayOrderCount(array $where, string $countField = "*")
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->count($countField);
    }

    /** 时间分组订单付款金额统计
     * @param array $where
     * @param string $sumField
     * @return mixed
     */
    public function getDayGroupMoney(array $where, string $sumField, string $group)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where, $sumField, $group) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
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
            ->order('pay_time ASC')->select()->toArray();
        // echo $this->getModel()->getLastSql();
    }

    /**时间分组订单数统计
     * @param array $where
     * @param string $sumField
     * @return mixed
     */
    public function getOrderGroupCount(array $where, string $sumField = "*")
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where, $sumField) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
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
                $query->field("count($sumField) as number,FROM_UNIXTIME(pay_time, '$timeUinx') as time");
                $query->group("FROM_UNIXTIME(pay_time, '$timeUinx')");
            })
            ->order('pay_time ASC')->select()->toArray();
        // echo $this->getModel()->getLastSql();die;
    }

    /**时间段支付订单人数
     * @param $where
     * @return mixed
     */
    public function getPayOrderPeople($where)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->field('uid')
            ->distinct(true)
            ->select()->toArray();
        //echo $this->getModel()->getLastSql();die;
    }

    /**时间段分组统计支付订单人数
     * @param $where
     * @return mixed
     */
    public function getPayOrderGroupPeople($where)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
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
                $query->field("count(distinct uid) as number,FROM_UNIXTIME(pay_time, '$timeUinx') as time");
                $query->group("FROM_UNIXTIME(pay_time, '$timeUinx')");
            })
            ->order('pay_time ASC')->select()->toArray();
        //echo $this->getModel()->getLastSql();die;
    }
}
