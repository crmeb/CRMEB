<?php
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\services\message\Notice;
use think\facade\Log;

/**
 * 实物商品下单成功通知监听器
 * 监听 OrderCreateAfter 事件，通过 Notice API 发送下单成功通知。
 */
class OrderCreateAfterNoticeListener
{
    public function handle(array $event): void
    {
        // 兼容多种调用格式：[$order, ...] 或 ['order_no' => ..., 'order_id' => ...]
        $first = isset($event['order_no']) ? $event : ($event[0] ?? []);
        // 旧格式可能传入 Model 对象，需转换为数组
        if (is_object($first) && method_exists($first, 'toArray')) {
            $first = $first->toArray();
        }
        if (!is_array($first)) {
            Log::warning('[短信诊断] 下单通知事件数据不是数组');
            return;
        }

        $uid = (int)($first['uid'] ?? 0);
        if (!$uid) {
            Log::warning('[短信诊断] 下单通知缺少用户ID');
            return;
        }

        $phone = (string)($first['user_phone'] ?? '');
        Log::info('[短信诊断] 下单通知准备发送 ' . json_encode([
            'uid' => $uid,
            'order_no_present' => !empty($first['order_no']),
            'event_keys' => array_keys($first),
            'phone_present' => $phone !== '',
            'phone_tail' => $phone !== '' ? substr($phone, -4) : '',
            'phone_source' => $phone !== '' ? 'event' : 'missing_before_channel_fallback',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // 场景：用户提交订单尚未支付时，告知下单成功并引导完成付款
        // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
        Notice::to($uid)->sendMsg('order_create', '下单成功', [
            'order_id'   => (string)($first['order_no'] ?? ''),                                     // 订单号
            'pay_price'  => (string)($first['pay_price'] ?? $first['price_data']['pay_price'] ?? 0), // 应付金额（元）
            'store_name' => (string)($first['storeName'] ?? $first['title'] ?? ''),                  // 商品名称
            'user_phone' => $phone,                                                                        // 收货联系电话
            'order_time' => date('Y-m-d H:i:s'), // 下单时间
            'order_type' => '实物商品', // 下单时间
        ]);
    }
}
