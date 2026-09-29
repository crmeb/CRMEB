<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\listener;

use modules\product\normal\dao\NormalOrderDao;
use think\facade\Log;

/**
 * 订单中心列表扩展监听器
 * 补充实物商品订单的配送扩展字段
 * 兼容旧数据：新订单在创建时已在 extra_info 中存储 delivery 信息，
 * 旧订单需从此监听器从扩展表 eb_pt_normal_order 读取补充
 */
class OrderCenterListExtendListener
{
    /**
     * 处理列表扩展
     * @param array $eventData ['list' => &$list]
     */
    public function handle($eventData): void
    {
        $list = &$eventData['list'];
        if (empty($list)) {
            return;
        }

        // 筛选出 normal 类型的订单，收集缺少配送信息的订单号
        $missingOrderNos = [];
        foreach ($list as $item) {
            if (($item['source_type'] ?? '') !== 'normal_order') {
                continue;
            }
            $extraInfo = $item['extra_info'] ?? [];
            if (is_string($extraInfo)) {
                $extraInfo = json_decode($extraInfo, true) ?: [];
            }
            $deliveryInfo = $extraInfo['delivery'] ?? [];
            // 统一检查 nested delivery 格式，任一核心字段缺失都从扩展表回填
            $hasDeliveryInfo = !empty($deliveryInfo['delivery_type'])
                && !empty($deliveryInfo['delivery_name'])
                && !empty($deliveryInfo['delivery_id']);
            if (!$hasDeliveryInfo) {
                $missingOrderNos[] = $item['order_no'] ?? '';
            }
        }
        $missingOrderNos = array_filter(array_unique($missingOrderNos));

        // 批量查询扩展表获取配送信息（仅存在缺失时查询；查询失败不阻断归一化，仍用 extra_info/中台数据兜底）
        $mappedExtDeliveries = [];
        if (!empty($missingOrderNos)) {
            try {
                /** @var NormalOrderDao $orderDao */
                $orderDao = app(NormalOrderDao::class);
                $extDeliveryMap = $orderDao->getDb()
                    ->whereIn('order_no', $missingOrderNos)
                    ->field('order_no, delivery_name, delivery_id, delivery_type')
                    ->select()
                    ->toArray();
                foreach ($extDeliveryMap as $row) {
                    $orderNo = (string)($row['order_no'] ?? '');
                    if ($orderNo === '') {
                        continue;
                    }
                    $mappedExtDeliveries[$orderNo] = [
                        'delivery_name' => (string)($row['delivery_name'] ?? ''),
                        'delivery_id'   => (string)($row['delivery_id'] ?? ''),
                        'delivery_type' => (string)($row['delivery_type'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('[Normal] OrderCenterListExtend 读取扩展表配送信息失败: ' . $e->getMessage());
            }
        }

        // 回填到列表数据（统一顶层 + nested 格式），对所有 normal 订单执行，
        // 保证顶层始终存在 delivery_type/delivery_name/delivery_id，前端列表按顶层字段渲染；
        // 即使本页全部订单配送信息完整，也不能跳过本循环，否则前端会把已发货订单误判为未发货
        foreach ($list as &$item) {
            if (($item['source_type'] ?? '') !== 'normal_order') {
                continue;
            }
            $orderNo = $item['order_no'] ?? '';
            $extraInfo = $item['extra_info'] ?? [];
            if (!is_array($extraInfo)) {
                $extraInfo = [];
            }
            $delivery = $extraInfo['delivery'] ?? [];
            $extDelivery = $mappedExtDeliveries[$orderNo] ?? [];

            $deliveryType = (string)($delivery['delivery_type'] ?? ($extraInfo['delivery_type'] ?? ''));
            if ($deliveryType === '') {
                $deliveryType = (string)($extDelivery['delivery_type'] ?? '');
            }
            if ($deliveryType === '') {
                $shippingType = (int)($item['shipping_type'] ?? 0);
                $deliveryType = $shippingType === 2 ? 'pickup' : ($shippingType === 3 ? 'door' : 'express');
            }

            $deliveryName = (string)($delivery['delivery_name'] ?? ($extDelivery['delivery_name'] ?? ''));
            $deliveryId = (string)($delivery['delivery_id'] ?? ($extDelivery['delivery_id'] ?? ''));

            $item['delivery_type'] = $deliveryType;
            $item['delivery_name'] = $deliveryName;
            $item['delivery_id'] = $deliveryId;

            $extraInfo['delivery_type'] = $deliveryType;
            $extraInfo['delivery'] = array_merge($delivery, [
                'delivery_type' => $deliveryType,
                'delivery_name' => $deliveryName,
                'delivery_id'   => $deliveryId,
            ]);
            $item['extra_info'] = $extraInfo;
        }
        unset($item);
    }
}
