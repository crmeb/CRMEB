<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace modules\product\normal\listener;

use app\services\order\OrderLifecycleServices;
use modules\product\normal\handler\NormalOrderAdapter;
use modules\product\normal\dao\NormalOrderDao;
use think\facade\Log;

/**
 * 实物商品发货监听器
 * 监听 OrderDelivery 事件，推进扩展表状态为待收货
 */
class OrderDeliveryListener
{
    public function handle($event): void
    {
        [$orderInfo, $storeName, $data, $type] = $event;
        try {
            $orderNo = $orderInfo['order_no'] ?? ($orderInfo['order_id'] ?? '');
            if (!$orderNo) {
                return;
            }

            // 幂等检查：仅当扩展表状态为待发货时才推进
            /** @var NormalOrderDao $orderDao */
            $orderDao = app(NormalOrderDao::class);
            $exist = $orderDao->getOne(['order_no' => $orderNo], 'status');
            $statusVal = $exist['status'] ?? null;
            if (!$exist || $statusVal !== NormalOrderAdapter::EXT_STATUS_PENDING_SHIPPED) {
                return;
            }

            // 1. 通用生命周期处理（写日志、同步物流信息到中台 extra_info.delivery）
            $lifecycleServices = app(OrderLifecycleServices::class);
            $orderArr = is_object($orderInfo) ? $orderInfo->toArray() : (array)$orderInfo;
            $lifecycleServices->handleDelivery($orderArr, $data, $type);

            // 2. 推进扩展表状态：待发货 → 待收货（自动同步中台 + business_status）
            NormalOrderAdapter::instance()->advance($orderNo, NormalOrderAdapter::EXT_STATUS_PENDING_RECEIVE);
        } catch (\Throwable $e) {
            Log::error('normal 发货后同步状态失败:' . $e->getMessage());
        }
    }
}
