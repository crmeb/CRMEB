<?php
declare(strict_types=1);

namespace app\dao\user;

use crmeb\basic\BaseDao;
use app\model\user\UserRecharge;

class UserRechargeDao extends BaseDao
{
    protected function setModel(): string
    {
        return UserRecharge::class;
    }

    public function getList(array $where, string $filed = "*", int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($filed)->with([
            'user' => function ($query) {
                $query->field('uid,phone,nickname,avatar');
            }])->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }

    public function getWhereSumField(array $where, string $field)
    {
        return $this->search($where, false)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->sum($field);
    }

    public function getGroupField(array $where, string $field, string $group)
    {
        return $this->search($where, false)
            ->when(isset($where['timeKey']), function ($query) use ($where, $field, $group) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
                $timeUinx = "%H";
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
                $query->field("sum($field) as number,FROM_UNIXTIME($group, '$timeUinx') as time");
                $query->group("FROM_UNIXTIME($group, '$timeUinx')");
            })
            ->order('add_time ASC')->select()->toArray();
    }

    public function getTrendData($time, $type, $timeType)
    {
        return $this->getModel()->when($type !== '' && $type !== null, function ($query) use ($type) {
            $query->where('channel_type', $type);
        })->where(function ($query) use ($time) {
            if ($time[0] == $time[1]) {
                $query->whereDay('pay_time', $time[0]);
            } else {
                $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                $query->whereTime('pay_time', 'between', $time);
            }
        })->field("FROM_UNIXTIME(pay_time,'$timeType') as days,count(id) as num")
            ->group('days')->select()->toArray();
    }
}
