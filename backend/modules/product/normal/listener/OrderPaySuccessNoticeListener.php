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
 * 实物商品支付成功通知监听器
 *
 * 监听 OrderPaySuccess 事件，通过 Notice API 发送支付成功通知。
 * 不再经过 NoticeSubscriber 事件系统中转。
 */
class OrderPaySuccessNoticeListener
{
    /**
     * 处理订单支付成功事件
     * @param array $event [$orderInfo]
     */
    public function handle(array $event): void
    {
        [$orderInfo] = $event;

        try {
            $uid = (int)($orderInfo['uid'] ?? 0);
            if (!$uid) return;

            $extraInfo = $orderInfo['extra_info'] ?? [];
            if (is_string($extraInfo)) {
                $extraInfo = json_decode($extraInfo, true) ?: [];
            }
            $phone = (string)($orderInfo['user_phone'] ?? ($extraInfo['contact']['phone'] ?? ''));

            // 场景：订单支付成功后，同步提醒平台方跟进发货
            Notice::to(0)->sendMsg('order_pay_success', '新订单支付客服提醒', [
                'order_id' => (string)($orderInfo['order_id'] ?? ''),   // 订单号
                'pay_price' => (string)($orderInfo['pay_price'] ?? 0),    // 支付金额（元）
                'total_num' => (string)($orderInfo['total_num'] ?? 1),    // 商品总数量
            ]);

            // 场景：订单支付成功后，通知用户查看订单详情
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            Notice::to($uid)->sendMsg('order_pay_success', '支付成功', [
                'order_id' => (string)($orderInfo['order_id'] ?? ''),                                      // 订单号
                'pay_price' => (string)($orderInfo['pay_price'] ?? 0),                                       // 支付金额（元）
                'total_num' => (string)($orderInfo['total_num'] ?? 1),                                       // 商品总数量
                'store_name' => (string)($orderInfo['storeName'] ?? $orderInfo['title'] ?? ''),                               // 商店名称
                'order_time' => date('Y-m-d H:i:s'),// 订单时间
                'user_phone' => $phone,                                                                  // 收货联系电话
                'link' => '/pages/goods/order_details/index?order_id=' . ($orderInfo['order_id'] ?? ''), // 订单详情页链接
            ]);
        } catch (\Throwable $e) {
            // 通知发送失败不影响主流程
            return;
        }
    }
}
