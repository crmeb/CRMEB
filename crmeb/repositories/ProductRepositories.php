<?php
namespace crmeb\repositories;

use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\UserAddress;
use app\models\user\WechatUser;
use crmeb\services\SystemConfigService;
use crmeb\services\WechatTemplateService;

/**
 * Class ProductRepositories
 * @package crmeb\repositories
 */
class ProductRepositories
{


    /**
     * 用户确认收货
     * @param $order
     * @param $uid
     * @throws \Exception
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
     * 订单创建成功后  wap模块
     * @param $order
     * @param $group
     */
    public static function storeProductOrderCreateWap($order,$group)
    {
        UserAddress::be(['is_default'=>1,'uid'=>$order['uid']]) || UserAddress::setDefaultAddress($group['addressId'],$order['uid']);
    }

    public static function storeProductOrderApplyRefundWap($oid, $uid)
    {
        $order = StoreOrder::where('id',$oid)->find();
        WechatTemplateService::sendAdminNoticeTemplate([
            'first'=>"亲,您有一个订单申请退款 \n订单号:{$order['order_id']}",
            'keyword1'=>'申请退款',
            'keyword2'=>'待处理',
            'keyword3'=>date('Y/m/d H:i',time()),
            'remark'=>'请及时处理'
        ]);
    }


    /**
     * 评价产品
     * @param $replyInfo
     * @param $cartInfo
     * @return StoreOrder|\think\Model
     */
    public static function storeProductOrderReplyWap($replyInfo, $cartInfo)
    {
        return StoreOrder::checkOrderOver($cartInfo['oid']);
    }


}