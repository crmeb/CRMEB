<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderLifecycleServices;
use think\facade\Log;

/**
 * 实物商品退款拒绝监听器
 * 管理员拒绝退款后，记录退款拒绝日志到订单生命周期
 */
class OrderRefundRefuseListener
{
    public function handle($event): void
    {
        [$orderRefundInfo] = $event;
        try {
            // 通过退款记录的 store_order_id 查询中台 product_type
            $storeOrderId = $orderRefundInfo['store_order_id'] ?? 0;
            if (!$storeOrderId) return;
            $centerDao = app(OrderCenterDao::class);
            $orderNo = $centerDao->getDb()->where('id', $storeOrderId)->value('order_no');
            if (!$orderNo) return;

            $meta = $centerDao->get(['order_no' => $orderNo], ['product_type']);
            if (!$meta || ($meta['product_type'] ?? '') !== 'normal') return;

            // 通用日志
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logRefundRefuse($orderRefundInfo);
        } catch (\Throwable $e) {
            Log::error('normal 退款拒绝监听失败:' . $e->getMessage());
        }
    }
}
