<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\listener;

use app\services\message\Notice;

/**
 * 实物商品确认收货通知监听器
 *
 * 监听 OrderTake 事件，通过 Notice API 发送确认收货通知。
 * 不再经过 NoticeSubscriber 事件系统中转。
 */
class OrderTakeNoticeListener
{
    /**
     * 处理确认收货事件
     * @param array $event [$order, $userInfo, $storeTitle]
     */
    public function handle(array $event): void
    {
        $order = $event[0] ?? [];

        try {
            $uid = (int)($order['uid'] ?? 0);
            if (!$uid) return;

            $title = $event[2] ?? $order['title'] ?? $order['storeName'] ?? '';
            $extraInfo = $order['extra_info'] ?? [];
            if (is_string($extraInfo)) {
                $extraInfo = json_decode($extraInfo, true) ?: [];
            }
            $phone = (string)($order['user_phone'] ?? ($extraInfo['contact']['phone'] ?? ''));

            // 场景：用户确认收货（含自动收货）后，同步提醒平台方交易已完成
            Notice::to(0)->sendMsg('order_take', '确认收货客服提醒', [
                'order_id'   => (string)($order['order_id'] ?? ''),   // 订单号
                'store_name' => (string)$title,                        // 店铺/商品名称
                'pay_price'  => (string)($order['pay_price'] ?? 0),    // 订单金额（元）
            ]);

            // 场景：用户确认收货（含自动收货）后，通知交易完成
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            Notice::to($uid)->sendMsg('order_take', '确认收货', [
                'order_id'   => (string)($order['order_id'] ?? ''),                                             // 订单号
                'store_name' => (string)$title,                                                                  // 店铺/商品名称
                'pay_price'  => (string)($order['pay_price'] ?? 0),                                              // 订单金额（元）
                'user_phone' => $phone,                                                                        // 收货联系电话
                'link'       => '/pages/goods/order_details/index?order_id=' . ($order['order_id'] ?? ''),       // 订单详情页链接
            ]);
        } catch (\Throwable $e) {
            // 通知发送失败不影响主流程
        }
    }
}
