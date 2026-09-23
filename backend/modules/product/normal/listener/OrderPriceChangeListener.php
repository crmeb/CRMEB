<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderLifecycleServices;
use modules\product\normal\handler\NormalOrderAdapter;
use think\facade\Log;

/**
 * 实物商品改价监听器
 * 管理员修改订单价格后，记录改价日志到订单生命周期
 */
class OrderPriceChangeListener
{
    public function handle($event): void
    {
        [$order, $payPrice] = $event;
        try {
            $orderNo = $order['order_id'] ?? '';
            if (!$orderNo) return;

            $centerDao = app(OrderCenterDao::class);
            $meta = $centerDao->get(['order_no' => $orderNo], ['product_type']);
            if (!$meta || ($meta['product_type'] ?? '') !== 'normal') return;

            // 通用日志
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logPriceChange($order, $payPrice);
        } catch (\Throwable $e) {
            Log::error('normal 改价监听失败:' . $e->getMessage());
        }
    }
}
