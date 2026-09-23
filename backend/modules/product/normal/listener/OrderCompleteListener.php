<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace modules\product\normal\listener;

use app\services\order\OrderCenterServices;
use app\services\order\OrderLifecycleServices;
use modules\product\normal\handler\NormalOrderAdapter;
use modules\product\normal\dao\NormalOrderDao;
use think\facade\Log;

/**
 * 实物商品订单完成监听器
 * 监听 OrderComplete 事件，推进扩展表状态为已完成
 */
class OrderCompleteListener
{
    public function handle($event): void
    {
        [$order] = $event;
        try {
            $orderNo = $order['order_id'] ?? '';
            if (!$orderNo) {
                return;
            }

            // 幂等检查：仅待评价状态才推进
            /** @var NormalOrderDao $orderDao */
            $orderDao = app(NormalOrderDao::class);
            $exist = $orderDao->getOne(['order_no' => $orderNo], 'status');
            if (!$exist || ($exist['status'] ?? '') !== NormalOrderAdapter::EXT_STATUS_PENDING_REVIEW) {
                return;
            }

            // 1. 同步中台状态为 completed
            $centerServices = app(OrderCenterServices::class);
            $centerServices->syncStatus($orderNo, 'completed');

            // 2. 通用生命周期日志
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logComplete($order);

            // 3. 推进扩展表状态：待评价 → 已完成
            NormalOrderAdapter::instance()->advance($orderNo, NormalOrderAdapter::EXT_STATUS_COMPLETED);
        } catch (\Throwable $e) {
            Log::error('normal 完成后同步状态失败:' . $e->getMessage());
        }
    }
}
