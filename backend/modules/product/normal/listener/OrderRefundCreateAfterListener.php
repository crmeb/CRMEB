<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderLifecycleServices;
use think\facade\Log;

/**
 * 实物商品退款单创建监听器
 * 退款申请提交后，记录退款创建日志到订单生命周期
 */
class OrderRefundCreateAfterListener
{
    public function handle($event): void
    {
        [$order] = $event;
        try {
            $orderNo = $order['order_id'] ?? '';
            if (!$orderNo) return;

            $centerDao = app(OrderCenterDao::class);
            $meta = $centerDao->get(['order_no' => $orderNo], ['product_type', 'status_code']);
            if (!$meta || ($meta['product_type'] ?? '') !== 'normal') return;

            // 通用日志
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logRefundCreate($order);
        } catch (\Throwable $e) {
            Log::error('normal 退款申请监听失败:' . $e->getMessage());
        }
    }
}
