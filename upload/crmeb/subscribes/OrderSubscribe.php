<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace crmeb\subscribes;

use app\admin\model\order\StoreOrder as AdminStoreOrder;
use app\models\store\StoreOrder;

/**
 * 订单事件
 * Class OrderSubscribe
 * @package crmeb\subscribes
 */
class OrderSubscribe
{

    public function handle()
    {

    }

    /**
     * 送货发送模板消息
     * @param $event
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function onStoreProductOrderDeliveryAfter($event)
    {
        list($data,$oid) = $event;
        AdminStoreOrder::orderPostageAfter($oid,$data);
    }

    /**
     * 发货发送模板消息
     * @param $event
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function onStoreProductOrderDeliveryGoodsAfter($event)
    {
        list($data,$oid) = $event;
        AdminStoreOrder::orderPostageAfter($oid,$data);
    }

    /**
     * 订单状态不退款 发送模板消息
     * @param $event
     */
    public function onStoreProductOrderRefundNAfter($event)
    {
        list($data,$id) = $event;
        AdminStoreOrder::refundNoPrieTemplate($id,$data);
    }

    /**
     * 线下付款成功后
     * @param $event
     */
    public function onStoreProductOrderOffline($event)
    {
        list($id) = $event;
        //订单编号  $id
    }

    /**
     * 修改订单金额
     * @param $event
     */
    public function onStoreProductOrderEditAfter($event)
    {
        list($data,$id) = $event;
        //$data  total_price 商品总价   pay_price 实际支付
        //订单编号  $id
    }

    /**
     * 修改配送信息
     * @param $event
     */
    public function onStoreProductOrderDistributionAfter($event)
    {
        list($data,$id) = $event;
        //$data   送货人姓名/快递公司   送货人电话/快递单号
        //订单编号  $id
    }

    /**
     * 订单全部产品评价完
     * @param $event
     */
    public function onStoreProductOrderOver($event)
    {
        list($oid) = $event;
    }

    /**
     * 回退所有  未支付和已退款的状态下才可以退积分退库存退优惠券
     * @param $event
     */
    public  function onStoreOrderRegressionAllAfter($event)
    {
        list($order) = $event;
        StoreOrder::RegressionStock($order) && StoreOrder::RegressionIntegral($order) && StoreOrder::RegressionCoupon($order);
    }

}