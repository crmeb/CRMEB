<?php


namespace app\listener\order;


use app\jobs\AgentJob;
use app\jobs\OrderJob;
use app\jobs\ProductLogJob;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderDeliveryServices;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderStatusServices;
use app\services\product\product\StoreProductCouponServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\product\sku\StoreProductVirtualServices;
use app\services\message\MessageSystemServices;
use app\services\statistic\CapitalFlowServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 订单支付成功后
 * Class OrderPaySuccess
 * @package app\listener\order
 */
class OrderPaySuccess implements ListenerInterface
{
    public function handle($event): void
    {
        [$orderInfo] = $event;

        //写入订单状态事件
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $orderInfo['id'],
            'change_type' => 'pay_success',
            'change_message' => '用户付款成功',
            'change_time' => time()
        ]);

        //回退秒杀库存占用
        /** @var StoreOrderCartInfoServices $cartServices */
        $cartServices = app()->make(StoreOrderCartInfoServices::class);
        $cartInfo = $cartServices->getOrderCartInfo($orderInfo['id']);
        /** @var StoreSeckillServices $seckiiServices */
        $seckiiServices = app()->make(StoreSeckillServices::class);
        $seckiiServices->cancelOccupySeckillStock($cartInfo, $orderInfo['unique']);

        //赠送购买商品优惠券
        /** @var StoreProductCouponServices $storeProductCouponServices */
        $storeProductCouponServices = app()->make(StoreProductCouponServices::class);
        $storeProductCouponServices->giveOrderProductCoupon((int)$orderInfo['uid'], $orderInfo['id']);

        //修改开票数据支付状态
        $orderInvoiceServices = app()->make(StoreOrderInvoiceServices::class);
        $orderInvoiceServices->update(['order_id' => $orderInfo['id']], ['is_pay' => 1]);

        //虚拟商品自动发货
        if($orderInfo['virtual_type'] > 0){
            /** @var StoreOrderDeliveryServices $orderDeliveryServices */
            $orderDeliveryServices = app()->make(StoreOrderDeliveryServices::class);
            $orderDeliveryServices->virtualSend($orderInfo);
        }

        // 写入资金流水
        if ($orderInfo['pay_type'] == 'weixin' || $orderInfo['pay_type'] == 'alipay') {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->get($orderInfo['uid']);
            /** @var CapitalFlowServices $capitalFlowServices */
            $capitalFlowServices = app()->make(CapitalFlowServices::class);
            $orderInfo['nickname'] = $userInfo['nickname'];
            $orderInfo['phone'] = $userInfo['phone'];
            $capitalFlowServices->setFlow($orderInfo, 'order');
        }

        //支付成功后发送消息
        OrderJob::dispatch([$orderInfo]);

        //支付成功处理自己、上级分销等级升级
        AgentJob::dispatch([(int)$orderInfo['uid']]);

        //商品日志记录支付记录
        ProductLogJob::dispatch(['pay', ['uid' => $orderInfo['uid'], 'order_id' => $orderInfo['id']]]);
    }
}
