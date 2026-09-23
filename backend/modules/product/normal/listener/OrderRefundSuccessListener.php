<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\services\order\OrderLifecycleServices;
use app\services\order\RefundEffectServices;
use think\facade\Log;

/**
 * 实物商品退款成功监听器
 * 监听 OrderRefundSuccess 事件，记录退款成功日志。
 * 退款状态、扩展表和订单中台更新由 RefundEffectServices 统一完成，避免重复写入。
 */
class OrderRefundSuccessListener
{
    public function handle($event): void
    {
        [$order, $refundData] = $event;
        if (($order['product_type'] ?? '') !== 'normal') {
            return;
        }
        try {
            $orderNo = $order['order_id'] ?? '';
            if (!$orderNo) return;

            // 通用日志
            $lifecycleServices = app(OrderLifecycleServices::class);
            $refundId = (int)($refundData['refund_order_id'] ?? 0);
            if ($refundId > 0) {
                app(RefundEffectServices::class)->recordSuccessLog($refundId,
                    fn () => $lifecycleServices->logRefundSuccess($order, $refundData));
            } else {
                $lifecycleServices->logRefundSuccess($order, $refundData);
            }

        } catch (\Throwable $e) {
            Log::error('normal 退款成功监听失败:' . $e->getMessage());
            throw $e;
        }
    }
}
