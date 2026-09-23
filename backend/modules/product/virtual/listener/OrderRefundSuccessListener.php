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
use modules\product\virtual\Handler;
use think\facade\Log;

/**
 * 网盘商品退款成功监听器
 * 校验 product_type 后调用通用生命周期服务记录退款日志，并调用 Handler 处理扩展表
 */
class OrderRefundSuccessListener
{
    public function handle($event): void
    {
        [$order, $refundData] = $event;
        $orderNo = $order['order_id'] ?? '';
        if (empty($orderNo)) {
            return;
        }

        try {
            // 校验 product_type 是否为 virtual
            $centerDao = app(OrderCenterDao::class);
            $meta = $centerDao->get(['order_no' => $orderNo], ['product_type']);
            if (!$meta || ($meta['product_type'] ?? '') !== 'virtual') {
                return;
            }

            // 调用通用生命周期服务
            $lifecycleServices = app(OrderLifecycleServices::class);
            $lifecycleServices->logRefundSuccess($order, $refundData);

            // 调用 Handler 处理扩展表
            $handler = app(Handler::class);
            $refundAmount = (float) ($refundData['refund_price'] ?? $order['refund_price'] ?? 0);
            $handler->onRefund($orderNo, $refundAmount, [
                'order'      => $order,
                'refundData' => $refundData,
            ]);
        } catch (\Throwable $e) {
            Log::error('网盘商品退款成功监听失败,失败原因:' . $e->getMessage());
        }
    }
}
