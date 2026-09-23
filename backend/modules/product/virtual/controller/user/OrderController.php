<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\controller\user;

use modules\product\virtual\service\OrderServices;

/**
 * 网盘商品用户端订单控制器
 */
class OrderController
{
    /**
     * 订单详情
     * 过渡期：调用 app 现有订单详情 + 扩展表数据
     */
    public function read(string $orderNo, OrderServices $service)
    {
        $order = $service->getUserOrder($orderNo, (int)request()->uid());
        if (!$order) {
            return app('json')->fail('订单不存在');
        }
        $extendInfo = $service->getExtendInfo($orderNo);
        return app('json')->success([
            'order_no'    => $orderNo,
            'extend_info' => $extendInfo,
        ]);
    }
}
