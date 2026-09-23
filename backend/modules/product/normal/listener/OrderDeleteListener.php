<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderLifecycleServices;
use modules\product\normal\handler\NormalOrderAdapter;
use think\facade\Log;

/**
 * 实物商品删除订单监听器
 * 订单删除（逻辑删除）后，记录删除日志到订单生命周期
 */
class OrderDeleteListener
{
    public function handle($event): void
    {
        [$order, $deleteType] = $event;
        try {
            $orderNo = $order['order_id'] ?? '';
            if (!$orderNo) return;

            $centerDao = app(OrderCenterDao::class);
            $meta = $centerDao->get(['order_no' => $orderNo], ['product_type']);
            if (!$meta || ($meta['product_type'] ?? '') !== 'normal') return;

            // 通用日志
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logDelete($order, $deleteType);
        } catch (\Throwable $e) {
            Log::error('normal 删除订单监听失败:' . $e->getMessage());
        }
    }
}
