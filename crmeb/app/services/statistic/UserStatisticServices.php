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

namespace app\services\statistic;

use app\services\BaseServices;
use app\services\other\export\ExportServices;
use app\services\order\OtherOrderServices;
use app\services\order\StoreOrderServices;
use app\services\user\UserRechargeServices;
use app\services\user\UserServices;
use app\services\user\UserVisitServices;
use app\services\user\UserWechatuserServices;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\AdminException;

/**
 * Class UserStatisticServices
 * @package app\services\statistic
 */
class UserStatisticServices extends BaseServices
{
    /**
     * 基本概况
     * @param $where
     * @return mixed
     */
    public function getBasic($where)
    {
        $time = explode('-', $where['time']);
        if (count($time) != 2) throw new AdminException(100100);
        /** @var UserVisitServices $userVisit */
        $userVisit = app()->make(UserVisitServices::class);
        /** @var UserServices $user */
        $user = app()->make(UserServices::class);
        /** @var StoreOrderServices $order */
        $order = app()->make(StoreOrderServices::class);
        /** @var UserRechargeServices $recharge */
        $recharge = app()->make(UserRechargeServices::class);
        /** @var OtherOrderServices $otherOrder */
        $otherOrder = app()->make(OtherOrderServices::class);

        $toEndtime = implode('-', [0, $time[1]]);
        $cumulativeUserWhere = ['time' => $toEndtime, 'user_type' => $where['channel_type']];
        $cumulativeRechargePeopleWhere = ['time' => $toEndtime, 'timeKey' => 'pay_time', 'channel_type' => $where['channel_type']];
        $cumulativePayPeopleWhere = ['time' => $toEndtime, 'timeKey' => 'pay_time', 'paid' => 1, 'channel_type' => $where['channel_type']];

        $now['people'] = $userVisit->getDistinctCount($where, 'uid');//访客数
        $now['browse'] = $userVisit->count($where);//访问量
        $now['newUser'] = $user->count($where + ['user_type' => $where['channel_type']]);//新增用户数
        $now['payPeople'] = $order->getDistinctCount($where + ['paid' => 1], 'uid');//成交用户数
        $now['payPercent'] = bcmul((string)($now['people'] > 0 ? bcdiv($now['payPeople'], $now['people'], 4) : 0), '100', 2);//访问-付款转化率
        $now['payUser'] = $otherOrder->getDistinctCount($where, 'uid');//激活付费会员数
        $now['rechargePeople'] = $recharge->getDistinctCount($where + ['timeKey' => 'pay_time'], 'uid');//充值用户数
        $totalPayPrice = $order->sum($where + ['paid' => 1], 'pay_price', true);
        $now['payPrice'] = floatval($now['payPeople'] > 0 ? bcdiv($totalPayPrice, $now['payPeople'], 2) : 0);//客单价
        $now['cumulativeUser'] = $user->count($cumulativeUserWhere);//累计用户数
        $now['cumulativePayUser'] = count($otherOrder->getPayUserCount(strtotime($time[1]), $where['channel_type']));//到截至日期有付费会员状态的会员数
        $now['cumulativeRechargePeople'] = $recharge->getDistinctCount($cumulativeRechargePeopleWhere, 'uid');//累计充值用户数
        $now['cumulativePayPeople'] = $order->getDistinctCount($cumulativePayPeopleWhere, 'uid');//累计成交用户数


        $dayNum = (strtotime($time[1]) - strtotime($time[0])) / 86400 + 1;
        $lastTime = [
            date("Y/m/d", strtotime("-$dayNum days", strtotime($time[0]))),
            date("Y/m/d", strtotime("-1 days", strtotime($time[0])))
        ];
        $where['time'] = implode('-', $lastTime);
        $toEndtime = implode('-', [0, $lastTime[1]]);
        $last['people'] = $userVisit->getDistinctCount($where, 'uid');//访客数
        $last['browse'] = $userVisit->count($where);//访问量
        $last['newUser'] = $user->count($where + ['user_type' => $where['channel_type']]);//新增用户数
        $last['payPeople'] = $order->getDistinctCount($where + ['paid' => 1], 'uid');//成交用户数
        $last['payPercent'] = bcmul((string)($last['people'] > 0 ? bcdiv($last['payPeople'], $last['people'], 4) : 0), '100', 2);//访问-付款转化率
        $last['payUser'] = $otherOrder->getDistinctCount($where, 'uid');//激活付费会员数
        $last['rechargePeople'] = $recharge->getDistinctCount($where + ['timeKey' => 'pay_time'], 'uid');//充值用户数
        $totalPayPrice = $order->sum($where + ['paid' => 1], 'pay_price', true);
        $last['payPrice'] = floatval($last['payPeople'] > 0 ? bcdiv($totalPayPrice, $last['payPeople'], 2) : 0);//客单价
        $cumulativeUserWhere['time'] = $toEndtime;
        $last['cumulativeUser'] = $user->count($cumulativeUserWhere);//累计用户数
        $last['cumulativePayUser'] = count($otherOrder->getPayUserCount(strtotime($lastTime[1]) + 86400, $where['channel_type']));//到截至日期有付费会员状态的会员数
        $cumulativeRechargePeopleWhere['time'] = $toEndtime;
        $last['cumulativeRechargePeople'] = $recharge->getDistinctCount($cumulativeRechargePeopleWhere, 'uid');//累计充值用户数
        $cumulativePayPeopleWhere['time'] = $toEndtime;
        $last['cumulativePayPeople'] = $order->getDistinctCount($cumulativePayPeopleWhere, 'uid');//累计成交用户数

        //组合数据，计算环比
        $data = [];
        foreach ($now as $key => $item) {
            $data[$key]['num'] = $item;
            $data[$key]['last_num'] = $last[$key];
            $num = $last[$key] > 0 ? $last[$key] : 1;
            $data[$key]['percent'] = bcmul((string)bcdiv((string)($item - $last[$key]), (string)$num, 4), 100, 2);
        }
        return $data;
    }

    /**
     * 用户趋势
     * @param $where
     * @param $excel
     * @return mixed
     */
    public function getTrend($where, $excel = false)
    {
        $time = explode('-', $where['time']);
        $channelType = $where['channel_type'];
        if (count($time) != 2) throw new AdminException(100100);
        $dayCount = (strtotime($time[1]) - strtotime($time[0])) / 86400 + 1;
        $data = [];
        if ($dayCount == 1) {
            $data = $this->trend($time, $channelType, 0, $excel);
        } elseif ($dayCount > 1 && $dayCount <= 31) {
            $data = $this->trend($time, $channelType, 1, $excel);
        } elseif ($dayCount > 31 && $dayCount <= 92) {
            $data = $this->trend($time, $channelType, 3, $excel);
        } elseif ($dayCount > 92) {
            $data = $this->trend($time, $channelType, 30, $excel);
        }
        return $data;
    }

    /**
     * 用户趋势
     * @param $time
     * @param $channelType
     * @param $num
     * @param $excel
     * @return array
     */
    public function trend($time, $channelType, $num, $excel)
    {
        /** @var UserServices $user */
        $user = app()->make(UserServices::class);
        /** @var UserVisitServices $userVisit */
        $userVisit = app()->make(UserVisitServices::class);
        /** @var StoreOrderServices $order */
        $order = app()->make(StoreOrderServices::class);
        /** @var UserRechargeServices $recharge */
        $recharge = app()->make(UserRechargeServices::class);
        /** @var OtherOrderServices $otherOrder */
        $otherOrder = app()->make(OtherOrderServices::class);

        $newPeople = $visitPeople = $paidPeople = $rechargePeople = $vipPeople = [];
        $newPeople['name'] = '新增用户数';
        $visitPeople['name'] = '访客数';
        $paidPeople['name'] = '成交用户数';
        $rechargePeople['name'] = '充值用户';
        $vipPeople['name'] = '新增付费用户数';
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
        $visitPeople = array_column($userVisit->getTrendData($time, $channelType, $timeType, 'count(distinct(uid))'), 'num', 'days');
        $newPeople = array_column($user->getTrendData($time, $channelType, $timeType), 'num', 'days');
        $paidPeople = array_column($order->getTrendData($time, $channelType, $timeType, 'count(distinct(uid))'), 'num', 'days');
        $rechargePeople = array_column($recharge->getTrendData($time, $channelType, $timeType), 'num', 'days');
        $vipPeople = array_column($otherOrder->getTrendData($time, $channelType, $timeType), 'num', 'days');
        if ($excel) {
            $data = [];
            $browsePeople = array_column($userVisit->getTrendData($time, $channelType, $timeType, 'count(id)'), 'num', 'days');
            $paidPrice = array_column($order->getTrendData($time, $channelType, $timeType, 'sum(pay_price)'), 'num', 'days');
            foreach ($xAxis as &$item) {
                if (isset($paidPeople[$item]) && isset($visitPeople[$item])) {
                    $changes = bcmul(bcdiv((string)$paidPeople[$item], (string)$visitPeople[$item], 4), 100, 2);
                    $changes = $changes > 100 ? 100 : $changes;
                } else {
                    $changes = 0;
                }
                $data[] = [
                    'time' => $item,
                    'user' => $visitPeople[$item] ?? 0,
                    'browse' => $browsePeople[$item] ?? 0,
                    'new' => $newPeople[$item] ?? 0,
                    'paid' => $paidPeople[$item] ?? 0,
                    'changes' => $changes,
                    'vip' => $vipPeople[$item] ?? 0,
                    'recharge' => $rechargePeople[$item] ?? 0,
                    'payPrice' => isset($paidPrice[$item]) && isset($paidPeople[$item]) ? bcdiv((string)$paidPrice[$item], (string)$paidPeople[$item], 2) : 0,
//                    'cumulativeUser' => $user->getCount($userWhere),
//                    'cumulativeVip' => $otherOrder->getPayUserCount($endTime, $channelType),
//                    'cumulativeRecharge' => $recharge->getCount($rechargeWhere),
//                    'cumulativePaid' => $order->getCount($payWhere),
                ];
            }
            /** @var ExportServices $exportService */
            $exportService = app()->make(ExportServices::class);
            $url = $exportService->userTrade($data);
            return compact('url');
        } else {
            $data = $series = [];
            foreach ($xAxis as $item) {
                $data['新增用户数'][] = isset($newPeople[$item]) ? intval($newPeople[$item]) : 0;
                $data['访客数'][] = isset($visitPeople[$item]) ? intval($visitPeople[$item]) : 0;
                $data['成交用户数'][] = isset($paidPeople[$item]) ? intval($paidPeople[$item]) : 0;
                $data['充值用户'][] = isset($rechargePeople[$item]) ? intval($rechargePeople[$item]) : 0;
                $data['新增付费用户数'][] = isset($vipPeople[$item]) ? intval($vipPeople[$item]) : 0;
            }
            foreach ($data as $key => $item) {
                $series[] = ['name' => $key, 'value' => $item];
            }
            return compact('xAxis', 'series');
        }
    }

    /**
     * 微信用户信息
     * @param $where
     * @return array
     */
    public function getWechat($where)
    {
        $time = explode('-', $where['time']);
        if (count($time) != 2) throw new AdminException(100100);
        /** @var WechatUserServices $user */
        $user = app()->make(WechatUserServices::class);

        $now['subscribe'] = $user->getCount([
            ['subscribe', '=', 1],
            ['user_type', '=', 'wechat'],
            ['subscribe_time', '>=', strtotime($time[0])],
            ['subscribe_time', '<', strtotime($time[1])]
        ]);
        $now['unSubscribe'] = $user->getCount([
            ['subscribe', '=', 0],
            ['user_type', '=', 'wechat'],
            ['subscribe_time', '<>', ''],
            ['subscribe_time', '>=', strtotime($time[0])],
            ['subscribe_time', '<', strtotime($time[1])]
        ]);
        $now['increaseSubscribe'] = $now['subscribe'] - $now['unSubscribe'];
        $now['cumulativeSubscribe'] = $user->getCount([['subscribe', '=', 1], ['user_type', '=', 'wechat']]);
        $now['cumulativeUnSubscribe'] = $user->getCount([
            ['subscribe', '=', 0],
            ['user_type', '=', 'wechat'],
            ['subscribe_time', '<>', '']
        ]);

        $dayNum = (strtotime($time[1]) - strtotime($time[0])) / 86400 + 1;
        $lastTime = array(
            date("Y/m/d", strtotime("-$dayNum days", strtotime($time[0]))),
            date("Y/m/d", strtotime("-1 days", strtotime($time[0])))
        );
        $last['subscribe'] = $user->getCount([
            ['subscribe', '=', 1],
            ['user_type', '=', 'wechat'],
            ['subscribe_time', '>=', strtotime($lastTime[0])],
            ['subscribe_time', '<', strtotime($lastTime[1])]
        ]);
        $last['unSubscribe'] = $user->getCount([
            ['subscribe', '=', 0],
            ['user_type', '=', 'wechat'],
            ['subscribe_time', '<>', ''],
            ['subscribe_time', '>=', strtotime($lastTime[0])],
            ['subscribe_time', '<', strtotime($lastTime[1])]
        ]);
        $last['increaseSubscribe'] = $last['subscribe'] - $last['unSubscribe'];
        $last['cumulativeSubscribe'] = $user->getCount([['subscribe', '=', 1], ['user_type', '=', 'wechat']]);
        $last['cumulativeUnSubscribe'] = $user->getCount([
            ['subscribe', '=', 0],
            ['user_type', '=', 'wechat'],
            ['subscribe_time', '<>', '']
        ]);

        //组合数据，计算环比
        $data = [];
        foreach ($now as $key => $item) {
            $data[$key]['num'] = $item;
            $num = $last[$key] ?: 1;
            $data[$key]['percent'] = bcmul(bcdiv(($item - $last[$key]), $num, 4), 100, 2);
        }
        return $data;
    }

    /**
     * 微信用户趋势
     * @param $where
     * @return array
     */
    public function getWechatTrend($where)
    {
        $time = explode('-', $where['time']);
        $channelType = $where['channel_type'];
        if (count($time) != 2) throw new AdminException(100100);
        $dayCount = (strtotime($time[1]) - strtotime($time[0])) / 86400 + 1;
        $data = [];
        if ($dayCount == 1) {
            $data = $this->wechatTrend($time, 0);
        } elseif ($dayCount > 1 && $dayCount <= 31) {
            $data = $this->wechatTrend($time, 1);
        } elseif ($dayCount > 31 && $dayCount <= 92) {
            $data = $this->wechatTrend($time, 3);
        } elseif ($dayCount > 92) {
            $data = $this->wechatTrend($time, 30);
        }
        return $data;
    }

    /**
     * 微信用户趋势
     * @param $time
     * @param $num
     * @return array
     */
    public function wechatTrend($time, $num)
    {
        /** @var WechatUserServices $user */
        $user = app()->make(WechatUserServices::class);

        $subscribe = $unSubscribe = $increaseSubscribe = $cumulativeSubscribe = $cumulativeUnSubscribe = [];
        if ($num == 0) {
            $xAxis = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
            $subscribe = array_column($user->getWechatTrendData($time, [
                ['subscribe', '=', 1],
                ['subscribe_time', '>=', strtotime($time[0])],
                ['subscribe_time', '<', strtotime($time[1])]
            ], '%H', 'subscribe'), 'subscribe', 'days');
            $unSubscribe = array_column($user->getWechatTrendData($time, [
                ['subscribe', '=', 0],
                ['subscribe_time', '<>', ''],
                ['subscribe_time', '>=', strtotime($time[0])],
                ['subscribe_time', '<', strtotime($time[1])]
            ], '%H', 'unSubscribe'), 'unSubscribe', 'days');
            $cumulativeSubscribe = array_column($user->getWechatTrendData($time, [
                ['subscribe', '=', 1]
            ], '%H', 'cumulativeSubscribe'), 'cumulativeSubscribe', 'days');
            $cumulativeUnSubscribe = array_column($user->getWechatTrendData($time, [
                ['subscribe', '=', 0], ['subscribe_time', '<>', '']
            ], '%H', 'cumulativeUnSubscribe'), 'cumulativeUnSubscribe', 'days');
        } elseif ($num != 0) {
            $dt_start = strtotime($time[0]);
            $dt_end = strtotime($time[1]);
            $timeType = '%m-%d';
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
            $subscribe = array_column($user->getWechatTrendData($time, [
                ['subscribe', '=', 1],
                ['subscribe_time', '>=', strtotime($time[0])],
                ['subscribe_time', '<', strtotime($time[1])]
            ], $timeType, 'subscribe'), 'subscribe', 'days');
            $unSubscribe = array_column($user->getWechatTrendData($time, [
                ['subscribe', '=', 0],
                ['subscribe_time', '<>', ''],
                ['subscribe_time', '>=', strtotime($time[0])],
                ['subscribe_time', '<', strtotime($time[1])]
            ], $timeType, 'unSubscribe'), 'unSubscribe', 'days');
            $cumulativeSubscribe = array_column($user->getWechatTrendData($time, [
                ['subscribe', '=', 1]
            ], $timeType, 'cumulativeSubscribe'), 'cumulativeSubscribe', 'days');
            $cumulativeUnSubscribe = array_column($user->getWechatTrendData($time, [
                ['subscribe', '=', 0], ['subscribe_time', '<>', '']
            ], $timeType, 'cumulativeUnSubscribe'), 'cumulativeUnSubscribe', 'days');
        }
        $data = $series = [];
        foreach ($xAxis as $item) {
            $data['新增关注用户'][] = $subscribe[$item] ?? 0;
            $data['新增取关用户'][] = $unSubscribe[$item] ?? 0;
            $data['累计关注用户'][] = $cumulativeSubscribe[$item] ?? 0;
            $data['累计取关用户'][] = $cumulativeUnSubscribe[$item] ?? 0;
        }
        foreach ($data['新增关注用户'] as $keys => $items) {
            $data['净增用户数'][] = $data['新增关注用户'][$keys] - $data['新增取关用户'][$keys];
        }
        foreach ($data as $key => $item) {
            $series[] = ['name' => $key, 'value' => $item];
        }
        return compact('xAxis', 'series');
    }

    /**
     * 用户地域图表
     * @param $where
     * @return array
     */
    public function getRegion($where)
    {
        $time = explode('-', $where['time']);
        $channelType = $where['channel_type'];
        if (count($time) != 2) throw new AdminException(100100);

        /** @var UserVisitServices $userVisit */
        $userVisit = app()->make(UserVisitServices::class);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        /** @var StoreOrderServices $order */
        $order = app()->make(StoreOrderServices::class);
        /** @var WechatUserServices $user */
        $wechatUser = app()->make(WechatUserServices::class);

        $all = $wechatUser->getRegionAll($time, $channelType);
        $new = $wechatUser->getRegionNew($time, $channelType);
        $visit = $userVisit->getRegion($time, $channelType);
        $payPrice = $order->getRegion($time, $channelType);
        foreach ($all as $key1 => $item1) {
            foreach ($new as $key2 => $item2) {
                if ($item1['province'] == $item2['province']) {
                    $all[$key1]['newNum'] = $item2['newNum'];
                    unset($new[$key2]);
                }
            }
        }
        $all = array_merge($all, $new);
        foreach ($all as $key1 => $item1) {
            foreach ($visit as $key3 => $item3) {
                if ($item1['province'] == $item3['province']) {
                    $all[$key1]['visitNum'] = $item3['visitNum'];
                    unset($visit[$key3]);
                }
            }
        }
        $all = array_merge($all, $visit);
        foreach ($all as $key1 => $item1) {
            foreach ($payPrice as $key3 => $item3) {
                if ($item1['province'] == $item3['province']) {
                    $all[$key1]['payPrice'] = $item3['payPrice'];
                    unset($payPrice[$key3]);
                }
            }
        }
        $all = array_merge($all, $payPrice);
        foreach ($all as &$item) {
            if ($item['province'] == '') $item['province'] = '未知';
            if (!isset($item['allNum'])) $item['allNum'] = 0;
            if (!isset($item['newNum'])) $item['newNum'] = 0;
            if (!isset($item['visitNum'])) $item['visitNum'] = 0;
            if (!isset($item['payPrice'])) {
                $item['payPrice'] = 0;
            } else {
                $item['payPrice'] = floatval($item['payPrice']);
            }
        }
        $data = array_values($all);
        $last_names = array_column($data, $where['sort']);
        array_multisort($last_names, SORT_DESC, $data);
        return $data;
    }

    /**
     * 用户性别
     * @param $where
     * @return mixed
     */
    public function getSex($where)
    {
        $time = explode('-', $where['time']);
        $channelType = $where['channel_type'];
        if (count($time) != 2) throw new AdminException(100100);

        /** @var UserWechatuserServices $user */
        $wechatUser = app()->make(UserWechatuserServices::class);

        $data = $wechatUser->getSex($time, $channelType);
        $oneData = [
            ['value' => 0, 'name' => '未知', 'name_key' => 0],
            ['value' => 0, 'name' => '男', 'name_key' => 1],
            ['value' => 0, 'name' => '女', 'name_key' => 2],
        ];
        foreach ($oneData as &$value) {
            foreach ($data as $item) {
                if ($item['name'] == $value['name_key']) {
                    $value['value'] = $item['value'];
                    break;
                }
            }
        }
        return $oneData;
    }
}
