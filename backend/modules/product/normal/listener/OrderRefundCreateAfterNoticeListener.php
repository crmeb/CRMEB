<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\services\message\Notice;

/**
 * 实物商品退款申请通知监听器
 * 监听 OrderRefundCreateAfter 事件，通过 Notice API 发送退款申请通知。
 */
class OrderRefundCreateAfterNoticeListener
{
    public function handle(array $event): void
    {
        $data = $event[0] ?? [];

        try {
            $uid = (int)($data['uid'] ?? 0);
            if (!$uid) return;

            // 从 refundData 中获取退款信息
            $refundData = $event[1] ?? [];

            // 场景：用户提交退款/售后申请后，同步提醒平台方及时处理售后
            Notice::to(0)->sendMsg('order_refund_create', '用户申请退款客服提醒', [
                'order_id'     => (string)($data['order_id'] ?? $refundData['order_id'] ?? ''),    // 订单号
                'pay_price'    => (string)($data['pay_price'] ?? 0),                                // 订单金额（元）
                'refund_price' => (string)($data['refund_price'] ?? $data['refunded_price'] ?? 0),  // 申请退款金额（元）
            ]);

            // 场景：用户提交退款/售后申请后，确认申请已受理
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            Notice::to($uid)->sendMsg('order_refund_create', '退款申请已提交', [
                'order_id'     => (string)($data['order_id'] ?? $refundData['order_id'] ?? ''),           // 订单号
                'pay_price'    => (string)($data['pay_price'] ?? 0),                                       // 订单金额（元）
                'refund_price' => (string)($data['refund_price'] ?? $data['refunded_price'] ?? 0),         // 申请退款金额（元）
            ]);
        } catch (\Throwable $e) {
        }
    }
}
