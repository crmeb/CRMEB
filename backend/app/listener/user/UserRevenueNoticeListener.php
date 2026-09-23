<?php
declare(strict_types=1);

namespace app\listener\user;

use app\services\message\Notice;

/**
 * 用户收益通知监听器
 * 监听 UserRevenue 事件，通过 Notice API 发送收益通知。
 */
class UserRevenueNoticeListener
{
    public function handle(array $event): void
    {
        [$uid, $orderId, $amount, $status] = $event + [1 => '', 2 => 0, 3 => 0];

        try {
            $uid = (int)$uid;
            if (!$uid) return;

            // 场景：分销佣金返现等收益到账时，通知用户查收
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            Notice::to($uid)->sendMsg('revenue_received', '收益通知', [
                'amount'   => (string)($amount ?: 0),              // 收益金额（元）
                'order_id' => (string)$orderId,                     // 关联订单号
                'type'     => (string)($status ?: '收益到账'),      // 收益说明
                'link'     => '/pages/users/user_bill/index',       // 账单明细页链接
            ]);
        } catch (\Throwable $e) {
        }
    }
}
