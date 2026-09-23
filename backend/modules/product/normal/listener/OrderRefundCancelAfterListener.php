<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderLifecycleServices;
use think\facade\Log;

/**
 * 实物商品退款取消监听器
 * 用户/管理员取消退款后，记录退款取消日志到订单生命周期
 */
class OrderRefundCancelAfterListener
{
    public function handle($event): void
    {
        [$orderRefundInfo] = $event;
        try {
            $storeOrderId = $orderRefundInfo['store_order_id'] ?? 0;
            if (!$storeOrderId) return;
            $centerDao = app(OrderCenterDao::class);
            $orderNo = $centerDao->getDb()->where('id', $storeOrderId)->value('order_no');
            if (!$orderNo) return;

            $meta = $centerDao->get(['order_no' => $orderNo], ['product_type']);
            if (!$meta || ($meta['product_type'] ?? '') !== 'normal') return;

            // 通用日志
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logRefundCancel($orderRefundInfo);
        } catch (\Throwable $e) {
            Log::error('normal 退款取消监听失败:' . $e->getMessage());
        }
    }
}
