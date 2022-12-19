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
use app\services\order\StoreOrderRefundServices;
use app\services\other\export\ExportServices;
use app\services\order\OtherOrderServices;
use app\services\order\StoreOrderServices;
use app\services\user\UserExtractServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserRechargeServices;

/**
 * Class TradeStatisticServices
 * @package app\services\statistic
 */
class TradeStatisticServices extends BaseServices
{
    public $_day = ['00时', '01时', '02时', '03时', '04时', '05时', '06时', '07时', '08时', '09时', '10时', '11时', '12时', '13时', '14时', '15时', '16时', '17时', '18时', '19时', '20时', '21时', '22时', '23时', '24时'];

    /**
     * 基本概况
     * @param $where
     * @return mixed
     */
    public function getTopLeftTrade($where)
    {
        //总交易额
        $selectType = "sum";
        $tradeTotalMoney = $this->tradeTotalMoney($where, $selectType);
        //交易曲线
        $selectType = "group";
        $hourTotalMoney = $this->tradeGroupMoney($where, $selectType);
        return ['total_money' => $tradeTotalMoney, 'curve' => $hourTotalMoney];
    }

    public function getTopRightOneTrade()
    {
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        /** day订单数 */
        //今日订单数
        $orderCountWhere['is_del'] = 0;
        $orderCountWhere['paid'] = 1;
        $orderCountWhere['pid'] = 0;
        $orderCountWhere['timeKey'] = $this->TimeConvert("today");
        $todayOrderCount = $orderService->getOrderCountByWhere($orderCountWhere);

        //今日订单数曲线
        $todayHourOrderCount = $orderService->getOrderGroupCountByWhere($orderCountWhere);
        $todayHourOrderCount = $this->trendYdata($todayHourOrderCount, $orderCountWhere['timeKey']);
        //昨日订单数
        $yestodayWhere['is_del'] = 0;
        $yestodayWhere['paid'] = 1;
        $yestodayWhere['pid'] = 0;
        $yestodayWhere['timeKey'] = $this->TimeConvert("yestoday");
        $yesTodayOrderCount = $orderService->getOrderCountByWhere($yestodayWhere);
        //昨日订单曲线
        // $yestodayHourOrderCount = $orderService->getOrderGroupCountByWhere($yestodayWhere);
        // $yestodayHourOrderCount = $this->trendYdata($yestodayHourOrderCount, 'day');
        //订单数环比增长率
        $orderCountDayChain = $this->countRate($todayOrderCount, $yesTodayOrderCount);
        $data[] = [
            'name' => "今日订单数",
            'now_value' => $todayOrderCount,
            'last_value' => $yesTodayOrderCount,
            'rate' => $orderCountDayChain,
            'curve' => $todayHourOrderCount
        ];
        /** day支付人数 */
        //今日支付人数
        $orderPeopleWhere['timeKey'] = $this->TimeConvert("today");
        $orderPeopleWhere['paid'] = 1;
        $orderPeopleWhere['pid'] = 0;
        $todayPayOrderPeople = count($orderService->getPayOrderPeopleByWhere($orderPeopleWhere));
        //今日支付人数曲线
        $todayHourOrderPeople = $orderService->getPayOrderGroupPeopleByWhere($orderPeopleWhere);
        $todayHourOrderPeople = $this->trendYdata($todayHourOrderPeople, $orderPeopleWhere['timeKey']);
        //昨日支付人数
        $yestodayOrderPeopleWhere['timeKey'] = $this->TimeConvert("yestoday");
        $yestodayOrderPeopleWhere['paid'] = 1;
        $yestodayPayOrderPeople = count($orderService->getPayOrderPeopleByWhere($yestodayOrderPeopleWhere));
        //昨日支付曲线
        // $yestodayHourOrderPeople = $orderService->getPayOrderGroupPeopleByWhere($yestodayOrderPeopleWhere);
        // $yestodayHourOrderPeople = $this->trendYdata($yestodayHourOrderPeople, 'day');
        //订单支付人数环比
        $orderPeopleDayChain = $this->countRate($todayPayOrderPeople, $yestodayPayOrderPeople);
        $data[] = [
            'name' => "今日支付人数",
            'now_value' => $todayPayOrderPeople,
            'last_value' => $yestodayPayOrderPeople,
            'rate' => $orderPeopleDayChain,
            'curve' => $todayHourOrderPeople
        ];
        $new_data = [];
        foreach ($data as $k => $v) {
            $new_data['x'] = $v['curve']['x'];
            $new_data['series'][$k]['name'] = $v['name'];
            $new_data['series'][$k]['now_money'] = $v['now_value'];
            $new_data['series'][$k]['last_money'] = $v['last_value'];
            $new_data['series'][$k]['rate'] = $v['rate'];
            $new_data['series'][$k]['value'] = array_values($v['curve']['y']);

        }

        return $new_data;
    }

    public function getTopRightTwoTrade()
    {
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        /** month订单数 */
        $monthOrderCountWhere['is_del'] = 0;
        $monthOrderCountWhere['paid'] = 1;
        $monthOrderCountWhere['pid'] = 0;
        $monthOrderCountWhere['timeKey'] = $this->TimeConvert("month");
        $monthOrderCount = $orderService->getOrderCountByWhere($monthOrderCountWhere);
        //本月订单数曲线
        $monthCurveOrderCount = $orderService->getOrderGroupCountByWhere($monthOrderCountWhere);
        $monthCurveOrderCount = $this->trendYdata($monthCurveOrderCount, $monthOrderCountWhere['timeKey']);
        //上月订单数
        $lastOrderCountWhere['timeKey'] = $this->TimeConvert("last_month");
        $lastOrderCountWhere['is_del'] = 0;
        $lastOrderCountWhere['paid'] = 1;
        $lastOrderCountWhere['pid'] = 0;
        $lastOrderCount = $orderService->getOrderCountByWhere($lastOrderCountWhere);
        //上月订单曲线
        // $lastCurveOrderCount = $orderService->getOrderGroupCountByWhere($lastOrderCountWhere);
        // $lastCurveOrderCount = $this->trendYdata($lastCurveOrderCount, 'month');
        //订单数环比增长率
        // $orderCountMonthChain = (($monthOrderCount - $lastOrderCount) / $lastOrderCount) * 100;
        $orderCountMonthChain = $this->countRate($monthOrderCount, $lastOrderCount);
        $data[] = [
            'name' => "本月订单数",
            'now_value' => $monthOrderCount,
            'last_value' => $lastOrderCount,
            'rate' => $orderCountMonthChain,
            'curve' => $monthCurveOrderCount
        ];
        /** month下单人数 */
        //本月支付人数
        $monthOrderPeopleWhere['timeKey'] = $this->TimeConvert("month");;
        $monthOrderPeopleWhere['paid'] = 1;
        $monthPayOrderPeople = count($orderService->getPayOrderPeopleByWhere($monthOrderPeopleWhere));
        //本月支付人数曲线
        $monthCurveOrderPeople = $orderService->getPayOrderGroupPeopleByWhere($monthOrderPeopleWhere);
        $monthCurveOrderPeople = $this->trendYdata($monthCurveOrderPeople, $monthOrderPeopleWhere['timeKey']);
        //上月支付人数
        $lastOrderPeopleWhere['timeKey'] = $this->TimeConvert("last_month");
        $lastOrderPeopleWhere['paid'] = 1;
        $lastPayOrderPeople = count($orderService->getPayOrderPeopleByWhere($lastOrderPeopleWhere));
        //上月支付曲线
        // $lastCurveOrderPeople = $orderService->getPayOrderGroupPeopleByWhere($lastOrderPeopleWhere);
        // $lastCurveOrderPeople = $this->trendYdata($lastCurveOrderPeople, 'month');
        //订单支付人数环比
        $orderPeopleDayChain = $this->countRate($monthPayOrderPeople, $lastPayOrderPeople);
        $data[] = [
            'name' => "本月支付人数",
            'now_value' => $monthPayOrderPeople,
            'last_value' => $lastPayOrderPeople,
            'rate' => $orderPeopleDayChain,
            'curve' => $monthCurveOrderPeople
        ];
        $new_data = [];
        foreach ($data as $k => $v) {
            $new_data[$k]['name'] = $v['name'];
            $new_data[$k]['now_money'] = $v['now_value'];
            $new_data[$k]['last_money'] = $v['last_value'];
            $new_data[$k]['rate'] = $v['rate'];
            $new_data[$k]['value'] = $v['curve']['y'];
        }

        return $new_data;
    }

    /**
     * 交易总额
     * @param $where
     * @param $selectType
     * @return array|float|int|mixed
     */
    public function tradeTotalMoney($where, $selectType, $isNum = false)
    {
        /** 收入营业额 */
        //商品订单收入
        $inOrderMoney = $this->getOrderTotalMoney($where, $selectType, "", $isNum);
        //用户充值收入
        $inRechargeMoneyHome = $this->getRechargeTotalMoney($where, $selectType, "", $isNum);
        $inrechgeMoneyAdmin = $this->getBillYeTotalMoney($where, $selectType, '', $isNum);
        $inRechargeMoney = bcadd($inRechargeMoneyHome, $inrechgeMoneyAdmin, 2);
        //购买会员收入
        $inMemberMoney = $this->getMemberTotalMoney($where, $selectType, "", $isNum);
        //线下收款收入
        $inOfflineMoney = $this->getOfflineTotalMoney($where, $selectType, "", $isNum);
        //总交易额
        $inTotalMoney = bcadd(bcadd($inOrderMoney, $inRechargeMoney, 2), bcadd($inMemberMoney, $inOfflineMoney, 2), 2);/* - $outExtractUserMoney*/
        return $inTotalMoney;
    }

    /**
     * 交易额曲线图
     * @param $where
     * @param $selectType
     * @return array
     */
    public function tradeGroupMoney($where, $selectType)
    {

        //商品订单收入
        $orderGroup = "add_time";
        $OrderMoney = $this->getOrderTotalMoney($where, $selectType, $orderGroup);
        //用户充值收入
        $rechargeGroup = "add_time";
        $RechargeMoneyHome = $this->getRechargeTotalMoney($where, $selectType, $rechargeGroup);
        $RechargeMoneyAdmin = $this->getBillYeTotalMoney($where, $selectType, $rechargeGroup);
        $RechargeMoney = $this->totalArrData([$RechargeMoneyHome, $RechargeMoneyAdmin]);
        //购买会员收入
        $memberGroup = "add_time";
        $MemberMoney = $this->getMemberTotalMoney($where, $selectType, $memberGroup);
        //线下收款收入
        $offlineGroup = "add_time";
        $OfflineMoney = $this->getOfflineTotalMoney($where, $selectType, $offlineGroup);
        return $this->totalArrData([$OrderMoney, $RechargeMoney, $MemberMoney, $OfflineMoney]);
    }

    /**
     * 底部数据
     * @param $where
     * @return array
     * @throws \Exception
     */
    public function getBottomTrade($where)
    {

        if (!$where['data']) {
            $where['time'] = ['start_time' => date('Y-m-d 00:00:00', time()), "end_time" => date('Y-m-d 23:59:59', time())];
        } else {
            $time = explode("-", $where['data']);
            $where['time'] = ['start_time' => date('Y-m-d 00:00:00', strtotime($time[0])), "end_time" => date('Y-m-d 23:59:59', strtotime($time[1]))];
        }
        unset($where['data']);
        /** @var ExportServices $exportService */
        $exportService = app()->make(ExportServices::class);
        $chainTime = $this->chainTime($where['time']);
        $isNum = false;
        if ($chainTime == "other") $isNum = true;
        $dateWhere['time'] = $isNum ? $where['time'] : $chainTime;
        $topData = array();
        $Chain = array();

        /** 商品支付金额 */
        $OrderMoney = $this->getOrderTotalMoney($where, "sum");
        $lastOrderMoney = $this->getOrderTotalMoney($dateWhere, "sum", "", $isNum);
        $OrderCurve = $this->getOrderTotalMoney($where, "group", "add_time");
        $OrderChain = $this->countRate($OrderMoney, $lastOrderMoney);
        $topData[2] = [
            'title' => '商品支付金额',
            'desc' => '选定条件下，用户购买商品的实际支付金额，包括微信支付、余额支付、支付宝支付、线下支付金额（拼团商品在成团之后计入，线下支付订单在后台确认支付后计入）',
            'total_money' => $OrderMoney,
            'rate' => $OrderChain,
            'value' => $OrderCurve['y'],
            'type' => 1,
            'sign' => 'goods',
        ];

        $Chain['goods'] = $OrderCurve;

        /** 购买会员金额 */
        $memberMoney = $this->getMemberTotalMoney($where, 'sum');
        $lastMemberMoney = $this->getMemberTotalMoney($dateWhere, 'sum', "", $isNum);
        $memberCurve = $this->getMemberTotalMoney($where, 'group', "pay_time");
        $MemberChain = $this->countRate($memberMoney, $lastMemberMoney);
        $topData[3] = [
            'title' => '购买会员金额',
            'desc' => '选定条件下，用户成功购买付费会员的金额',
            'total_money' => $memberMoney,
            'rate' => $MemberChain,
            'value' => $memberCurve['y'],
            'type' => 1,
            'sign' => 'member',
        ];
        $Chain['member'] = $memberCurve;

        /** 充值金额 */
        $rechgeMoneyHome = $this->getRechargeTotalMoney($where, 'sum');
        $rechgeMoneyAdmin = $this->getBillYeTotalMoney($where, 'sum');
        $rechgeMoneyTotal = bcadd($rechgeMoneyHome, $rechgeMoneyAdmin, 2);
        $lastRechgeMoneyHome = $this->getRechargeTotalMoney($dateWhere, 'sum', "", $isNum);
        $lastRechgeMoneyAdmin = $this->getBillYeTotalMoney($dateWhere, 'sum', "", $isNum);
        $lastRechgeMoneyTotal = bcadd($lastRechgeMoneyHome, $lastRechgeMoneyAdmin, 2);
        $RechgeHomeCurve = $this->getRechargeTotalMoney($where, 'group', "pay_time");
        $RechgeAdminCurve = $this->getBillYeTotalMoney($where, 'group', "add_time");
        $RechgeTotalCurve = $this->totalArrData([$RechgeHomeCurve, $RechgeAdminCurve]);
        $RechgeChain = $this->countRate($rechgeMoneyTotal, $lastRechgeMoneyTotal);
        $topData[4] = [
            'title' => '充值金额',
            'desc' => '选定条件下，用户成功充值的金额',
            'total_money' => $rechgeMoneyTotal,
            'rate' => $RechgeChain,
            'value' => $RechgeTotalCurve['y'],
            'type' => 1,
            'sign' => 'rechge',
        ];
        $Chain['rechage'] = $RechgeTotalCurve;

        /** 线下收银 */
        $offlineMoney = $this->getOfflineTotalMoney($where, 'sum');
        $lastOfflineMoney = $this->getOfflineTotalMoney($dateWhere, 'sum', "", $isNum);
        $offlineCurve = $this->getOfflineTotalMoney($where, 'group', "pay_time");
        $offlineChain = $this->countRate($offlineMoney, $lastOfflineMoney);
        $topData[5] = [
            'title' => '线下收银金额',
            'desc' => '选定条件下，用户在线下扫码支付的金额',
            'total_money' => $offlineMoney,
            'rate' => $offlineChain,
            'value' => $offlineCurve['y'],
            'type' => 0,
            'sign' => 'offline',
        ];
        $Chain['offline'] = $offlineCurve;

        /**  支出*/
        //余额支付商品
        $outYeOrderMoney = $this->getOrderTotalMoney(['pay_type' => "yue", 'time' => $where['time']], 'sum');
        $lastOutYeOrderMoney = $this->getOrderTotalMoney(['pay_type' => "yue", 'time' => $dateWhere['time']], 'sum', "", $isNum);
        $outYeOrderCurve = $this->getOrderTotalMoney(['pay_type' => "yue", 'time' => $where['time']], 'group', 'pay_time');
        $outYeOrderChain = $this->countRate($outYeOrderMoney, $lastOutYeOrderMoney);
        //余额购买会员
        $outYeMemberMoney = $this->getMemberTotalMoney(['pay_type' => "yue", 'time' => $where['time']], 'sum');
        $lastOutYeMemberMoney = $this->getMemberTotalMoney(['pay_type' => "yue", 'time' => $dateWhere['time']], 'sum', "", $isNum);
        $outYeMemberCurve = $this->getMemberTotalMoney(['pay_type' => "yue", 'time' => $where['time']], 'group', "pay_time");
        $outYeMemberChain = $this->countRate($outYeMemberMoney, $lastOutYeMemberMoney);
        //余额支付
        $outYeMoney = bcadd($outYeOrderMoney, $outYeMemberMoney, 2);
        $lastOutYeMoney = bcadd($lastOutYeOrderMoney, $lastOutYeMemberMoney, 2);
        $outYeCurve = $this->totalArrData([$outYeOrderCurve, $outYeMemberCurve]);
        $outYeChain = $this->countRate($outYeOrderChain, $outYeMemberChain);
        $topData[7] = [
            'title' => '余额支付金额',
            'desc' => '用户下单时使用余额实际支付的金额',
            'total_money' => $outYeMoney,
            'rate' => $outYeChain,
            'value' => $outYeCurve['y'],
            'type' => 0,
            'sign' => 'yue',
        ];
        $Chain['out_ye'] = $outYeCurve;


        //支付佣金金额
        $outExtractMoney = $this->getExtractTotalMoney($where, 'sum');
        $lastOutExtractMoney = $this->getExtractTotalMoney($dateWhere, 'sum', "", $isNum);
        $OutExtractCurve = $this->getExtractTotalMoney($where, 'group', "add_time");
        $OutExtractChain = $this->countRate($outExtractMoney, $lastOutExtractMoney);
        $topData[8] = [
            'title' => '支付佣金金额',
            'desc' => '后台给推广员支付的推广佣金，以实际支付为准',
            'total_money' => $outExtractMoney,
            'rate' => $OutExtractChain,
            'value' => $OutExtractCurve['y'],
            'type' => 0,
            'sign' => 'yong',
        ];
        $Chain['extract'] = $OutExtractCurve;

        //商品退款金额
        $outOrderRefund = $this->getOrderRefundTotalMoney(['refund_type' => 6, 'time' => $where['time']], 'sum');
        $lastOutOrderRefund = $this->getOrderRefundTotalMoney(['refund_type' => 6, 'time' => $dateWhere['time']], 'sum', "", $isNum);
        $outOrderRefundCurve = $this->getOrderRefundTotalMoney(['refund_type' => 6, 'time' => $where['time']], 'group', 'add_time');
        $orderRefundChain = $this->countRate($outOrderRefund, $lastOutOrderRefund);
        $topData[9] = [
            'title' => '商品退款金额',
            'desc' => '用户成功退款的商品金额',
            'total_money' => $outOrderRefund,
            'rate' => $orderRefundChain,
            'value' => $outOrderRefundCurve['y'],
            'type' => 0,
            'sign' => 'refund',
        ];
        $Chain['refund'] = $outOrderRefundCurve;

        //支出金额
        $outTotalMoney = bcadd(bcadd($outYeMoney, $outExtractMoney, 2), $outOrderRefund, 2);
        $lastOutTotalMoney = bcadd(bcadd($lastOutYeMoney, $lastOutExtractMoney, 2), $lastOutOrderRefund, 2);
        $outTotalCurve = $this->totalArrData([$outYeCurve, $OutExtractCurve, $outOrderRefundCurve]);
        $outTotalChain = $this->countRate($outTotalMoney, $lastOutTotalMoney);
        $topData[6] = [
            'title' => '支出金额',
            'desc' => '余额支付金额、支付佣金金额、商品退款金额',
            'total_money' => $outTotalMoney,
            'rate' => $outTotalChain,
            'value' => $outTotalCurve['y'],
            'type' => 1,
            'sign' => 'out',
        ];
        $Chain['out'] = $outTotalCurve;

        /** 交易毛利金额*/
        $jiaoyiMoney = $this->tradeTotalMoney($where, "sum");

        $jiaoyiMoney = bcsub($jiaoyiMoney, $outTotalMoney, 2);
        $lastJiaoyiMoney = $this->tradeTotalMoney($dateWhere, "sum", $isNum);
        $lastJiaoyiMoney = bcsub($lastJiaoyiMoney, $lastOutTotalMoney, 2);
        $jiaoyiCurve = $this->tradeGroupMoney($where, "group");
        $jiaoyiCurve = $this->subdutionArrData($jiaoyiCurve, $outTotalCurve);
        $jiaoyiChain = $this->countRate($jiaoyiMoney, $lastJiaoyiMoney);
        $topData[1] = [
            'title' => '交易毛利金额',
            'desc' => '交易毛利金额 = 营业额 - 支出金额',
            'total_money' => $jiaoyiMoney,
            'rate' => $jiaoyiChain,
            'value' => $jiaoyiCurve['y'],
            'type' => 1,
            'sign' => 'jiaoyi',
        ];
        $Chain['jiaoyi'] = $jiaoyiCurve;

        /** @var 营业额 $inTotalMoney */
        $inTotalMoney = $this->tradeTotalMoney($where, "sum");
        $lastInTotalMoney = $this->tradeTotalMoney($dateWhere, "sum", $isNum);
        $inTotalCurve = $this->tradeGroupMoney($where, "group");
        $inTotalChain = $this->countRate($inTotalMoney, $lastInTotalMoney);
        $topData[0] = [
            'title' => '营业额',
            'desc' => '商品支付金额、充值金额、购买付费会员金额、线下收银金额',
            'total_money' => $inTotalMoney,
            'rate' => $inTotalChain,
            'value' => $inTotalCurve['y'],
            'type' => 1,
            'sign' => 'in',
        ];
        ksort($topData);
        $data = [];
        foreach ($topData as $k => $v) {
            $data['x'] = $Chain['out']['x'];
            $data['series'][$k]['name'] = $v['title'];
            $data['series'][$k]['desc'] = $v['desc'];
            $data['series'][$k]['money'] = $v['total_money'];
            $data['series'][$k]['type'] = $v['type'];
            $data['series'][$k]['rate'] = $v['rate'];
            $data['series'][$k]['value'] = array_values($v['value']);
        }
        $export = $exportService->tradeData($data, '交易统计', 2);
        $data['export'] = $export[0];
        return $data;
    }

    /**
     * 多个数组相加
     * @param array $arr
     * @return array|false
     */
    public function totalArrData(array $arr)
    {
        if (!$arr || !is_array($arr)) return false;
        $item = array();
        $y = array_column($arr, "y");
        $x = array_column($arr, "x")[0];
        foreach ($y as $key => $value) {
            foreach ($value as $k => $v) {
                if (isset($item[$k])) {
                    $item[$k] = bcadd($item[$k], $v, 2);
                } else {
                    $item[$k] = $v;
                }
            }
        }
        return ['x' => $x, 'y' => $item];
    }

    /**
     * 数组相减
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public function subdutionArrData(array $arr1, array $arr2)
    {
        $item = array();
        foreach ($arr1['y'] as $key => $value) {
            $item['y'][$key] = bcsub($value, $arr2['y'][$key], 2);
        }
        $item['x'] = $arr1['x'];
        return $item;
    }

    /**
     * 搜索时间转换
     * @param $timeKey
     * @param false $isNum
     * @return array
     * @throws \Exception
     */
    public function TimeConvert($timeKey, $isNum = false)
    {
        switch ($timeKey) {
            case "today" :
                $data['start_time'] = date('Y-m-d 00:00:00', time());
                $data['end_time'] = date('Y-m-d 23:59:59', time());
                $data['days'] = 1;
                break;
            case "yestoday" :
                $data['start_time'] = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $data['end_time'] = date('Y-m-d 23:59:59', strtotime('-1 day'));
                $data['days'] = 1;
                break;
            case "last_month" :
                $data['start_time'] = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $data['end_time'] = date('Y-m-t 23:59:59', strtotime('-1 month'));
                $data['days'] = 30;
                break;
            case "month" :
                $data['start_time'] = $month_start_time = date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));
                $data['end_time'] = date('Y-m-d 23:59:59', strtotime("$month_start_time +1 month -1 day"));
                $data['days'] = 30;
                break;
            case "year" :
                $data['start_time'] = date('Y-01-01 00:00:00', time());
                $data['end_time'] = date('Y-12-t 23:59:59', time());
                $data['days'] = 365;
                break;
            case "last_year" :
                $data['start_time'] = date('Y-01-01 00:00:00', strtotime('-1 year'));
                $data['end_time'] = date('Y-12-t 23:59:59', strtotime('-1 year'));
                $data['days'] = 365;
                break;
            case 30 :
            case 15 :
            case 7 :
                if (!$isNum) {
                    $data['start_time'] = date("Y-m-d 00:00:00", strtotime("-$timeKey day"));
                    $data['end_time'] = date('Y-m-d 23:59:59', time());
                    $data['days'] = $timeKey;
                } else {
                    $day = $timeKey * 2;
                    $data['start_time'] = date("Y-m-d 00:00:00", strtotime("-$day  day"));
                    $data['end_time'] = date("Y-m-d 23:59:59", strtotime("-$timeKey day"));
                    $data['days'] = $timeKey;
                }
                break;
            default:
                $datetime_start = new \DateTime($timeKey['start_time']);
                $datetime_end = new \DateTime($timeKey['end_time']);
                $days = $datetime_start->diff($datetime_end)->days;
                $days = $days > 0 ? $days : 1;
                if (!$isNum) {
                    $data['start_time'] = $timeKey['start_time'];
                    $data['end_time'] = $timeKey['end_time'];
                    $data['days'] = $days;
                } else {
                    $data['start_time'] = date("Y-m-d 00:00:00", strtotime("-$days day"));
                    $data['end_time'] = $timeKey['start_time'];
                    $data['days'] = $days;
                }

        }

        return $data;
    }

    /**
     * 获取订单退款
     * @param $where
     * @param string $selectType
     * @param string $group
     * @param bool $isNum
     * @return array|float|int
     * @throws \Exception
     */
    public function getOrderRefundTotalMoney($where, string $selectType, string $group = '', bool $isNum = false)
    {
        $orderSumField = isset($where['refund_type']) ? "refunded_price" : "refund_price";
        $whereOrderMoner['refund_type'] = isset($where['refund_type']) ? $where['refund_type'] : 6;
        $whereOrderMoner['is_cancel'] = 0;
        $whereOrderMoner['timeKey'] = $this->TimeConvert($where['time'], $isNum);

        /** @var StoreOrderRefundServices $storeOrderRefundServices */
        $storeOrderRefundServices = app()->make(StoreOrderRefundServices::class);
        $totalMoney = $storeOrderRefundServices->getOrderRefundMoneyByWhere($whereOrderMoner, $orderSumField, $selectType, $group);

        if ($group) {
            $totalMoney = $this->trendYdata($totalMoney, $whereOrderMoner['timeKey']);
        }
        return $totalMoney;
    }

    /**
     * 获取商品营收
     * @param $where
     * @param string $selectType
     * @param string $group
     * @param bool $isNum
     * @return array|float|int
     * @throws \Exception
     */
    public function getOrderTotalMoney($where, string $selectType, string $group = "", bool $isNum = false)
    {
        /** 普通商品订单支付金额 */
        /** @var StoreOrderServices $storeOrderService */
        $storeOrderService = app()->make(StoreOrderServices::class);
        $orderSumField = isset($where['refund_status']) ? "refund_price" : "pay_price";
        $whereOrderMoner['refund_status'] = isset($where['refund_status']) ? $where['refund_status'] : 0;
        $whereOrderMoner['paid'] = 1;
        $whereOrderMoner['pid'] = 0;

        if (isset($where['pay_type'])) {
            $whereOrderMoner['pay_type'] = $where['pay_type'];
        }
        $whereOrderMoner['timeKey'] = $this->TimeConvert($where['time'], $isNum);
        $totalMoney = $storeOrderService->getOrderMoneyByWhere($whereOrderMoner, $orderSumField, $selectType, $group);

        if ($group) {
            $totalMoney = $this->trendYdata($totalMoney, $whereOrderMoner['timeKey']);
        }
        return $totalMoney;
    }

    /**
     * 支付佣金
     * @param $where
     * @param string $selectType
     * @param string $group
     * @param bool $isNum
     * @return array|float|mixed
     * @throws \Exception
     */
    public function getExtractTotalMoney($where, string $selectType, string $group = "", bool $isNum = false)
    {
        /** 普通商品订单支付金额 */
        /** @var UserExtractServices $extractService */
        $extractService = app()->make(UserExtractServices::class);
        $orderSumField = "extract_price";
        $whereData['status'] = 1;
        $whereData['timeKey'] = $this->TimeConvert($where['time'], $isNum);
        $totalMoney = $extractService->getOutMoneyByWhere($whereData, $orderSumField, $selectType, $group);
        if ($group) {

            $totalMoney = $this->trendYdata($totalMoney, $whereData['timeKey']);

        }
        return $totalMoney;
    }

    /**
     * 获取用户充值营收
     * @param array $where
     * @param string $selectType
     * @param string $group
     * @param bool $isNum
     * @return array|float|int
     * @throws \Exception
     */
    public function getRechargeTotalMoney(array $where, string $selectType, string $group = "", bool $isNum = false)
    {
        /** 用户充值金额 */
        /** @var UserRechargeServices $userRechageService */
        $userRechageService = app()->make(UserRechargeServices::class);
        $rechargeSumField = "price";
        $whereInRecharge['paid'] = 1;
        $whereInRecharge['refund_price'] = '0.00';
        $whereInRecharge['timeKey'] = $this->TimeConvert($where['time'], $isNum);
        $whereInRecharge['store_id'] = 0;
        $totalMoney = $userRechageService->getRechargeMoneyByWhere($whereInRecharge, $rechargeSumField, $selectType, $group);
        if ($group) {
            $totalMoney = $this->trendYdata($totalMoney, $whereInRecharge['timeKey']);
        }
        return $totalMoney;
    }

    /**
     * 后台手动充值
     * @param array $where
     * @param string $selectType
     * @param string $group
     * @param bool $isNum
     * @return array|float|int
     * @throws \Exception
     */
    public function getBillYeTotalMoney(array $where, string $selectType, string $group = "", bool $isNum = false)
    {
        /** 后台用户充值金额 */
        $rechargeSumField = "number";
        $whereInRecharge['pm'] = 1;
        $whereInRecharge['type'] = 'system_add';
        $whereInRecharge['timeKey'] = $this->TimeConvert($where['time'], $isNum);
        $whereInRecharge['store_id'] = 0;
        /** @var UserMoneyServices $userMoneyServices */
        $userMoneyServices = app()->make(UserMoneyServices::class);
        $totalMoney = $userMoneyServices->getRechargeMoneyByWhere($whereInRecharge, $rechargeSumField, $selectType, $group);
        if ($group) {
            $totalMoney = $this->trendYdata($totalMoney, $whereInRecharge['timeKey']);
        }
        return $totalMoney;
    }

    /**
     * 购买会员总额
     * @param array $where
     * @param string $selectType
     * @param string $group
     * @param bool $isNum
     * @return array|mixed
     * @throws \Exception
     */
    public function getMemberTotalMoney(array $where, string $selectType, string $group = "", bool $isNum = false)
    {

        /** 购买会员 */
        /** @var OtherOrderServices $otherOrderService */
        $otherOrderService = app()->make(OtherOrderServices::class);
        $memberSumField = "pay_price";
        $whereInMember['type'] = 1;
        $whereInMember['paid'] = 1;
        $whereInMember['store_id'] = 0;
        if (isset($where['pay_type'])) {
            $whereInMember['pay_type'] = $where['pay_type'];
        } else {
            //$whereInMember['pay_type_no'] = 'yue';
        }
        $whereInMember['timeKey'] = $this->TimeConvert($where['time'], $isNum);
        $totalMoney = $otherOrderService->getMemberMoneyByWhere($whereInMember, $memberSumField, $selectType, $group);
        if ($group) {
            $totalMoney = $this->trendYdata($totalMoney, $whereInMember['timeKey']);
        }
        return $totalMoney;

    }

    /**
     * 线下付款总额
     * @param array $where
     * @param string $selectType
     * @param string $group
     * @param bool $isNum
     * @return array|mixed
     * @throws \Exception
     */
    public function getOfflineTotalMoney(array $where, string $selectType, string $group = "", bool $isNum = false)
    {
        /** 线下付款总额 */
        /** @var OtherOrderServices $otherOrderService */
        $otherOrderService = app()->make(OtherOrderServices::class);
        $offlineSumField = "pay_price";
        $whereOffline['type'] = 3;
        $whereOffline['paid'] = 1;
        $whereOffline['store_id'] = 0;
        // $whereOffline['pay_type_no'] = 'yue';
        $whereOffline['timeKey'] = $this->TimeConvert($where['time'], $isNum);
        $totalMoney = $otherOrderService->getMemberMoneyByWhere($whereOffline, $offlineSumField, $selectType, $group);
        if ($group) {
            $totalMoney = $this->trendYdata($totalMoney, $whereOffline['timeKey']);
        }
        return $totalMoney;
    }

    /**
     * 处理Y坐标数据
     * @param array $data
     * @param array $timeKey
     * @return array
     * @throws \Exception
     */
    public function trendYdata(array $data, array $timeKey)
    {
        $hourMoney = array();
        $timeData = array();
        //获取日期之间的天数
        $getDayRange = function ($date, $timeKey) {
            $datearr = [];
            $stime = strtotime($timeKey['start_time']);
            $etime = strtotime($timeKey['end_time']);
            while ($stime <= $etime) {
                $datearr['x'][] = date($date, $stime);
                $datearr['y'][] = date($date, $stime);
                $stime = $stime + 86400;
            }
            return $datearr;
        };
        //获取日期之间的月份
        $getMonthRange = function ($date, $timeKey) {
            $datearr = [];
            $stime = date('Y-m-d', strtotime($timeKey['start_time']));
            $etime = date('Y-m-d', strtotime($timeKey['end_time']));
            $start = new \DateTime($stime);
            $end = new \DateTime($etime);
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $datearr['x'][] = $dt->format($date);
                $datearr['y'][] = $dt->format($date);
            }
            return $datearr;
        };
        if ($timeKey['days'] == 1) {
            for ($i = 0; $i <= 24; $i++) {
                $timeData['x'][] = (string)($i < 10 ? ('0' . $i) : $i);
                $timeData['y'][] = $i < 10 ? ('0' . $i) : $i;
                //$timeData['y'][] = $i < 10 ? ('0' . $i . ":00") : $i . ":00";
                //$timeData['x'][] = $i < 10 ? ('0' . $i . ":00") : $i . ":00";
            }
        } elseif ($timeKey['days'] == 30) {
            $timeData = $getDayRange('Y-m-d', $timeKey);
        } elseif ($timeKey['days'] == 365) {
            $timeData = $getMonthRange('Y-m', $timeKey);
        } elseif ($timeKey['days'] > 1 && $timeKey['days'] < 30) {
            $timeData = $getDayRange('Y-m-d', $timeKey);
        } elseif ($timeKey['days'] > 30 && $timeKey['days'] < 365) {
            $timeData = $getMonthRange('Y-m', $timeKey);
        }
        if ($data) {
            $hourMoney = array_column($data, 'number', 'time');
        }
        $y = array();
        foreach ($timeData['y'] as $k => $v) {
            if (array_key_exists($v, $hourMoney)) {
                $y[$v] = $hourMoney[$v];
            } else {
                $y[$v] = 0;
            }
        }
        return ['x' => $timeData['x'], 'y' => $y];
    }

    /**
     * 计算环比增长率
     * @param $nowValue
     * @param $lastValue
     * @return float|int|string
     */
    public function countRate($nowValue, $lastValue)
    {
        if ($lastValue == 0 && $nowValue == 0) return 0;
        if ($lastValue == 0) return round(bcmul(bcdiv($nowValue, 1, 4), 100, 2), 2);
        if ($nowValue == 0) return -100;
        return bcmul(bcdiv((bcsub($nowValue, $lastValue, 2)), $lastValue, 2), 100, 2);
    }

    /**
     * 获取环比时间类型
     * @param $timeKey
     * @return string
     */
    public function chainTime($timeKey)
    {
        switch ($timeKey) {
            case "today" :
                return "yestoday";
            case "month" :
                return "last_month";
            case "year" :
                return "last_year";
            default :
                return "other";
        }

    }


}
