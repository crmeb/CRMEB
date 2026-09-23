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
use think\facade\Log;

/**
 * 实物商品退款成功通知监听器
 *
 * 监听 OrderRefundSuccess 事件，通过 Notice API 发送退款成功通知。
 * 不再经过 NoticeSubscriber 事件系统中转。
 */
class OrderRefundSuccessNoticeListener
{
    /**
     * 处理退款成功事件
     * @param array $event [$order, $refundData]
     * @return void
     */
    public function handle(array $event): void
    {
        [$order, $refundData] = $event + [1 => []];
        if (($order['product_type'] ?? '') !== 'normal') return;

        $uid = (int)($order['uid'] ?? 0);
        $orderId = (string)($refundData['order_id'] ?? $order['order_id'] ?? '');

        try {
            if (!$uid) {
                Log::warning('[退款成功通知] 缺少用户ID ' . json_encode([
                    'order_id' => $orderId,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                return;
            }

            $title = $order['store_name'] ?? $order['title'] ?? '';
            $extraInfo = $order['extra_info'] ?? [];
            if (is_string($extraInfo)) {
                $extraInfo = json_decode($extraInfo, true) ?: [];
            }
            $phone = (string)($order['user_phone'] ?? ($extraInfo['contact']['phone'] ?? ''));

            // 场景：退款审核通过且款项退回后，通知用户退款结果
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            $results = Notice::to($uid)->sendMsg('order_refund', '退款成功', [
                'order_id' => (string)$orderId,                                                  // 订单号
                'pay_price' => (string)($order['pay_price'] ?? 0),                                 // 订单金额（元）
                'refund_price' => (string)($refundData['refund_price'] ?? $order['refund_price'] ?? $order['refunded_price'] ?? 0),  // 实退金额（元）
                'refund_time' => date('Y-m-d H:i:s'),  // 退款时间
                'refund_status' => '退款成功',  //退款状态
                'store_name' => (string)$title,                                                     // 商品名称
                'user_phone' => (string)$phone,                                                      // 联系电话
                'link' => '/pages/center/order/detail/index?order_no=' . rawurlencode((string)$orderId), // 订单详情页链接
            ]);
            // sendMsg 返回 [channel_key => ['success'=>bool,'error'=>string]]，空数组 = 没有渠道实际执行
            $succeeded = count(array_filter($results, fn($r) => !empty($r['success'])));
            if ($succeeded === 0) {
                Log::warning('[退款成功通知] 未成功投递 ' . json_encode([
                    'mark'      => 'order_refund',
                    'uid'       => $uid,
                    'order_id'  => (string)$orderId,
                    'results'   => $results,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                throw new \RuntimeException('退款成功通知提交失败');
            }
        } catch (\Throwable $e) {
            Log::error('[退款成功通知] 发送异常 ' . json_encode([
                'mark'      => 'order_refund',
                'uid'       => $uid,
                'order_id'  => $orderId,
                'exception' => get_class($e),
                'error'     => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            // 待办发送器负责隔离失败并重试，不能在监听器内吞掉失败。
            throw $e;
        }
    }
}
