<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\pay;

use app\services\order\OtherOrderServices;
use app\services\order\StoreOrderSuccessServices;
use app\services\user\UserRechargeServices;

/**
 * 支付成功回调
 * Class PayNotifyServices
 * @package app\services\pay
 */
class PayNotifyServices
{

    /**
     * 订单支付成功之后
     * @param string|null $order_id 订单id
     * @return bool
     */
    public function wechatProduct(string $order_id = null, string $trade_no = null)
    {
        try {
            /** @var StoreOrderSuccessServices $services */
            $services = app()->make(StoreOrderSuccessServices::class);
            $orderInfo = $services->getOne(['order_id' => $order_id]);
            if (!$orderInfo) return true;
            if ($orderInfo->paid) return true;
            return $services->paySuccess($orderInfo->toArray(), PayServices::WEIXIN_PAY, ['trade_no' => $trade_no]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 充值成功后
     * @param string|null $order_id 订单id
     * @return bool
     */
    public function wechatUserRecharge(string $order_id = null, string $trade_no = null)
    {
        try {
            /** @var UserRechargeServices $userRecharge */
            $userRecharge = app()->make(UserRechargeServices::class);
            if ($userRecharge->be(['order_id' => $order_id, 'paid' => 1])) return true;
            return $userRecharge->rechargeSuccess($order_id);
        } catch (\Exception $e) {
            return false;
        }
    }
}
