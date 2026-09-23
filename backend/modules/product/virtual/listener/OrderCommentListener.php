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
 * 网盘商品评价监听器
 * 校验 product_type 后调用通用生命周期服务记录评价日志
 */
class OrderCommentListener
{
    public function handle($event): void
    {
        [$replyInfo, $order] = $event;
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
            $lifecycleServices->logComment($replyInfo, $order);
        } catch (\Throwable $e) {
            Log::error('网盘商品评价监听失败,失败原因:' . $e->getMessage());
        }
    }
}
