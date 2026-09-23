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

namespace app\services\statistic;

use crmeb\basic\BaseService;
use app\model\order\OrderCenter;
use app\model\user\User;
use app\dao\order\OrderCenterDao;
use crmeb\exceptions\AdminException;


class OrderStatisticServices extends BaseService
{
    /**
     * 订单统计基础
     * @param $where
     * @return array
     */
    public function getBasic($where)
    {
        $time = explode('-', $where['time']);
        if (count($time) != 2) throw new AdminException('请选择时间');
        /** @var OrderCenterDao $orderDao */
        $orderDao = app()->make(OrderCenterDao::class);
        // 支付时间区间（timeKey 绑定 eb_order_center.pay_time）
        $timeKey = $this->buildTimeKey($time);
        // 已支付订单：支付金额/订单量
        $wherePay = ['pay_status' => OrderCenter::PAY_PAID, 'is_del' => 0];
        $data['pay_price'] = $orderDao->getWhereSumField($wherePay + ['timeKey' => $timeKey], 'pay_price');
        $data['pay_count'] = $orderDao->getDb()->where($wherePay)
            ->whereBetweenTime('pay_time', $timeKey['start_time'], $timeKey['end_time'])->count();
        // 已退款订单：退款金额/退款订单数（refund_status=已退款 累计 refund_price，按订单数计非售后单数）
        $whereRefund = ['pay_status' => OrderCenter::PAY_PAID, 'refund_status' => OrderCenter::REFUND_SUCCESS, 'is_del' => 0];
        $data['refund_price'] = $orderDao->getWhereSumField($whereRefund + ['timeKey' => $timeKey], 'refund_price');
        $data['refund_count'] = $orderDao->getDb()->where($whereRefund)
            ->whereBetweenTime('pay_time', $timeKey['start_time'], $timeKey['end_time'])->count();
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
        if (count($time) != 2) throw new AdminException('请选择时间');
        $dayCount = bcadd(bcdiv(bcsub(strtotime($time[1]), strtotime($time[0])), '86400'), '1');
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
    public function trend($time, $num)
    {
        /** @var OrderCenterDao $storeOrder */
        $storeOrder = app()->make(OrderCenterDao::class);

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
        // 支付金额/订单量趋势（按 pay_time 分组）
        $wherePay = ['pay_status' => OrderCenter::PAY_PAID, 'is_del' => 0];
        $pay_price = $this->groupTrend($storeOrder, $wherePay, 'SUM(pay_price)', $time, $timeType);
        $pay_count = $this->groupTrend($storeOrder, $wherePay, 'COUNT(*)', $time, $timeType);
        // 退款金额/退款单数趋势（refund_status=已退款 累计 refund_price）
        $whereRefund = ['pay_status' => OrderCenter::PAY_PAID, 'refund_status' => OrderCenter::REFUND_SUCCESS, 'is_del' => 0];
        $refund_price = $this->groupTrend($storeOrder, $whereRefund, 'SUM(refund_price)', $time, $timeType);
        $refund_count = $this->groupTrend($storeOrder, $whereRefund, 'COUNT(*)', $time, $timeType);
        $data = $series = [];
        foreach ($xAxis as $item) {
            $data['订单金额'][] = isset($pay_price[$item]) ? floatval($pay_price[$item]) : 0;
            $data['订单量'][] = isset($pay_count[$item]) ? floatval($pay_count[$item]) : 0;
            $data['退款金额'][] = isset($refund_price[$item]) ? floatval($refund_price[$item]) : 0;
            $data['退款订单量'][] = isset($refund_count[$item]) ? floatval($refund_count[$item]) : 0;
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
        $time = explode('-', $where['time']);
        if (count($time) != 2) throw new AdminException('请选择时间');
        /** @var OrderCenterDao $orderService */
        $orderService = app()->make(OrderCenterDao::class);

        // 渠道统计：中台订单无渠道字段，按下单用户的注册渠道（eb_user.user_type）统计已支付订单分布
        $channelMap = ['wechat', 'routine', 'h5', 'app'];
        $bing_xdata = ['公众号', '小程序', 'H5', 'APP'];
        $color = ['#64a1f4', '#3edeb5', '#70869f', '#fc7d6a'];
        $userTable = app()->make(User::class)->getName();
        $bing_data = [];
        foreach ($bing_xdata as $key => $item) {
            $bing_data[] = [
                'name' => $item,
                'value' => $orderService->getDb()->alias('o')
                    ->join($userTable . ' u', 'o.uid = u.uid')
                    ->where('o.pay_status', OrderCenter::PAY_PAID)
                    ->where('o.is_del', 0)
                    // 结束日补至当天 23:59:59（whereBetweenTime 对纯日期字符串不扩展当天，join 别名场景用 whereBetween 时间戳更稳）
                    ->whereBetween('o.pay_time', [strtotime($time[0]), strtotime($time[1] . ' 23:59:59')])
                    ->where('u.user_type', $channelMap[$key])
                    ->count(),
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
     * 订单类型
     * @param $where
     * @return array
     */
    public function getType($where)
    {
        $time = explode('-', $where['time']);
        if (count($time) != 2) throw new AdminException('请选择时间');
        /** @var OrderCenterDao $orderService */
        $orderService = app()->make(OrderCenterDao::class);

        // 活动类型映射（中台 activity_type 为字符串，普通订单为空或 normal，与旧数字枚举不兼容）
        // integral（积分兑换）为 trade 活动类型，独立成片统计；无独立开关，始终展示（无数据时显示 0，与空活动表现一致）
        $activityMap = [['', 'normal'], 'seckill', 'bargain', 'combination', 'advance', 'integral'];
        $bing_xdata = ['普通订单', '秒杀订单', '砍价订单', '拼团订单', '预售订单', '积分订单'];
        $model_checkbox = sys_config('model_checkbox', ['seckill', 'bargain', 'combination']);
        $color = ['#64a1f4', '#3edeb5', '#70869f', '#ffc653', '#fc7d6a', '#9b59b6'];
        $timeKey = $this->buildTimeKey($time);
        $bing_data = [];
        foreach ($bing_xdata as $key => $item) {
            if (!in_array('seckill', $model_checkbox) && $key == 1) continue;
            if (!in_array('bargain', $model_checkbox) && $key == 2) continue;
            if (!in_array('combination', $model_checkbox) && $key == 3) continue;
            $whereType = ['pay_status' => OrderCenter::PAY_PAID, 'is_del' => 0, 'timeKey' => $timeKey];
            // 普通订单匹配空或 normal，活动订单按活动类型字符串精确匹配
            $whereType[] = $key == 0
                ? ['activity_type', 'IN', $activityMap[$key]]
                : ['activity_type', '=', $activityMap[$key]];
            $bing_data[] = [
                'name' => $item,
                'value' => $orderService->getWhereSumField($whereType, 'pay_price'),
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
     * 构建统计时间区间（与订单中台 timeKey 约定一致，绑定 pay_time）
     * @param array $time 起止时间数组 [开始, 结束]
     * @return array
     */
    private function buildTimeKey(array $time): array
    {
        $days = bcadd(bcdiv(bcsub(strtotime($time[1]), strtotime($time[0])), '86400'), '1');
        return [
            'start_time' => $time[0],
            // 结束日补至当天 23:59:59：whereBetweenTime 对纯日期字符串不扩展当天，否则丢失结束日数据
            'end_time' => date('Y-m-d 23:59:59', strtotime($time[1])),
            'days' => (int)$days,
        ];
    }

    /**
     * 按支付时间分组统计趋势数据（对应旧 getProductTrend 语义，基于 eb_order_center）
     * @param OrderCenterDao $orderDao
     * @param array $where 查询条件
     * @param string $field 聚合表达式（SUM(x)/COUNT(*)）
     * @param array $time 起止时间数组
     * @param string $timeType 分组时间格式（%H/%m-%d/%Y-%m）
     * @return array days=>num 映射
     */
    private function groupTrend(OrderCenterDao $orderDao, array $where, string $field, array $time, string $timeType): array
    {
        // 结束日补至当天 23:59:59，保证结束日数据纳入统计
        return array_column($orderDao->getDb()->where($where)
            ->whereBetweenTime('pay_time', $time[0], $time[1] . ' 23:59:59')
            ->field("FROM_UNIXTIME(pay_time,'$timeType') as days,$field as num")
            ->group('days')->select()->toArray(), 'num', 'days');
    }
}
