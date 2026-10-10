<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\listener;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 订单中台详情扩展监听器（实物商品）
 * 监听 OrderCenterDetailExtend 事件，将实物订单扩展独有字段（contact/delivery/service）
 * 从 extra_info 提取到顶层，供前端统一渲染。
 *
 * 迁移自 app/services/order/OrderCenterServices::getDetail()，
 * 落实"中台层不处理扩展独有字段"铁律，由 normal 模块自行处理。
 */
class OrderCenterDetailExtendListener implements ListenerInterface
{
    /**
     * 处理订单中台详情扩展事件
     * @param mixed $event ['detail' => &array, 'source_type' => string, 'source_id' => int]
     * @return void
     */
    public function handle($event): void
    {
        $detail = &$event['detail'];
        if (empty($detail)) {
            return;
        }

        // 仅处理实物商品订单，其他产品类型由各自的监听器负责
        if (($detail['source_type'] ?? '') !== 'normal_order') {
            return;
        }

        // 异常容错：单个监听器失败不影响订单详情整体渲染
        try {
            $extraInfo = &$detail['extra_info'];
            if (!is_array($extraInfo)) {
                $extraInfo = [];
            }

            // 从 extra_info 提取联系信息到顶层，方便前端统一渲染
            if (isset($extraInfo['contact'])) {
                $detail['contact'] = $extraInfo['contact'];
                unset($extraInfo['contact']);

                // 将 contact.address 对象格式化为字符串，赋值给 user_address
                if (isset($detail['contact']['address']) && is_array($detail['contact']['address'])) {
                    $addr = $detail['contact']['address'];
                    $detail['user_address'] = implode('', array_filter([
                        $addr['province'] ?? '',
                        $addr['city'] ?? '',
                        $addr['district'] ?? '',
                        $addr['detail'] ?? '',
                    ]));
                }
            }

            // 从 extra_info.delivery 回退配送信息到顶层，方便物流查询等场景统一访问
            // 保留 extra_info.delivery 以保持向后兼容（用户端 express() 等方法仍从此读取）
            if (isset($extraInfo['delivery'])) {
                $delivery = $extraInfo['delivery'];
                if (empty($detail['delivery_name'])) {
                    $detail['delivery_name'] = $delivery['delivery_name'] ?? '';
                }
                if (empty($detail['delivery_id'])) {
                    $detail['delivery_id'] = $delivery['delivery_id'] ?? '';
                }
                if (empty($detail['delivery_type'])) {
                    $detail['delivery_type'] = $delivery['delivery_type'] ?? '';
                }
                if (empty($detail['delivery_code'])) {
                    $detail['delivery_code'] = $delivery['delivery_code'] ?? '';
                }
                if (empty($detail['delivery_uid'])) {
                    $detail['delivery_uid'] = (int)($delivery['delivery_uid'] ?? 0);
                }
            }

            $deliveryType = (string)($extraInfo['delivery_type'] ?? ($extraInfo['delivery']['delivery_type'] ?? ''));
            if ($deliveryType === '') {
                $shippingType = (int)($detail['shipping_type'] ?? 0);
                $deliveryType = $shippingType === 2 ? 'pickup' : ($shippingType === 3 ? 'door' : 'express');
            }
            $extraInfo['delivery_type'] = $deliveryType;
            $extraInfo['delivery'] = array_merge($extraInfo['delivery'] ?? [], [
                'delivery_type' => $deliveryType,
            ]);
            $detail['delivery_type'] = $deliveryType;

            // 前端统一的到店自提/上门服务标识（与中台保持一致计算口径）
            $isPickup = ($detail['shipping_type'] ?? 0) == 2;
            $isDoorService = ($detail['shipping_type'] ?? 0) == 3;

            // 上门服务订单：从 extra_info 提取预约信息到顶层，供详情页展示
            if ($isDoorService && isset($extraInfo['service'])) {
                $serviceInfo = $extraInfo['service'];
                $detail['service'] = $serviceInfo;
                unset($extraInfo['service']);

                // 自声明渲染 section（中台不再硬编码插件 section，数据生产方自声明）
                $detail['extend_sections']['service'] = [
                    'component' => 'service_appointment',
                    'props'     => ['service' => $serviceInfo],
                    'visible'   => true,
                    'order'     => 118,
                ];
            }

            // 到店自提/上门服务订单清除 contact（避免前端误判显示收货信息）
            if ($isPickup || $isDoorService) {
                unset($detail['contact']);
            }
        } catch (\Throwable $e) {
            Log::error('[Normal] OrderCenterDetailExtend 异常: ' . $e->getMessage());
        }
    }
}
