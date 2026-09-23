<?php
declare(strict_types=1);

namespace app\listener\order;

use app\services\message\Notice;

/**
 * 未支付订单提醒通知监听器
 * 监听 UnpaidOrderNotice 事件，通过 Notice API 发送未支付提醒通知。
 */
class UnpaidOrderNoticeListener
{
    public function handle(array $event): void
    {
        $orderInfo = $event[0] ?? [];

        try {
            $uid = (int)($orderInfo['uid'] ?? 0);
            if (!$uid) return;

            // 场景：订单创建后超时未支付，提醒用户尽快完成付款
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            Notice::to($uid)->sendMsg('order_pay_false', '未支付订单提醒', [
                'order_id'  => (string)($orderInfo['order_id'] ?? ''),   // 订单号
                'pay_price' => (string)($orderInfo['pay_price'] ?? 0),   // 支付金额（元）
                'link'      => '/pages/goods/order_details/index?order_id=' . ($orderInfo['order_id'] ?? ''), // 订单详情页链接
            ]);
        } catch (\Throwable $e) {
        }
    }
}
