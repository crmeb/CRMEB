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
use app\services\order\OrderCenterServices;
use app\dao\order\OrderCenterDao;
use app\model\order\OrderCenter;
use modules\product\normal\handler\NormalOrderAdapter;
use modules\product\normal\dao\NormalOrderDao;
use think\facade\Log;

/**
 * 实物商品收货监听器
 * 监听 OrderTake 事件，根据是否需要安装分流：
 * - 无需安装 → 推进到待评价
 * - 需安装 → 推进到待安装（自动创建服务履约记录）
 */
class OrderTakeListener
{
    /**
     * 串行推进收货状态，不覆盖并发完成的退款终态。
     * @param array $event 收货事件
     * @return void
     */
    public function handle($event): void
    {
        [$order] = $event;
        try {
            $orderNo = $order['order_id'] ?? '';
            if (!$orderNo) {
                return;
            }

            app(OrderCenterServices::class)->transaction(function () use ($orderNo, $order): void {
                $center = app(OrderCenterDao::class)->getByOrderNoForUpdate($orderNo);
                if (!$center || ($center['product_type'] ?? '') !== 'normal'
                    || !in_array((int)$center['pay_status'], [OrderCenter::PAY_PAID, OrderCenter::PAY_PARTIAL_REFUND], true)) {
                    return;
                }
                $order = array_replace($order, $center->toArray());
                // 判断是否需要安装，推进到不同扩展状态。
                /** @var NormalOrderDao $orderDao */
                $orderDao = app(NormalOrderDao::class);
                $extOrder = $orderDao->getOne(['order_no' => $orderNo]);
                if (!$extOrder || ($extOrder['status'] ?? '') !== NormalOrderAdapter::EXT_STATUS_PENDING_RECEIVE) {
                    return;
                }

                $hasInstallation = (int)($extOrder['has_installation'] ?? 0);
                $logToStatus = $hasInstallation ? OrderCenter::STATUS_PROCESSING : OrderCenter::STATUS_PENDING_REVIEW;
                $logContent = $hasInstallation ? '已收货，待安装' : '已收货，待评价';

                $lifecycleServices = app(OrderLifecycleServices::class);
                $lifecycleServices->handleTake($order, $logToStatus, $logContent);

                if ($hasInstallation) {
                    // 需安装的 on_enter 回调同时创建服务履约记录。
                    NormalOrderAdapter::instance()->advance($orderNo, NormalOrderAdapter::EXT_STATUS_PENDING_INSTALL);
                } else {
                    NormalOrderAdapter::instance()->advance($orderNo, NormalOrderAdapter::EXT_STATUS_PENDING_REVIEW);
                }
            });
        } catch (\Throwable $e) {
            Log::error('normal 收货后同步状态失败:' . $e->getMessage());
            throw $e;
        }
    }
}
