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

namespace modules\product\virtual\controller\user;

use modules\product\virtual\service\OrderCreateServices;

/**
 * 网盘商品下单控制器
 * 调用内化的 OrderCreateServices
 */
class CheckoutController
{
    /**
     * 确认订单（结算页）
     * 过渡期：调 app 现有确认订单逻辑
     */
    public function confirm()
    {
        // 过渡期：调用 app 现有 StoreOrderServices::checkOrder
        return app('json')->success([
            'message' => '确认订单（过渡期）',
        ]);
    }

    /**
     * 创建订单
     * 调用内化的 OrderCreateServices
     */
    public function create(OrderCreateServices $service)
    {
        $request = request();

        // 过渡期：从请求中获取下单参数
        $uid = (int) $request->uid();
        $key = $request->param('key', '');
        $payType = $request->param('payType', '');
        $useIntegral = (bool) $request->param('useIntegral', false);
        $couponId = (int) $request->param('couponId', 0);
        $mark = $request->param('mark', '');

        // 过渡期：用户信息从请求获取
        $userInfo = [
            'uid'         => $uid,
            'user_type'   => $request->param('user_type', 'routine'),
            'division_id' => 0,
            'agent_id'    => 0,
            'staff_id'    => 0,
        ];

        $order = $service->createOrder(
            $uid, $key, $userInfo, $payType,
            $useIntegral, $couponId, $mark
        );

        return app('json')->success('订单创建成功', [
            'order_id' => $order['order_id'] ?? '',
        ]);
    }
}
