<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderLifecycleServices;
use modules\product\normal\handler\NormalOrderAdapter;
use think\facade\Log;

/**
 * 实物商品评价完成监听器
 * 用户评价完成后，记录评价日志到订单生命周期
 */
class OrderCommentListener
{
    public function handle($event): void
    {
        [$replyInfo, $order] = $event;
        try {
            $orderNo = $order['order_id'] ?? '';
            if (!$orderNo) return;

            $centerDao = app(OrderCenterDao::class);
            $meta = $centerDao->get(['order_no' => $orderNo], ['product_type']);
            if (!$meta || ($meta['product_type'] ?? '') !== 'normal') return;

            // 通用日志
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logComment($replyInfo, $order);
        } catch (\Throwable $e) {
            Log::error('normal 评价监听失败:' . $e->getMessage());
        }
    }
}
