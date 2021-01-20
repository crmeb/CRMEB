<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\db\concern;

/**
 * 时间查询支持
 */
trait TimeFieldQuery
{
    /**
     * 日期查询表达式
     * @var array
     */
    protected $timeRule = [
        'today'      => ['today', 'tomorrow -1second'],
        'yesterday'  => ['yesterday', 'today -1second'],
        'week'       => ['this week 00:00:00', 'next week 00:00:00 -1second'],
        'last week'  => ['last week 00:00:00', 'this week 00:00:00 -1second'],
        'month'      => ['first Day of this month 00:00:00', 'first Day of next month 00:00:00 -1second'],
        'last month' => ['first Day of last month 00:00:00', 'first Day of this month 00:00:00 -1second'],
        'year'       => ['this year 1/1', 'next year 1/1 -1second'],
        'last year'  => ['last year 1/1', 'this year 1/1 -1second'],
    ];

    /**
     * 添加日期或者时间查询规则
     * @access public
     * @param array $rule 时间表达式
     * @return $this
     */
    public function timeRule(array $rule)
    {
        $this->timeRule = array_merge($this->timeRule, $rule);
        return $this;
    }

    /**
     * 查询日期或者时间
     * @access public
     * @param string       $field 日期字段名
     * @param string       $op    比较运算符或者表达式
     * @param string|array $range 比较范围
     * @param string       $logic AND OR
     * @return $this
     */
    public function whereTime(string $field, string $op, $range = null, string $logic = 'AND')
    {
        if (is_null($range)) {
            if (isset($this->timeRule[$op])) {
                $range = $this->timeRule[$op];
            } else {
                $range = $op;
            }
            $op = is_array($range) ? 'between' : '>=';
        }

        return $this->parseWhereExp($logic, $field, strtolower($op) . ' time', $range, [], true);
    }

    /**
     * 查询某个时间间隔数据
     * @access public
     * @param string $field    日期字段名
     * @param string $start    开始时间
     * @param string $interval 时间间隔单位 day/month/year/week/hour/minute/second
     * @param int    $step     间隔
     * @param string $logic    AND OR
     * @return $this
     */
    public function whereTimeInterval(string $field, string $start, string $interval = 'day', int $step = 1, string $logic = 'AND')
    {
        $startTime = strtotime($start);
        $endTime   = strtotime(($step > 0 ? '+' : '-') . abs($step) . ' ' . $interval . (abs($step) > 1 ? 's' : ''), $startTime);

        return $this->whereTime($field, 'between', $step > 0 ? [$startTime, $endTime - 1] : [$endTime, $startTime - 1], $logic);
    }

    /**
     * 查询月数据 whereMonth('time_field', '2018-1')
     * @access public
     * @param string $field 日期字段名
     * @param string $month 月份信息
     * @param int    $step  间隔
     * @param string $logic AND OR
     * @return $this
     */
    public function whereMonth(string $field, string $month = 'this month', int $step = 1, string $logic = 'AND')
    {
        if (in_array($month, ['this month', 'last month'])) {
            $month = date('Y-m', strtotime($month));
        }

        return $this->whereTimeInterval($field, $month, 'month', $step, $logic);
    }

    /**
     * 查询周数据 whereWeek('time_field', '2018-1-1') 从2018-1-1开始的一周数据
     * @access public
     * @param string $field 日期字段名
     * @param string $week  周信息
     * @param int    $step  间隔
     * @param string $logic AND OR
     * @return $this
     */
    public function whereWeek(string $field, string $week = 'this week', int $step = 1, string $logic = 'AND')
    {
        if (in_array($week, ['this week', 'last week'])) {
            $week = date('Y-m-d', strtotime($week));
        }

        return $this->whereTimeInterval($field, $week, 'week', $step, $logic);
    }

    /**
     * 查询年数据 whereYear('time_field', '2018')
     * @access public
     * @param string $field 日期字段名
     * @param string $year  年份信息
     * @param int    $step     间隔
     * @param string $logic AND OR
     * @return $this
     */
    public function whereYear(string $field, string $year = 'this year', int $step = 1, string $logic = 'AND')
    {
        if (in_array($year, ['this year', 'last year'])) {
            $year = date('Y', strtotime($year));
        }

        return $this->whereTimeInterval($field, $year . '-1-1', 'year', $step, $logic);
    }

    /**
     * 查询日数据 whereDay('time_field', '2018-1-1')
     * @access public
     * @param string $field 日期字段名
     * @param string $day   日期信息
     * @param int    $step     间隔
     * @param string $logic AND OR
     * @return $this
     */
    public function whereDay(string $field, string $day = 'today', int $step = 1, string $logic = 'AND')
    {
        if (in_array($day, ['today', 'yesterday'])) {
            $day = date('Y-m-d', strtotime($day));
        }

        return $this->whereTimeInterval($field, $day, 'day', $step, $logic);
    }

    /**
     * 查询日期或者时间范围 whereBetweenTime('time_field', '2018-1-1','2018-1-15')
     * @access public
     * @param string     $field     日期字段名
     * @param string|int $startTime 开始时间
     * @param string|int $endTime   结束时间
     * @param string     $logic     AND OR
     * @return $this
     */
    public function whereBetweenTime(string $field, $startTime, $endTime, string $logic = 'AND')
    {
        return $this->whereTime($field, 'between', [$startTime, $endTime], $logic);
    }

    /**
     * 查询日期或者时间范围 whereNotBetweenTime('time_field', '2018-1-1','2018-1-15')
     * @access public
     * @param string     $field     日期字段名
     * @param string|int $startTime 开始时间
     * @param string|int $endTime   结束时间
     * @return $this
     */
    public function whereNotBetweenTime(string $field, $startTime, $endTime)
    {
        return $this->whereTime($field, '<', $startTime)
            ->whereTime($field, '>', $endTime);
    }

    /**
     * 查询当前时间在两个时间字段范围 whereBetweenTimeField('start_time', 'end_time')
     * @access public
     * @param string $startField 开始时间字段
     * @param string $endField   结束时间字段
     * @return $this
     */
    public function whereBetweenTimeField(string $startField, string $endField)
    {
        return $this->whereTime($startField, '<=', time())
            ->whereTime($endField, '>=', time());
    }

    /**
     * 查询当前时间不在两个时间字段范围 whereNotBetweenTimeField('start_time', 'end_time')
     * @access public
     * @param string $startField 开始时间字段
     * @param string $endField   结束时间字段
     * @return $this
     */
    public function whereNotBetweenTimeField(string $startField, string $endField)
    {
        return $this->whereTime($startField, '>', time())
            ->whereTime($endField, '<', time(), 'OR');
    }

}
