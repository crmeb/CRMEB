<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace behavior\routine;


use app\routine\model\store\StoreOrder;
use app\routine\model\user\User;
use app\routine\model\user\WechatUser;
use service\SystemConfigService;

class StoreProductBehavior
{
    /**
     * 用户确认收货
     * @param $order
     * @param $uid
     */
    public static function storeProductOrderUserTakeDelivery($order, $uid)
    {
        $res1 = StoreOrder::gainUserIntegral($order);
        $res2 = User::backOrderBrokerage($order);
        StoreOrder::orderTakeAfter($order);
        $giveCouponMinPrice = SystemConfigService::get('store_give_con_min_price');
        if($order['total_price'] >= $giveCouponMinPrice) WechatUser::userTakeOrderGiveCoupon($uid);
        if(!($res1 && $res2)) exception('收货失败!');
    }
}