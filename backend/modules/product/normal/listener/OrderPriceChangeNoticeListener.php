<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\services\message\Notice;

/**
 * 实物商品改价通知监听器
 * 监听 OrderPriceChange 事件，通过 Notice API 发送改价通知。
 */
class OrderPriceChangeNoticeListener
{
    public function handle(array $event): void
    {
        $order = $event[0] ?? [];

        try {
            $uid = (int)($order['uid'] ?? 0);
            if (!$uid) return;

            $extraInfo = $order['extra_info'] ?? [];
            if (is_string($extraInfo)) {
                $extraInfo = json_decode($extraInfo, true) ?: [];
            }
            $phone = (string)($order['user_phone'] ?? ($extraInfo['contact']['phone'] ?? ''));

            // 场景：管理员对未支付订单调整价格后，提醒用户按新价格支付
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            Notice::to($uid)->sendMsg('price_revision', '订单改价', [
                'order_id'     => (string)($order['order_id'] ?? ''), // 订单号
                'pay_price'    => (string)($order['pay_price'] ?? 0), // 改后应付金额（元）
                'change_price' => (string)($order['change_price'] ?? 0), // 改价差额（元）
                'user_phone'   => (string)$phone, // 收货联系电话
            ]);
        } catch (\Throwable $e) {
        }
    }
}
