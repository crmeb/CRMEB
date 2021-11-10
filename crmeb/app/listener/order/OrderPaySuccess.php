<?php


namespace app\listener\order;


use app\jobs\AgentJob;
use app\jobs\OrderJob;
use app\jobs\ProductLogJob;
use app\services\activity\StoreSeckillServices;
use app\services\coupon\StoreCouponIssueServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderStatusServices;
use app\services\product\product\StoreProductCouponServices;
use app\services\product\sku\StoreProductVirtualServices;
use app\services\system\MessageSystemServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\ListenerInterface;
use think\Exception;
use think\facade\Log;

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
//        $userServices->incPayCount($orderInfo['uid']);
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


        /** @var StoreOrderCartInfoServices $services */
        $services = app()->make(StoreOrderCartInfoServices::class);
        $orderInfo['cart_info'] = $services->getOrderCartInfo((int)$orderInfo['id']);

        if ($orderInfo['virtual_type'] == 1) {
            $unique = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['attrInfo']['unique'];
            /** @var StoreProductVirtualServices $virtualService */
            $virtualService = app()->make(StoreProductVirtualServices::class);
            $virtual = $virtualService->get(['attr_unique' => $unique, 'uid' => 0]);
            $virtual->order_id = $orderInfo['order_id'];
            $virtual->uid = $orderInfo['uid'];
            $virtual->save();
            /** @var StoreOrderServices $orderService */
            $orderService = app()->make(StoreOrderServices::class);
            $orderService->update(['id' => $orderInfo['id']], ['status' => 1, 'virtual_info' => $virtual->card_unique, 'remark' => '卡密已自动发放，卡号：' . $virtual->card_no . '；密码：' . $virtual->card_pwd]);
            $this->SystemSend($orderInfo['uid'], [
                'mark' => 'virtual_info',
                'title' => '虚拟卡密发放',
                'content' => '您购买的卡密商品已支付成功，支付金额' . $orderInfo['pay_price'] . '元，订单号' . $orderInfo['order_id'] . '卡号：' . $virtual->card_no . '；密码：' . $virtual->card_pwd . ',感谢您的光临！'
            ]);
        } elseif ($orderInfo['virtual_type'] == 2) {
            $coupon_id = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['attrInfo']['coupon_id'];
            /** @var StoreCouponIssueServices $issueService */
            $issueService = app()->make(StoreCouponIssueServices::class);
            $coupon = $issueService->get($coupon_id);
            if ($issueService->setCoupon($coupon, [$orderInfo['uid']])) {
                /** @var StoreOrderServices $orderService */
                $orderService = app()->make(StoreOrderServices::class);
                $orderService->update(['id' => $orderInfo['id']], ['status' => 1, 'virtual_info' => $coupon_id, 'remark' => '优惠券已自动发放']);
                $this->SystemSend($orderInfo['uid'], [
                    'mark' => 'virtual_info',
                    'title' => '购买优惠券发放',
                    'content' => '您购买的优惠券已支付成功，支付金额' . $orderInfo['pay_price'] . '元，订单号' . $orderInfo['order_id'] . '请在个人中心优惠券中查看,感谢您的光临！'
                ]);
            } else {
                throw new AdminException('您已有这张优惠券，请勿重复购买');
            }
        }


        //支付成功后发送消息
        OrderJob::dispatch([$orderInfo]);
        //支付成功处理自己、上级分销等级升级
        AgentJob::dispatch([(int)$orderInfo['uid']]);

        //商品日志记录支付记录
        ProductLogJob::dispatch(['pay', ['uid' => $orderInfo['uid'], 'order_id' => $orderInfo['id']]]);
    }

    /**
     * 虚拟商品站内信
     * @param int $uid
     * @param array $noticeInfo
     */
    public function SystemSend(int $uid, array $noticeInfo)
    {
        /** @var MessageSystemServices $MessageSystemServices */
        $MessageSystemServices = app()->make(MessageSystemServices::class);
        $data = [];
        $data['mark'] = $noticeInfo['mark'];
        $data['uid'] = $uid;
        $data['title'] = $noticeInfo['title'];
        $data['content'] = $noticeInfo['content'];
        $data['type'] = 1;
        $data['add_time'] = time();
        $MessageSystemServices->save($data);
    }
}
