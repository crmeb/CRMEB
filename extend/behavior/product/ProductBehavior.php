<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace behavior\product;


class ProductBehavior
{

    /**
     * 取消点赞产品后
     * @param $productId
     * @param $uid
     */
    public static function storeProductUnLikeAfter($productId, $uid)
    {

    }

    /**
     * 收藏产品后
     * @param $product
     * @param $uid
     */
    public static function storeProductCollecAfter($product, $uid)
    {

    }

    /**
     * 取消收藏产品后
     * @param $productId
     * @param $uid
     */
    public static function storeProductUnCollecAfter($productId, $uid)
    {

    }

    /**
     * 点赞产品后
     * @param $product
     * @param $uid
     */
    public static function storeProductLikeAfter($product, $uid)
    {

    }

    /**
     * 订单创建成功后
     * @param $oid
     */
    public static function storeProductOrderCreate($order,$group)
    {

    }


    /**
     * 修改状态 为已收货
     * @param $data
     *  $data array status  状态为  已收货
     * @param $oid
     * $oid  string store_order表中的id
     */
    public static function storeProductOrderTakeDeliveryAfter($order,$oid)
    {

    }

    /**
     * 用户确认收货
     * @param $order
     * @param $uid
     */
    public static function storeProductOrderUserTakeDelivery($order, $uid)
    {

    }

    /**
     * 线下付款
     * @param $id
     * $id 订单id
     */
    public static function storeProductOrderOffline($id){

    }

    /**
     * 修改状态为  已退款
     * @param $data
     *  $data array type 1 直接退款  2 退款后返回原状态  refund_price  退款金额
     * @param $oid
     * $oid  string store_order表中的id
     */
    public static function storeProductOrderRefundYAfter($data,$oid){

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

    }


    /**
     * 评价产品
     * @param $replyInfo
     * @param $cartInfo
     */
    public static function storeProductOrderReply($replyInfo, $cartInfo)
    {

    }

    /**
     * 订单全部产品评价完
     * @param $oid
     */
    public static function storeProductOrderOver($oid)
    {

    }

    /**
     * 加入购物车成功之后
     * @param array $cartInfo 购物车信息
     * @param array $userInfo 用户信息
     */
    public static function storeProductSetCartAfterAfter($cartInfo, $userInfo)
    {

    }


}