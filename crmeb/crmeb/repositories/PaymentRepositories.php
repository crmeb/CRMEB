<?php

namespace crmeb\repositories;

use app\models\store\StoreOrder;
use app\models\user\UserRecharge;

/**
 * Class PaymentRepositories
 * @package crmeb\repositories
 */
class PaymentRepositories
{

    /**
     * 公众号下单成功之后
     * @param $order
     * @param $prepay_id
     */
    public static function wechatPaymentPrepare($order, $prepay_id)
    {

    }

    /**
     * 小程序下单成功之后
     * @param $order
     * @param $prepay_id
     */
    public static function wechatPaymentPrepareProgram($order, $prepay_id)
    {

    }

    /**
     * 使用余额支付订单时
     * @param $userInfo
     * @param $orderInfo
     */
    public static function yuePayProduct($userInfo, $orderInfo)
    {


    }

    /**
     * 订单支付成功之后
     * @param string|null $order_id 订单id
     * @return bool
     */
    public static function wechatProduct(string $order_id = null)
    {
        try {
            if (StoreOrder::be(['order_id' => $order_id, 'paid' => 1])) return true;
            return StoreOrder::paySuccess($order_id);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 充值成功后
     * @param string|null $order_id 订单id
     * @return bool
     */
    public static function wechatUserRecharge(string $order_id = null)
    {
        try {
            if (UserRecharge::be(['order_id' => $order_id, 'paid' => 1])) return true;
            return UserRecharge::rechargeSuccess($order_id);
        } catch (\Exception $e) {
            return false;
        }
    }
}