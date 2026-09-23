<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\controller\user;

use app\services\order\OrderCenterUserServices;

/**
 * 实物商品用户端订单控制器
 */
class OrderController
{
    /**
     * 订单详情
     * 过渡期：调用 app 现有订单详情 + 扩展表数据
     */
    public function read(string $orderNo, OrderCenterUserServices $service)
    {
        $uid = (int) request()->uid();
        try {
            $ownedId = $service->getOwnedOrderIdByNo($orderNo, $uid);
            if ($ownedId === null) return app('json')->fail('订单不存在');
            $result = $service->getDetailWithConfig($ownedId);
            if (!empty($result['page_config']['sections'])) {
                $result['page_config']['sections'] = array_values(array_filter(
                    $result['page_config']['sections'], fn ($s) => ($s['key'] ?? '') !== 'timeline'
                ));
            }
            return app('json')->success($result);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
