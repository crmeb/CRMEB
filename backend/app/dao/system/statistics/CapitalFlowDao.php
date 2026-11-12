<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\dao\system\statistics;

use crmeb\basic\BaseDao;
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
        // type 只表示分组粒度，不是表字段；从 where 剥离，避免 search=true 时误写成 where type=day
        $type = (string)($where['type'] ?? 'day');
        unset($where['type']);

        $timeUnix = match ($type) {
            'week' => '%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };
        // max(add_time) 满足 ONLY_FULL_GROUP_BY；裸 add_time 在严格 sql_mode 下会直接报错
        $field = "FROM_UNIXTIME(add_time,'{$timeUnix}') as day,"
            . "sum(if(price >= 0,price,0)) as income_price,"
            . "sum(if(price < 0,price,0)) as exp_price,"
            . "max(add_time) as add_time";
        $group = "FROM_UNIXTIME(add_time, '{$timeUnix}')";

        // countWithGroup 会把当前查询的 field 改成 count(*)，不能与 select 共用同一条 query
        $count = (int)$this->search($where, false)->field($field)->group($group)->count();
        $list = $this->search($where, false)
            ->field($field)
            ->group($group)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order('add_time desc')
            ->select()
            ->toArray();

        $cashOnly = !empty($where['cash_only']);
        foreach ($list as &$item) {
            $idsQuery = $this->getModel();
            if ($cashOnly) {
                $idsQuery = $idsQuery->whereNotIn('pay_type', ['', 'yue', 'admin']);
            }
            if ($type === 'day') {
                $item['ids'] = array_values($idsQuery->whereDay('add_time', $item['day'])->column('id'));
            } elseif ($type === 'week') {
                $day = $this->weekDayTime((int)date('Y'), (int)$item['day']);
                $item['ids'] = array_values($idsQuery->whereWeek('add_time', $day)->column('id'));
            } elseif ($type === 'month') {
                $item['ids'] = array_values($idsQuery->whereMonth('add_time', $item['day'])->column('id'));
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
