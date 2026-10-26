<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------

namespace modules\product\normal\service;

use app\model\order\AfterSale;
use app\model\order\OrderCenter;
use app\model\order\StoreCart;
use app\model\product\CenterProductModel;
use app\model\product\product\StoreProductLog;
use app\services\other\export\ExportServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

/**
 * 实物商品统计服务。
 * 商品日志和购物车没有可靠的 product_type 字段，因此统一按实物商品主表 ID 集合过滤；
 * 订单中台则同时按 product_type=normal 过滤，保证统计口径不会跨商品类型。
 */
class StatisticServices extends BaseService
{
    private const RANK_SORT_FIELDS = ['visit', 'user', 'cart', 'orders', 'pay', 'price', 'changes', 'collect'];

    public function getBasic(array $where): array
    {
        $time = $this->parseTime($where['time'] ?? '');
        $now = $this->basicInfo($time);
        $dayNum = (int)floor((strtotime($time[1]) - strtotime($time[0])) / 86400) + 1;
        $lastTime = [
            date('Y/m/d 00:00:00', strtotime("-{$dayNum} days", strtotime($time[0]))),
            date('Y/m/d 23:59:59', strtotime('-1 day', strtotime($time[0]))),
        ];
        $last = $this->basicInfo($lastTime);

        $data = [];
        foreach ($now as $key => $value) {
            $previous = (float)($last[$key] ?? 0);
            $base = $previous > 0 ? $previous : 1;
            $data[$key] = [
                'num' => $value,
                'percent' => function_exists('bcdiv')
                    ? bcmul(bcdiv((string)((float)$value - $previous), (string)$base, 4), '100', 2)
                    : round((((float)$value - $previous) / $base) * 100, 2),
            ];
        }
        return $data;
    }

    public function getTrend(array $where, bool $excel = false)
    {
        $time = $this->parseTime($where['time'] ?? '');
        $dayCount = (int)floor((strtotime($time[1]) - strtotime($time[0])) / 86400) + 1;
        if ($dayCount <= 1) {
            return $this->trend($time, 0, $excel);
        }
        if ($dayCount <= 31) {
            return $this->trend($time, 1, $excel);
        }
        if ($dayCount <= 92) {
            return $this->trend($time, 3, $excel);
        }
        return $this->trend($time, 30, $excel);
    }

    public function getProductRanking(array $where): array
    {
        $time = $this->parseTime($where['time'] ?? '');
        $productIds = $this->productIds();
        if (!$productIds) {
            return [];
        }

        $sort = $where['sort'] ?? 'pay';
        $sort = is_string($sort) && in_array($sort, self::RANK_SORT_FIELDS, true) ? $sort : 'pay';
        $query = $this->productLogQuery($productIds)->whereBetweenTime('add_time', $time[0], $time[1]);
        $list = $query->with('storeName')
            ->field([
                'product_id',
                'SUM(visit_num) as visit',
                'COUNT(distinct(uid)) as user',
                'SUM(cart_num) as cart',
                'SUM(order_num) as orders',
                'SUM(pay_num) as pay',
                'SUM(pay_price * pay_num) as price',
                'SUM(cost_price) as cost',
                'ROUND((SUM(pay_price)-SUM(cost_price))/SUM(pay_price),2) as profit',
                'SUM(collect_num) as collect',
                'ROUND((COUNT(distinct(pay_uid))-1)/COUNT(distinct(uid)),2) as changes',
                'COUNT(distinct(pay_uid))-1 as repeats',
            ])
            ->group('product_id')
            ->order($sort, 'desc')
            ->order('product_id', 'asc')
            ->limit(20)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            $item['profit'] = $item['profit'] ?? 0;
            $item['changes'] = $item['changes'] ?? 0;
            if ($item['repeats'] === null) {
                $item['repeats'] = 0;
            } else {
                $item['repeats'] = $this->repeats($productIds, $time, (int)$item['product_id']) / max((float)$item['repeats'], 1);
            }
        }
        return $list;
    }

    protected function basicInfo(array $time): array
    {
        $productIds = $this->productIds();
        $data = [
            'browse' => 0,
            'user' => 0,
            'cart' => 0,
            'order' => 0,
            'pay' => 0,
            'payPrice' => 0,
            'cost' => 0,
            'refundPrice' => 0,
            'refund' => 0,
            'payPercent' => 0,
        ];
        if (!$productIds) {
            return $data;
        }

        $visit = $this->productLogQuery($productIds)->where('type', 'visit')->whereBetweenTime('add_time', $time[0], $time[1]);
        $data['browse'] = $visit->sum('visit_num');
        $user = $this->productLogQuery($productIds)->where('type', 'visit')->whereBetweenTime('add_time', $time[0], $time[1])
            ->field('COUNT(DISTINCT uid) AS count')->find();
        $data['user'] = (int)($user['count'] ?? 0);

        $cart = $this->cartQuery($productIds)->whereBetweenTime('add_time', $time[0], $time[1]);
        $data['cart'] = $cart->sum('cart_num');

        $order = $this->orderQuery()->where('is_del', 0)->whereBetweenTime('pay_time', $time[0], $time[1]);
        $data['order'] = $order->sum('total_num');

        // 支付口径含后续部分/全额退款订单（pay_status=2/3），退款在退款卡片单独统计
        $paidWhere = fn() => $this->orderQuery()->whereIn('pay_status', $this->paidStatuses())->where('is_del', 0)
            ->whereBetweenTime('pay_time', $time[0], $time[1]);
        $data['pay'] = $paidWhere()->sum('total_num');
        $data['payPrice'] = $paidWhere()->sum('pay_price');
        $data['cost'] = array_sum($this->costTrend($time, '%Y-%m-%d'));

        // 全额退款后 pay_status=3，不能再限定 pay_status=1，否则退款金额/件数恒为 0；按 refund_time 归属
        $data['refundPrice'] = $this->orderQuery()
            ->where('refund_status', OrderCenter::REFUND_SUCCESS)->where('is_del', 0)
            ->whereBetweenTime('refund_time', $time[0], $time[1])->sum('refund_price');
        $data['refund'] = (int)app()->make(AfterSale::class)
            ->where('product_type', 'normal')->where('is_del', 0)
            ->whereIn('status', ['refunded', 'completed'])
            ->whereBetweenTime('refund_time', $time[0], $time[1])->sum('refund_num');

        $payPeople = $paidWhere()->field('COUNT(DISTINCT uid) AS count')->find();
        $payPeople = (int)($payPeople['count'] ?? 0);
        $data['payPercent'] = $data['user'] > 0 ? round($payPeople / $data['user'] * 100, 2) : 0;
        return $data;
    }

    protected function trend(array $time, int $step, bool $excel = false)
    {
        if ($step === 0) {
            $xAxis = array_map(static fn($hour) => str_pad((string)$hour, 2, '0', STR_PAD_LEFT), range(0, 23));
            $timeType = '%H';
        } else {
            $xAxis = [];
            $cursor = strtotime($time[0]);
            $end = strtotime($time[1]);
            while ($cursor <= $end) {
                if ($step === 30) {
                    $xAxis[] = date('Y-m', $cursor);
                    $cursor = strtotime('+1 month', $cursor);
                    $timeType = '%Y-%m';
                } else {
                    $xAxis[] = date('m-d', $cursor);
                    $cursor = strtotime("+{$step} day", $cursor);
                    $timeType = '%m-%d';
                }
            }
        }

        $productIds = $this->productIds();
        $browse = $this->logTrend($productIds, $time, $timeType, 'SUM(visit_num)');
        $user = $this->logTrend($productIds, $time, $timeType, 'COUNT(DISTINCT uid)');
        $paid = [['pay_status', 'in', $this->paidStatuses()], ['is_del', '=', 0]];
        $refunded = [['refund_status', '=', OrderCenter::REFUND_SUCCESS], ['is_del', '=', 0]];
        $pay = $this->groupOrderTrend($paid, 'SUM(pay_price)', $time, $timeType, 'pay_time');
        $refund = $this->groupOrderTrend($refunded, 'SUM(refund_price)', $time, $timeType, 'refund_time');

        if (!$excel) {
            $data = [
                '商品浏览量' => [],
                '商品访客量' => [],
                '支付金额' => [],
                '退款金额' => [],
            ];
            foreach ($xAxis as $item) {
                $data['商品浏览量'][] = (float)($browse[$item] ?? 0);
                $data['商品访客量'][] = (float)($user[$item] ?? 0);
                $data['支付金额'][] = (float)($pay[$item] ?? 0);
                $data['退款金额'][] = (float)($refund[$item] ?? 0);
            }
            $series = [];
            foreach ($data as $name => $values) {
                $series[] = [
                    'name' => $name,
                    'data' => $values,
                    'type' => in_array($name, ['商品浏览量', '商品访客量'], true) ? 'line' : 'bar',
                    'smooth' => 'true',
                    'yAxisIndex' => in_array($name, ['商品浏览量', '商品访客量'], true) ? 1 : 0,
                ];
            }
            return compact('xAxis', 'series');
        }

        $cart = $this->cartTrend($productIds, $time, $timeType, 'SUM(cart_num)');
        $order = $this->groupOrderTrend([['is_del', '=', 0]], 'SUM(total_num)', $time, $timeType, 'pay_time');
        $payNum = $this->groupOrderTrend($paid, 'SUM(total_num)', $time, $timeType, 'pay_time');
        $payCount = $this->groupOrderTrend($paid, 'COUNT(*)', $time, $timeType, 'pay_time');
        $cost = $this->costTrend($time, $timeType);
        $refundNum = $this->groupAfterSaleTrend($time, $timeType, 'SUM(refund_num)');
        $rows = [];
        foreach ($xAxis as $item) {
            $changes = !empty($user[$item]) ? round(((float)($payCount[$item] ?? 0) / (float)$user[$item]) * 100, 2) : 0;
            $rows[] = [
                'time' => $item,
                'browse' => $browse[$item] ?? 0,
                'user' => $user[$item] ?? 0,
                'pay' => $pay[$item] ?? 0,
                'refund' => $refund[$item] ?? 0,
                'cart' => $cart[$item] ?? 0,
                'order' => $order[$item] ?? 0,
                'payNum' => $payNum[$item] ?? 0,
                'cost' => $cost[$item] ?? 0,
                'refundNum' => $refundNum[$item] ?? 0,
                'changes' => min($changes, 100),
            ];
        }
        /** @var ExportServices $exportService */
        $exportService = app()->make(ExportServices::class);
        return ['url' => $exportService->productTrade($rows)];
    }

    protected function productIds(): array
    {
        return array_map('intval', app()->make(CenterProductModel::class)->where('product_type', 'normal')->column('id'));
    }

    protected function productLogQuery(array $productIds)
    {
        $query = app()->make(StoreProductLog::class)->whereIn('product_id', $productIds ?: [0]);
        return $query;
    }

    protected function cartQuery(array $productIds)
    {
        return app()->make(StoreCart::class)->whereIn('product_id', $productIds ?: [0]);
    }

    protected function orderQuery()
    {
        return app()->make(OrderCenter::class)->where('product_type', 'normal');
    }

    /** @return int[] 已支付口径（含后续部分/全额退款） */
    protected function paidStatuses(): array
    {
        return [OrderCenter::PAY_PAID, OrderCenter::PAY_PARTIAL_REFUND, OrderCenter::PAY_FULL_REFUND];
    }

    protected function logTrend(array $productIds, array $time, string $timeType, string $expression): array
    {
        if (!$productIds) {
            return [];
        }
        return array_column($this->productLogQuery($productIds)->whereBetweenTime('add_time', $time[0], $time[1])
            ->field("FROM_UNIXTIME(add_time,'{$timeType}') AS days,{$expression} AS num")
            ->group('days')->select()->toArray(), 'num', 'days');
    }

    protected function cartTrend(array $productIds, array $time, string $timeType, string $expression): array
    {
        if (!$productIds) {
            return [];
        }
        return array_column($this->cartQuery($productIds)->whereBetweenTime('add_time', $time[0], $time[1])
            ->field("FROM_UNIXTIME(add_time,'{$timeType}') AS days,{$expression} AS num")
            ->group('days')->select()->toArray(), 'num', 'days');
    }

    protected function groupOrderTrend(array $where, string $expression, array $time, string $timeType, string $timeField = 'pay_time'): array
    {
        $query = $this->orderQuery()->where($where)->whereBetweenTime($timeField, $time[0], $time[1]);
        return array_column($query->field("FROM_UNIXTIME({$timeField},'{$timeType}') AS days,{$expression} AS num")
            ->group('days')->select()->toArray(), 'num', 'days');
    }

    protected function groupAfterSaleTrend(array $time, string $timeType, string $expression): array
    {
        return array_column(app()->make(AfterSale::class)
            ->where('product_type', 'normal')->where('is_del', 0)
            ->whereIn('status', ['refunded', 'completed'])
            ->whereBetweenTime('refund_time', $time[0], $time[1])
            ->field("FROM_UNIXTIME(refund_time,'{$timeType}') AS days,{$expression} AS num")
            ->group('days')->select()->toArray(), 'num', 'days');
    }

    protected function costTrend(array $time, string $timeType): array
    {
        $rows = $this->orderQuery()->whereIn('pay_status', $this->paidStatuses())->where('is_del', 0)
            ->whereBetweenTime('pay_time', $time[0], $time[1])
            ->field("FROM_UNIXTIME(pay_time,'{$timeType}') AS days,extra_info")
            ->select()->toArray();
        $result = [];
        foreach ($rows as $row) {
            $extra = $row['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            $dayCost = 0.0;
            foreach (($extra['items'] ?? []) as $item) {
                $dayCost += (float)($item['attr_info']['cost'] ?? 0) * (float)($item['cart_num'] ?? 0);
            }
            $result[$row['days']] = ($result[$row['days']] ?? 0) + $dayCost;
        }
        return $result;
    }

    protected function repeats(array $productIds, array $time, int $productId): int
    {
        return count($this->productLogQuery($productIds)->where('type', 'pay')->where('product_id', $productId)
            ->whereBetweenTime('add_time', $time[0], $time[1])->field('COUNT(pay_uid) AS p')->group('pay_uid')->having('p > 1')->select());
    }

    protected function parseTime(string $time): array
    {
        if (!str_contains($time, '-')) {
            throw new AdminException('请选择时间');
        }
        [$start, $end] = explode('-', $time, 2);
        if (!$start || !$end) {
            throw new AdminException('请选择时间');
        }
        return [$start, $end];
    }
}
