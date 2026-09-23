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
 * 实物商品发货通知监听器
 *
 * 监听 OrderDelivery 事件，通过 Notice API 发送发货通知。
 * 不再经过 NoticeSubscriber 事件系统中转。
 */
class OrderDeliveryNoticeListener
{
    /**
     * 处理订单发货事件
     * @param array $event [$orderInfo, $storeName, $data, $type]
     *        - orderInfo: 订单信息（含 order_id, uid, delivery_name, delivery_id）
     *        - storeName: 商品名称
     *        - data: 发货数据（含 delivery_name, delivery_id）
     *        - type: 发货类型（postage/express/deliver）
     */
    public function handle(array $event): void
    {
        [$orderInfo, $storeName, $data, $type] = $event + [1 => '', 2 => [], 3 => ''];
        if (!in_array($type, ['postage', 'deliver'], true)) return;

        try {
            $uid = (int)($orderInfo['uid'] ?? 0);
            if (!$uid) return;

            $deliveryName = $orderInfo['delivery_name'] ?? $data['delivery_name'] ?? '';
            $deliveryId   = $orderInfo['delivery_id'] ?? $data['delivery_id'] ?? '';
            $extraInfo = $orderInfo['extra_info'] ?? [];
            if (is_string($extraInfo)) {
                $extraInfo = json_decode($extraInfo, true) ?: [];
            }
            $phone = (string)($orderInfo['user_phone'] ?? ($extraInfo['contact']['phone'] ?? ''));

            // 根据发货类型选择通知模板：快递发货用 order_postage_success，配送送货用 order_deliver_success
            $mark = ($type === 'deliver') ? 'order_deliver_success' : 'order_postage_success';
            $title = ($type === 'deliver') ? '送货提醒' : '发货提醒';
            Log::info('[订单发货通知] 发送开始 ' . json_encode([
                'mark'      => $mark,
                'uid'       => $uid,
                'order_id'  => (string)($orderInfo['order_id'] ?? $orderInfo['order_no'] ?? ''),
                'delivery_name' => (string)$deliveryName,
                'delivery_id'   => (string)$deliveryId,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            // 扁平业务数据：is_system/is_sms 模板用 {nickname}/{store_name}/{order_id}/{delivery_name}/{delivery_id}；
            // is_wechat/is_routine 模板通过 key_list 做变量映射
            $results = Notice::to($uid)->sendMsg($mark, $title, [
                'order_id'      => (string)($orderInfo['order_id'] ?? ''),                                                // 订单号
                'store_name'    => (string)($storeName ?: ($orderInfo['title'] ?? '')),                                    // 商品名称
                'delivery_name' => (string)$deliveryName,                                                                  // 快递公司/配送员名称
                'delivery_id'   => (string)$deliveryId,                                                                    // 快递单号/配送单号
                'nickname'      => (string)($orderInfo['nickname'] ?? ''),                                                 // 收货人昵称
                'user_phone'    => $phone,                                                                                // 收货联系电话
                'link'          => '/pages/goods/order_details/index?order_id=' . ($orderInfo['order_id'] ?? ''),          // 订单详情页链接
            ]);
            // sendMsg 返回 [channel_key => ['success'=>bool,'error'=>string]]，空数组 = 没有渠道实际执行
            $succeeded = count(array_filter($results, fn($r) => !empty($r['success'])));
            if ($succeeded === 0) {
                Log::warning('[订单发货通知] 未成功投递 ' . json_encode([
                    'mark'      => $mark,
                    'uid'       => $uid,
                    'order_id'  => (string)($orderInfo['order_id'] ?? $orderInfo['order_no'] ?? ''),
                    'results'   => $results,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        } catch (\Throwable $e) {
            Log::error('[订单发货通知] 发送失败 ' . json_encode([
                'mark'      => $mark ?? 'unknown',
                'uid'       => $uid ?? 0,
                'order_id'  => (string)($orderInfo['order_id'] ?? $orderInfo['order_no'] ?? ''),
                'exception' => get_class($e),
                'error'     => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }
}
