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

namespace app\dao\system\statistics;

use app\dao\BaseDao;
use app\model\system\statistics\CapitalFlow;

class CapitalFlowDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return CapitalFlow::class;
    }

    /**
     * 资金流水
     * @param $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($where, $page = 0, $limit = 0)
    {
        return $this->search($where)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }

    /**
     * 账单记录
     * @param $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecordList($where, int $page = 0, int $limit = 0)
    {
        $timeUnix = "%Y-%m-%d";
        switch ($where['type']) {
            case "day" :
                $timeUnix = "%Y-%m-%d";
                break;
            case "week" :
                $timeUnix = "%u";
                break;
            case "month" :
                $timeUnix = "%Y-%m";
                break;
        }
        $model = $this->search($where)
            ->when(isset($where['type']) && $where['type'] !== '', function ($query) use ($where, $timeUnix) {
                $query->field("FROM_UNIXTIME(add_time,'$timeUnix') as day,sum(if(price >= 0,price,0)) as income_price,sum(if(price < 0,price,0)) as exp_price,add_time");
                $query->group("FROM_UNIXTIME(add_time, '$timeUnix')");
            });
        $count = $model->count();
        $list = $model->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time desc')->select()->toArray();
        foreach ($list as &$item) {
            if ($where['type'] == 'day') {
                $item['ids'] = array_merge($this->getModel()->whereDay('add_time', $item['day'])->column('id'));
            } elseif ($where['type'] == 'week') {
                $day = $this->weekDayTime(date('Y'), $item['day']);
                $item['ids'] = array_merge($this->getModel()->whereWeek('add_time', $day)->column('id'));
            } elseif ($where['type'] == 'month') {
                $item['ids'] = array_merge($this->getModel()->whereMonth('add_time', $item['day'])->column('id'));
            }
        }
        return compact('list', 'count');
    }

    /**
     * 获取某年第几周的开始日期
     * @param int $year
     * @param int $week
     * @return array|false|string
     */
    public function weekDayTime(int $year, int $week = 1)
    {
        $year_start = mktime(0, 0, 0, 1, 1, $year);
        // 判断第一天是否为第一周的开始
        if (intval(date('W', $year_start)) === 1) {
            $start = $year_start;//把第一天做为第一周的开始
        } else {
            $start = strtotime('+1 monday', $year_start);//把第一个周一作为开始
        }
        // 第几周的开始时间
        if ($week === 1) {
            $weekday = $start;
        } else {
            $weekday = strtotime('+' . ($week - 0) . ' monday', $start);
        }
        return date('Y-m-d', $weekday);
    }
}
