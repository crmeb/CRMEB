<?php
declare(strict_types=1);

namespace app\dao\user;

use crmeb\basic\BaseDao;
use app\model\user\OfflineReceipt;

/**
 * 线下收银记录 DAO
 * 负责 eb_offline_receipt 表的数据访问与统计
 */
class OfflineReceiptDao extends BaseDao
{
    protected function setModel(): string
    {
        return OfflineReceipt::class;
    }

    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }

    public function getSumField(array $where, string $field)
    {
        return $this->search($where, false)->sum($field);
    }

    public function getCountField(array $where)
    {
        return $this->search($where, false)->count();
    }

    /**
     * 获取线下收银订单趋势数据
     * 按时间分组统计收款单数量，用于用户趋势统计
     * @param array $time 时间范围数组
     * @param string $type 渠道类型
     * @param string $timeType 时间格式
     * @return array 返回趋势数据数组
     */
    public function getTrendData($time, $type, $timeType)
    {
        return $this->getModel()->where(function ($query) use ($time) {
            if ($time[0] == $time[1]) {
                $query->whereDay('add_time', $time[0]);
            } else {
                $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                $query->whereTime('add_time', 'between', $time);
            }
        })->field("FROM_UNIXTIME(add_time,'$timeType') as days,count(uid) as num")->group('days')->select()->toArray();
    }

    /**
     * 按条件求和（支持支付时间范围 timeKey）
     * @param array $where 查询条件
     * @param string $field 求和字段
     * @return float 求和结果
     */
    public function getWhereSumField(array $where, string $field)
    {
        $timeKey = $where['timeKey'] ?? [];
        unset($where['timeKey']);
        $query = $this->getModel()->where($where);
        if ($timeKey) {
            $query->whereBetweenTime('pay_time', $timeKey['start_time'], $timeKey['end_time']);
        }
        return $query->sum($field);
    }

    /**
     * 按条件分组查询（支持支付时间范围 timeKey）
     * @param array $where 查询条件
     * @param string $field 求和字段
     * @param string $group 分组字段（如 pay_time）
     * @return array 分组统计结果
     */
    public function getGroupField(array $where, string $field, string $group)
    {
        $timeKey = $where['timeKey'] ?? [];
        unset($where['timeKey']);
        $query = $this->getModel()->where($where);
        $timeUinx = "%H";
        if ($timeKey) {
            $query->whereBetweenTime('pay_time', $timeKey['start_time'], $timeKey['end_time']);
            if ($timeKey['days'] == 1) {
                $timeUinx = "%H";
            } elseif ($timeKey['days'] == 30) {
                $timeUinx = "%Y-%m-%d";
            } elseif ($timeKey['days'] == 365) {
                $timeUinx = "%Y-%m";
            } elseif ($timeKey['days'] > 1 && $timeKey['days'] < 30) {
                $timeUinx = "%Y-%m-%d";
            } elseif ($timeKey['days'] > 30 && $timeKey['days'] < 365) {
                $timeUinx = "%Y-%m";
            }
        }
        return $query->field("sum($field) as number,FROM_UNIXTIME($group, '$timeUinx') as time")
            ->group("FROM_UNIXTIME($group, '$timeUinx')")
            ->order('pay_time ASC')->select()->toArray();
    }
}
