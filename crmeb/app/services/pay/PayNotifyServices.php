<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
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
 * 支付成功回调 所有的异步通知回调都会走下面的三个方法,不在取分微信/支付宝支付回调
 * Class PayNotifyServices
 * @package app\services\pay
 */
class PayNotifyServices
{

    /**
     * 订单支付成功之后
     * @param string|null $order_id 订单id
     * @param string|null $trade_no
     * @param string $payType
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function wechatProduct(string $order_id = null, string $trade_no = null, string $payType = PayServices::WEIXIN_PAY)
    {
        try {
            /** @var StoreOrderSuccessServices $services */
            $services = app()->make(StoreOrderSuccessServices::class);
            $orderInfo = $services->getOne(['order_id' => $order_id]);
            if (!$orderInfo) return true;
            if ($orderInfo->paid) return true;
            return $services->paySuccess($orderInfo->toArray(), $payType, ['trade_no' => $trade_no]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 充值成功后
     * @param string|null $order_id 订单id
     * @return bool
     */
    public function wechatUserRecharge(string $order_id = null, string $trade_no = null, string $payType = PayServices::WEIXIN_PAY)
    {
        try {
            /** @var UserRechargeServices $userRecharge */
            $userRecharge = app()->make(UserRechargeServices::class);
            if ($userRecharge->be(['order_id' => $order_id, 'paid' => 1])) return true;
            return $userRecharge->rechargeSuccess($order_id, ['trade_no' => $trade_no, 'pay_type' => $payType]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 购买会员
     * @param string|null $order_id
     * @return bool
     */
    public function wechatMember(string $order_id = null, string $trade_no = null, string $payType = PayServices::WEIXIN_PAY)
    {
        try {
            /** @var OtherOrderServices $services */
            $services = app()->make(OtherOrderServices::class);
            $orderInfo = $services->getOne(['order_id' => $order_id]);
            if (!$orderInfo) return true;
            if ($orderInfo->paid) return true;
            return $services->paySuccess($orderInfo->toArray(), $payType, ['trade_no' => $trade_no]);
        } catch (\Exception $e) {
            return false;
        }
    }

}
