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
declare (strict_types=1);

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\UserRecharge;

/**
 *
 * Class UserRechargeDao
 * @package app\dao\user
 */
class UserRechargeDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserRecharge::class;
    }

    /**
     * 获取充值记录
     * @param array $where
     * @param string $filed
     * @param int $page
     * @param int $limit
     */
    public function getList(array $where, string $filed = "*", int $page, int $limit)
    {
        return $this->search($where)->field($filed)->with([
            'user' => function ($query) {
                $query->field('uid,phone,nickname,avatar');
            }])->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }

    /**
     * 获取某个字段总和
     * @param array $where
     * @param string $field
     * @return float
     */
    public function getWhereSumField(array $where, string $field)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where) {
                $query->whereBetweenTime('pay_time', $where['timeKey']['start_time'], $where['timeKey']['end_time']);
            })
            ->sum($field);
    }

    /**根据某字段分组查询
     * @param array $where
     * @param string $field
     * @param string $group
     * @return mixed
     */
    public function getGroupField(array $where, string $field, string $group)
    {
        return $this->search($where)
            ->when(isset($where['timeKey']), function ($query) use ($where, $field, $group) {
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
                $query->field("sum($field) as number,FROM_UNIXTIME($group, '$timeUinx') as time");
                $query->group("FROM_UNIXTIME($group, '$timeUinx')");
            })
            ->order('add_time ASC')->select()->toArray();

    }

    public function getTrendData($time, $type, $timeType)
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
        })->field("FROM_UNIXTIME(pay_time,'$timeType') as days,count(id) as num")
            ->group('days')->select()->toArray();
    }
}
