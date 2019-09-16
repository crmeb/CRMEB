<?php
namespace crmeb\repositories;

/**
 * Class WechatPaymentRepositories
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
}