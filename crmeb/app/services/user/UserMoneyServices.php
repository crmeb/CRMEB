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

namespace app\services\user;

use app\dao\user\UserMoneyDao;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use crmeb\exceptions\AdminException;

class UserMoneyServices extends BaseServices
{
    /**
     * 用户记录模板
     * @var array[]
     */
    protected $incomeData = [
        'pay_product' => [
            'title' => '余额支付购买商品',
            'type' => 'pay_product',
            'mark' => '余额支付{%num%}元购买商品',
            'status' => 1,
            'pm' => 0
        ],
        'pay_product_refund' => [
            'title' => '商品退款',
            'type' => 'pay_product_refund',
            'mark' => '订单退款到余额{%num%}元',
            'status' => 1,
            'pm' => 1
        ],
        'system_add' => [
            'title' => '系统增加余额',
            'type' => 'system_add',
            'mark' => '系统增加{%num%}余额',
            'status' => 1,
            'pm' => 1
        ],
        'system_sub' => [
            'title' => '系统减少余额',
            'type' => 'system_sub',
            'mark' => '系统扣除{%num%}余额',
            'status' => 1,
            'pm' => 0
        ],
        'user_recharge' => [
            'title' => '用户充值余额',
            'type' => 'recharge',
            'mark' => '成功充值余额{%price%}元,赠送{%give_price%}元',
            'status' => 1,
            'pm' => 1
        ],
        'user_recharge_refund' => [
            'title' => '用户充值退款',
            'type' => 'recharge_refund',
            'mark' => '退款扣除余额{%num%}元',
            'status' => 1,
            'pm' => 0
        ],
        'brokerage_to_nowMoney' => [
            'title' => '佣金提现到余额',
            'type' => 'extract',
            'mark' => '佣金提现到余额{%num%}元',
            'status' => 1,
            'pm' => 1
        ],
        'lottery_use_money' => [
            'title' => '参与抽奖使用余额',
            'type' => 'lottery_use',
            'mark' => '参与抽奖使用{%num%}余额',
            'status' => 1,
            'pm' => 0
        ],
        'lottery_give_money' => [
            'title' => '抽奖中奖赠送余额',
            'type' => 'lottery_add',
            'mark' => '抽奖中奖赠送{%num%}余额',
            'status' => 1,
            'pm' => 1
        ],
        'register_system_add' => [
            'title' => '新用户注册赠送余额',
            'type' => 'register_system_add',
            'mark' => '新用户注册赠送{%num%}余额',
            'status' => 1,
            'pm' => 1
        ],
    ];

    /**
     * UserMoneyServices constructor.
     * @param UserMoneyDao $dao
     */
    public function __construct(UserMoneyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 写入用户记录
     * @param string $type 写入类型
     * @param int $uid
     * @param int|string|array $number
     * @param int|string $balance
     * @param $linkId
     * @return bool|mixed
     */
    public function income(string $type, int $uid, $number, $balance, $linkId)
    {
        $data = $this->incomeData[$type] ?? null;
        if (!$data) {
            return true;
        }
        $data['uid'] = $uid;
        $data['balance'] = $balance ?? 0;
        $data['link_id'] = $linkId;
        if (is_array($number)) {
            $key = array_keys($number);
            $key = array_map(function ($item) {
                return '{%' . $item . '%}';
            }, $key);
            $value = array_values($number);
            $data['number'] = $number['number'] ?? 0;
            $data['mark'] = str_replace($key, $value, $data['mark']);
        } else {
            $data['number'] = $number;
            $data['mark'] = str_replace(['{%num%}'], $number, $data['mark']);
        }
        $data['add_time'] = time();

        return $this->dao->save($data);
    }

    /**
     * 余额记录
     * @param $where
     * @return array
     */
    public function balanceList($where)
    {
        $status = [];
        foreach ($this->incomeData as $value) {
            $status[$value['type']] = $value['title'];
        }
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        //关联用户
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $uids = array_column($list, 'uid');
        $nicknameArr = $userServices->getColumn([['uid', 'in', $uids]], 'nickname', 'uid');
        //关联订单
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        /** @var UserRechargeServices $rechargeServices */
        $rechargeServices = app()->make(UserRechargeServices::class);
        foreach ($list as &$item) {
            $item['nickname'] = $nicknameArr[$item['uid']];
            if ($item['type'] == 'pay_product' || $item['type'] == 'pay_product_refund') {
                $item['relation'] = $orderServices->value(['id' => $item['link_id']], 'order_id');
            } elseif ($item['type'] == 'recharge' || $item['type'] == 'recharge_refund') {
                $item['relation'] = $rechargeServices->value(['id' => $item['link_id']], 'order_id');
            } else {
                $item['relation'] = $status[$item['type']];
            }
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['type_name'] = $status[$item['type']];
        }
        $count = $this->dao->count($where);
        return compact('list', 'count', 'status');
    }

    /**
     * 余额记录备注
     * @param $data
     * @return bool
     */
    public function recordRemark($id, $mark)
    {
        if ($this->dao->update($id, ['mark' => $mark])) {
            return true;
        } else {
            throw new AdminException(100025);
        }
    }

    /**
     * 余额统计基础
     * @param $where
     * @return array
     */
    public function getBasic($where)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $data['now_balance'] = $userServices->sum(['status' => 1], 'now_money', true);
        $data['add_balance'] = $this->dao->sum(['pm' => 1], 'number', true);
        $data['sub_balance'] = $this->dao->sum(['pm' => 0], 'number', true);
        return $data;
    }

    /**
     * 余额趋势
     * @param $where
     * @return array
     */
    public function getTrend($where)
    {
        $time = explode('-', $where['time']);
        if (count($time) != 2) throw new AdminException(100100);
        $dayCount = (strtotime($time[1]) - strtotime($time[0])) / 86400 + 1;
        $data = [];
        if ($dayCount == 1) {
            $data = $this->trend($time, 0);
        } elseif ($dayCount > 1 && $dayCount <= 31) {
            $data = $this->trend($time, 1);
        } elseif ($dayCount > 31 && $dayCount <= 92) {
            $data = $this->trend($time, 3);
        } elseif ($dayCount > 92) {
            $data = $this->trend($time, 30);
        }
        return $data;
    }

    /**
     * 余额趋势
     * @param $time
     * @param $num
     * @param false $excel
     * @return array
     */
    public function trend($time, $num, $excel = false)
    {
        if ($num == 0) {
            $xAxis = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
            $timeType = '%H';
        } elseif ($num != 0) {
            $dt_start = strtotime($time[0]);
            $dt_end = strtotime($time[1]);
            while ($dt_start <= $dt_end) {
                if ($num == 30) {
                    $xAxis[] = date('Y-m', $dt_start);
                    $dt_start = strtotime("+1 month", $dt_start);
                    $timeType = '%Y-%m';
                } else {
                    $xAxis[] = date('m-d', $dt_start);
                    $dt_start = strtotime("+$num day", $dt_start);
                    $timeType = '%m-%d';
                }
            }
        }
        $time[1] = date("Y-m-d", strtotime("+1 day", strtotime($time[1])));
        $point_add = array_column($this->dao->getBalanceTrend($time, $timeType, 'add_time', 'sum(number)', 'add'), 'num', 'days');
        $point_sub = array_column($this->dao->getBalanceTrend($time, $timeType, 'add_time', 'sum(number)', 'sub'), 'num', 'days');
        $data = $series = [];
        foreach ($xAxis as $item) {
            $data['余额积累'][] = isset($point_add[$item]) ? floatval($point_add[$item]) : 0;
            $data['余额消耗'][] = isset($point_sub[$item]) ? floatval($point_sub[$item]) : 0;
        }
        foreach ($data as $key => $item) {
            $series[] = [
                'name' => $key,
                'data' => $item,
                'type' => 'line',
            ];
        }
        return compact('xAxis', 'series');
    }

    /**
     * 余额来源
     * @param $where
     * @return array
     */
    public function getChannel($where)
    {
        $bing_xdata = ['系统增加', '用户充值', '佣金提现', '抽奖赠送', '商品退款'];
        $color = ['#64a1f4', '#3edeb5', '#70869f', '#ffc653', '#fc7d6a'];
        $data = ['system_add', 'recharge', 'extract', 'lottery_add', 'pay_product_refund'];
        $bing_data = [];
        foreach ($data as $key => $item) {
            $bing_data[] = [
                'name' => $bing_xdata[$key],
                'value' => $this->dao->sum(['pm' => 1, 'type' => $item, 'time' => $where['time']], 'number', true),
                'itemStyle' => ['color' => $color[$key]]
            ];
        }

        $list = [];
        $count = array_sum(array_column($bing_data, 'value'));
        foreach ($bing_data as $key => $item) {
            $list[] = [
                'name' => $item['name'],
                'value' => $item['value'],
                'percent' => $count != 0 ? bcmul((string)bcdiv((string)$item['value'], (string)$count, 4), '100', 2) : 0,
            ];
        }
        array_multisort(array_column($list, 'value'), SORT_DESC, $list);
        return compact('bing_xdata', 'bing_data', 'list');
    }

    /**
     * 余额类型
     * @param $where
     * @return array
     */
    public function getType($where)
    {
        $bing_xdata = ['系统减少', '充值退款', '购买商品'];
        $color = ['#64a1f4', '#3edeb5', '#70869f'];
        $data = ['system_sub', 'recharge_refund', 'pay_product'];
        $bing_data = [];
        foreach ($data as $key => $item) {
            $bing_data[] = [
                'name' => $bing_xdata[$key],
                'value' => $this->dao->sum(['pm' => 0, 'type' => $item, 'time' => $where['time']], 'number', true),
                'itemStyle' => ['color' => $color[$key]]
            ];
        }

        $list = [];
        $count = array_sum(array_column($bing_data, 'value'));
        foreach ($bing_data as $key => $item) {
            $list[] = [
                'name' => $item['name'],
                'value' => $item['value'],
                'percent' => $count != 0 ? bcmul((string)bcdiv((string)$item['value'], (string)$count, 4), '100', 2) : 0,
            ];
        }
        array_multisort(array_column($list, 'value'), SORT_DESC, $list);
        return compact('bing_xdata', 'bing_data', 'list');
    }

    public function getMoneyList($uid, $type)
    {
        $where = [];
        $where['uid'] = $uid;
        [$page, $limit] = $this->getPageValue();
        if ($type == 1) {
            $where['pm'] = 0;
        } elseif ($type == 2) {
            $where['pm'] = 1;
            $where['not_type'] = ['pay_product_refund'];
        }
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        $times = [];
        if ($list) {
            foreach ($list as &$item) {
                $item['time'] = $item['time_key'] = $item['add_time'] ? date('Y-m', (int)$item['add_time']) : '';
                $item['add_time'] = $item['add_time'] ? date('Y-m-d H:i', (int)$item['add_time']) : '';
            }
            $times = array_merge(array_unique(array_column($list, 'time_key')));
        }
        return ['list' => $list, 'time' => $times, 'count' => $count];
    }

    /**
     * 根据查询用户充值金额
     * @param array $where
     * @param string $rechargeSumField
     * @param string $selectType
     * @param string $group
     * @return float|mixed
     */
    public function getRechargeMoneyByWhere(array $where, string $rechargeSumField, string $selectType, string $group = "")
    {
        switch ($selectType) {
            case "sum" :
                return $this->dao->getWhereSumField($where, $rechargeSumField);
            case "group" :
                return $this->dao->getGroupField($where, $rechargeSumField, $group);
        }
    }
}
