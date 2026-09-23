<?php
declare(strict_types=1);

namespace app\listener\user;

use app\services\message\Notice;

/**
 * 用户积分变更通知监听器
 * 监听 UserIntegralChange 事件，通过 Notice API 发送积分变更通知。
 */
class UserIntegralChangeNoticeListener
{
    public function handle(array $event): void
    {
        // 兼容两种调用格式
        $uid = $event[0] ?? 0;
        $second = $event[1] ?? null;

        try {
            $uid = (int)$uid;
            if (!$uid) return;

            // 判断调用格式：
            // OrderTakeServices 传 ($uid, $order, $storeTitle, $giveIntegral, $integral)
            // UserBillServices 传 ($uid, $isIncrease(bool), $number, $balance)
            $isOrderFormat = is_array($second) && isset($second['order_id']);

            if ($isOrderFormat) {
                // 订单收货返积分：$event[3]=赠送积分, $event[4]=当前积分
                $giveIntegral = $event[3] ?? 0;
                $integral = $event[4] ?? 0;
                $storeTitle = $event[2] ?? '';
                $orderId = $second['order_id'] ?? '';
            } else {
                // 通用积分变动：$event[2]=变动数量, $event[3]=变动后余额
                $giveIntegral = $event[2] ?? 0;
                $integral = $event[3] ?? 0;
                $storeTitle = '';
                $orderId = '';
            }

            // 场景：订单收货返积分、签到/消费奖励等积分变动时，通知用户
            // 扁平业务数据：各渠道模板直接用业务变量名（如 {give_integral}）
            // 微信渠道（公众号/小程序）需在渠道配置 key_list 中声明 [微信变量名 => 业务变量名] 映射
            // 新增渠道无需改本监听器，只需在后台配置对应模板即可
            Notice::to($uid)->sendMsg('user_integral_change', '积分变更', [
                'give_integral' => (string)$giveIntegral, // 变动积分（正为获得，负为扣减）
                'integral'      => (string)$integral,     // 变动后剩余积分
                'order_id'      => (string)$orderId,      // 关联订单号（通用变动时为空）
                'store_title'   => $storeTitle ?: '积分奖励', // 来源商品/事项名称
                'link'          => $orderId ? '/pages/goods/order_details/index?order_id=' . $orderId : '/pages/users/user_bill/index', // 跳转链接
            ]);
        } catch (\Throwable $e) {
        }
    }
}
