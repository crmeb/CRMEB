<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace behavior\admin;

use app\admin\model\user\User;
use app\admin\model\order\StoreOrder as AdminStoreOrder;
use app\core\util\WechatTemplateService;

class OrderBehavior
{



    /**
     * 修改发货状态  为送货
     * @param $data
     *  $data array  送货方式 送货人姓名  送货人电话
     * @param $oid
     * $oid  string store_order表中的id
     */
    public static function storeProductOrderDeliveryAfter($data,$oid){
        AdminStoreOrder::orderPostageAfter($oid,$data);
//        AdminStoreOrder::sendOrderGoods($oid,$data);
    }

    /**
     * 修改发货状态  为发货
     * @param $data
     *  $data array  发货方式 送货人姓名  送货人电话
     * @param $oid
     * $oid  string store_order表中的id
     */
    public static function storeProductOrderDeliveryGoodsAfter($data,$oid){
        AdminStoreOrder::orderPostageAfter($oid,$data);
    }

    /**
     * 后台修改状态 为已收货
     * @param $data
     *  $data array status  状态为  已收货
     * @param $oid
     * $oid  string store_order表中的id
     */
    public static function storeProductOrderTakeDelivery($order)
    {

        $res1 = AdminStoreOrder::gainUserIntegral($order);
        $res2 = \app\ebapi\model\user\User::backOrderBrokerage($order);
        AdminStoreOrder::orderTakeAfter($order);
        if(!($res1 && $res2)) exception('收货失败!');
    }


    /**
     * 修改状态为  已退款
     * @param $data
     *  $data array type 1 直接退款  2 退款后返回原状态  refund_price  退款金额
     * @param $oid
     * $oid  string store_order表中的id
     */
    public static function storeProductOrderRefundYAfter($data,$oid){
        $order=AdminStoreOrder::where(['id'=>$oid])->find();
        if($order['is_channel'])
            AdminStoreOrder::refundRoutineTemplate($oid); //TODO 小程序余额退款模板消息
        else
            AdminStoreOrder::refundTemplate($data,$oid);//TODO 公众号余额退款模板消息
    }


    /**
     * 修改状态为  不退款
     * @param $data
     *  $data string  退款原因
     * @param $oid
     * $oid  string store_order表中的id
     */
    public static function storeProductOrderRefundNAfter($data,$oid){
        AdminStoreOrder::refundNoPrieTemplate($oid,$data);
    }
    /**
     * 线下付款
     * @param $id
     * $id 订单id
     */
    public static function storeProductOrderOffline($id){

    }

    /**
     * 修改订单状态
     * @param $data
     *  data  total_price 商品总价   pay_price 实际支付
     * @param $oid
     * oid 订单id
     */
    public static function storeProductOrderEditAfter($data,$oid){

    }
    /**
     * 修改送货信息
     * @param $data
     *  $data array  送货人姓名/快递公司   送货人电话/快递单号
     * @param $oid
     * $oid  string store_order表中的id
     */
    public static function storeProductOrderDistributionAfter($data,$oid){

    }

    /**
     * 用户申请退款
     * @param $oid
     * @param $uid
     */
    public static function storeProductOrderApplyRefundAfter($oid, $uid)
    {
        $order = AdminStoreOrder::where('id',$oid)->find();
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
     */
    public static function storeProductOrderReply($replyInfo, $cartInfo)
    {
        //StoreOrder::checkOrderOver($cartInfo['oid']);
    }


    /**
     * 退积分
     * @param $product
     * $product 商品信息
     * @param $back_integral
     * $back_integral 退多少积分
     */
    public static function storeOrderIntegralBack($product,$back_integral){

    }

    /**
     * TODO  后台余额退款
     * @param $product
     * @param $refund_data
     */
    public static function storeOrderYueRefund($product,$refund_data)
    {
        $res = AdminStoreOrder::integralBack($product['id']);
        if(!$res) exception('退积分失败!');

    }




}