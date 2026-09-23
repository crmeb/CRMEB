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

use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use app\services\order\OrderLifecycleServices;
use crmeb\trade\order\type\OrderTypeAdapterInterface;
use crmeb\trade\order\type\OrderTypeRegistry;
use modules\product\normal\handler\NormalOrderAdapter;

/**
 * 实物商品订单取消监听器
 * 统一走 OrderTypeAdapter::advance 推进扩展表状态 + 中台同步
 */
class OrderCancelListener
{
    public function handle($event): void
    {
        [$orderInfo, $uid] = $event;
        $orderNo = $orderInfo['order_no'] ?? $orderInfo['order_id'] ?? '';
        if (empty($orderNo)) {
            return;
        }

        // 确认 normal 类型 + 幂等
        $centerDao = app(OrderCenterDao::class);
        $meta = $centerDao->get(['order_no' => $orderNo], ['source_type', 'status_code']);
        if (!$meta || ($meta['source_type'] ?? '') !== NormalOrderAdapter::getSourceType()) {
            return;
        }
        if ($meta['status_code'] === 'canceled') {
            return;
        }

        // 1. advance 推进扩展表状态(is_cancel=1) + 中台 status_code 同步
        $adapter = OrderTypeRegistry::getAdapter(NormalOrderAdapter::getSourceType());
        $adapter->advance($orderNo, OrderTypeAdapterInterface::EXT_STATUS_CANCELED, [
            'is_cancel' => 1,
        ]);

        // 2. 补写中台 cancel_time
        app(OrderCenterServices::class)->syncStatus($orderNo, OrderTypeAdapterInterface::STATUS_CANCELED, [
            'cancel_time' => time(),
        ]);

        // 3. 通用生命周期：返还积分/优惠券、回滚库存、写日志
        app(OrderLifecycleServices::class)->handleCancel($orderInfo);
    }
}
