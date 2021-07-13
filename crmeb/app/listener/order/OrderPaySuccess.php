<?php


namespace app\listener\order;


use app\jobs\OrderJob;
use app\jobs\ProductLogJob;
use app\services\activity\StoreSeckillServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderStatusServices;
use app\services\product\product\StoreProductCouponServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use crmeb\interfaces\ListenerInterface;

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

        //增加购买次数，写入购买记录
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userServices->incPayCount($orderInfo['uid']);
        $now_money = $userServices->value(['uid' => $orderInfo['uid']], 'now_money');
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $userBillServices->income('pay_money', $orderInfo['uid'], $orderInfo['pay_price'], $now_money, $orderInfo['id']);

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

        //支付成功后发送消息
        OrderJob::dispatch([$orderInfo]);

        //商品日志记录支付记录
        ProductLogJob::dispatch(['pay', ['uid' => $orderInfo['uid'], 'order_id' => $orderInfo['id']]]);
    }
}