<?php

namespace app\services\activity\integral;

use app\dao\user\UserBillDao;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;

class StorePointRecordServices extends BaseServices
{
    /**
     * UserBillServices constructor.
     * @param UserBillDao $dao
     */
    public function __construct(UserBillDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 积分记录
     * @param $where
     * @return array
     */
    public function pointRecord($where)
    {
        $where['category'] = 'integral';
        $status = [
            'invite_user' => '邀新奖励',
            'system_add' => '系统增加积分',
            'system_sub' => '系统减少积分',
            'gain' => '下单赠送积分',
            'product_gain' => '购买商品赠送积分',
            'deduction' => '下单积分抵扣',
            'lottery_use' => '参与抽奖使用积分',
            'lottery_add' => '抽奖中奖赠送积分',
            'order_deduction' => '扣除订单下单赠送积分',
            'storeIntegral_use' => '积分兑换商品',
            'pay_product_integral_back' => '返还下单使用积分',
            'sign' => '签到获得积分',
        ];
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', $page, $limit);
        //关联用户
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $uids = array_column($list, 'uid');
        $nicknameArr = $userServices->getColumn([['uid', 'in', $uids]], 'nickname', 'uid');
        //关联订单
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        /** @var StoreIntegralOrderServices $integralOrderServices */
        $integralOrderServices = app()->make(StoreIntegralOrderServices::class);
        foreach ($list as &$item) {
            $item['nickname'] = $nicknameArr[$item['uid']] ?? '未知用户';
            if ($item['type'] == 'gain' || $item['type'] == 'deduction' || $item['type'] == 'product_deduction' || $item['type'] == 'pay_product_integral_back') {
                $item['relation'] = $orderServices->value(['id' => $item['link_id']], 'order_id');
            } elseif ($item['type'] == 'storeIntegral_use') {
                $item['relation'] = $integralOrderServices->value(['id' => $item['link_id']], 'order_id');
            } else {
                $item['relation'] = $status[$item['type']];
            }
            $item['type_name'] = $status[$item['type']];
        }
        $count = $this->dao->count($where);
        return compact('list', 'count', 'status');
    }

    /**
     * 积分记录备注
     * @param $data
     * @return bool
     */
    public function recordRemark($id, $mark)
    {
        if (!$id) throw new AdminException(100100);
        if ($mark === '') throw new AdminException(400106);
        if ($this->dao->update($id, ['mark' => $mark])) {
            return true;
        } else {
            throw new AdminException(100025);
        }
    }

    /**
     * 订单统计基础
     * @param $where
     * @return array
     */
    public function getBasic($where)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $data['now_point'] = $userServices->sum(['status' => 1], 'integral', true);
        $data['all_point'] = $this->dao->sum(['category' => 'integral', 'pm' => 1, 'time' => $where['time']], 'number', true);
        $data['pay_point'] = $this->dao->sum(['category' => 'integral', 'pm' => 0, 'time' => $where['time']], 'number', true);
        return $data;
    }

    /**
     * 订单趋势
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
     * 订单趋势
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
        $point_add = array_column($this->dao->getPointTrend($time, $timeType, 'add_time', 'sum(number)', 'add'), 'num', 'days');
        $point_sub = array_column($this->dao->getPointTrend($time, $timeType, 'add_time', 'sum(number)', 'sub'), 'num', 'days');
        $data = $series = [];
        foreach ($xAxis as $item) {
            $data['积分积累'][] = isset($point_add[$item]) ? floatval($point_add[$item]) : 0;
            $data['积分消耗'][] = isset($point_sub[$item]) ? floatval($point_sub[$item]) : 0;
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
     * 订单来源
     * @param $where
     * @return array
     */
    public function getChannel($where)
    {
        $bing_xdata = ['订单赠送', '商品赠送', '后台赠送', '签到获得', '九宫格抽奖'];
        $color = ['#64a1f4', '#3edeb5', '#70869f', '#ffc653', '#fc7d6a'];
        $data = ['gain', 'product_gain', 'system_add', 'sign', 'lottery_add'];
        $bing_data = [];
        foreach ($data as $key => $item) {
            $bing_data[] = [
                'name' => $bing_xdata[$key],
                'value' => $this->dao->sum(['pm' => 1, 'type' => $item, 'category' => 'integral', 'time' => $where['time']], 'number', true),
                'itemStyle' => ['color' => $color[$key]]
            ];
        }
        $list = [];
        $count = array_sum(array_column($bing_data, 'value'));
        foreach ($bing_data as $key => $item) {
            $list[] = [
                'name' => $item['name'],
                'value' => $item['value'],
                'percent' => $count != 0 ? bcmul((string)bcdiv((string)$item['value'], (string)$count, 4), '100', 1) : 0,
            ];
        }
        array_multisort(array_column($list, 'value'), SORT_DESC, $list);
        return compact('bing_xdata', 'bing_data', 'list');
    }

    /**
     * 订单类型
     * @param $where
     * @return array
     */
    public function getType($where)
    {
        $bing_xdata = ['订单抵扣', '九宫格抽奖', '后台减少', '退款退回', '兑换商品'];
        $color = ['#64a1f4', '#3edeb5', '#70869f', '#ffc653', '#fc7d6a'];
        $data = ['deduction', 'lottery_use', 'system_sub', 'order_deduction', 'storeIntegral_use'];
        $bing_data = [];
        foreach ($data as $key => $item) {
            $bing_data[] = [
                'name' => $bing_xdata[$key],
                'value' => $this->dao->sum(['pm' => 0, 'type' => $item, 'category' => 'integral', 'time' => $where['time']], 'number', true),
                'itemStyle' => ['color' => $color[$key]]
            ];
        }

        $list = [];
        $count = array_sum(array_column($bing_data, 'value'));
        foreach ($bing_data as $key => $item) {
            $list[] = [
                'name' => $item['name'],
                'value' => $item['value'],
                'percent' => $count != 0 ? bcmul((string)bcdiv((string)$item['value'], (string)$count, 4), '100', 1) : 0,
            ];
        }
        array_multisort(array_column($list, 'value'), SORT_DESC, $list);
        return compact('bing_xdata', 'bing_data', 'list');
    }
}
