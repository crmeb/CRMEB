<?php
namespace crmeb\repositories;

use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\WechatUser;
use app\admin\model\user\User as AdminUser;
use app\admin\model\order\StoreOrder as AdminStoreOrder;
use crmeb\services\SystemConfigService;

/**
 * Class OrderRepository
 * @package crmeb\repositories
 */
class OrderRepository
{

    /**
     * 用户确认收货
     * @param $order
     * @param $uid
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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

    /**
     * 修改状态 为已收货  admin模块
     * @param $order
     * @throws \Exception
     */
    public static function storeProductOrderTakeDeliveryAdmin($order)
    {

        $res1 = AdminStoreOrder::gainUserIntegral($order);
        $res2 = User::backOrderBrokerage($order);
        AdminStoreOrder::orderTakeAfter($order);
        if(!($res1 && $res2)) exception('收货失败!');
    }


    /**
     * 修改状态为  已退款  admin模块
     * @param $data
     * @param $oid
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function storeProductOrderRefundY($data,$oid){
        $order = AdminStoreOrder::where('id', $oid)->find();
        if($order['is_channel'])
            return AdminStoreOrder::refundRoutineTemplate($oid); //TODO 小程序余额退款模板消息
        else
            return AdminStoreOrder::refundTemplate($data,$oid);//TODO 公众号余额退款模板消息
    }


    /**
     * TODO  后台余额退款
     * @param $product
     * @param $refund_data
     * @throws \Exception
     */
    public static function storeOrderYueRefund($product,$refund_data)
    {
        $res = AdminStoreOrder::integralBack($product['id']);
        if(!$res) exception('退积分失败!');
    }

    /**
     * 订单退积分
     * @param $product $product 商品信息
     * @param $back_integral $back_integral 退多少积分
     */
    public static function storeOrderIntegralBack($product,$back_integral){

    }


    public static function computedOrder()
    {

    }

}