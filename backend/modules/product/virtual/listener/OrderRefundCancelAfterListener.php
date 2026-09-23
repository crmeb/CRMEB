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

namespace modules\product\virtual\listener;

use app\dao\order\OrderCenterDao;
use app\services\order\OrderLifecycleServices;
use think\facade\Log;

/**
 * 网盘商品退款取消监听器
 * 通过 store_order_id 查 orderNo 再校验 product_type，调用通用生命周期服务记录日志
 */
class OrderRefundCancelAfterListener
{
    public function handle($event): void
    {
        [$orderRefundInfo] = $event;

        try {
            // 通过 store_order_id 查询订单号
            $storeOrderId = $orderRefundInfo['store_order_id'] ?? 0;
            if (!$storeOrderId) {
                return;
            }
            $centerDao = app(OrderCenterDao::class);
            $orderNo = $centerDao->getDb()->where('id', $storeOrderId)->value('order_no');
            if (empty($orderNo)) {
                return;
            }

            // 校验 product_type 是否为 virtual
            $meta = $centerDao->get(['order_no' => $orderNo], ['product_type']);
            if (!$meta || ($meta['product_type'] ?? '') !== 'virtual') {
                return;
            }

            // 调用通用生命周期服务
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logRefundCancel($orderRefundInfo);
        } catch (\Throwable $e) {
            Log::error('网盘商品退款取消监听失败,失败原因:' . $e->getMessage());
        }
    }
}
