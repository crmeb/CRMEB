<?php


namespace app\listener\order;


use app\jobs\OrderCreateAfterJob;
use app\jobs\OrderJob;
use app\jobs\ProductLogJob;
use app\jobs\UnpaidOrderCancelJob;
use app\jobs\UnpaidOrderSend;
use app\services\order\StoreOrderCreateServices;
use app\services\order\StoreOrderStatusServices;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\CacheService;
use crmeb\services\SystemConfigService;
use crmeb\utils\Arr;

/**
 * 订单创建后置事件
 * Class OrderCreateAfter
 * @package app\listener\order
 */
class OrderCreateAfter implements ListenerInterface
{
    public function handle($event): void
    {
        [$order, $group, $uid, $key, $combinationId, $seckillId, $bargainId] = $event;

        //订单数据创建之后的商品实际金额计算，佣金计算，优惠折扣计算，设置默认地址，清理购物车
        /** @var StoreOrderCreateServices $orderCreate */
        $orderCreate = app()->make(StoreOrderCreateServices::class);
        $orderCreate->orderCreateAfter($order, $group, $combinationId || $seckillId || $bargainId);

        //清除订单缓存
        CacheService::delete('user_order_' . $uid . $key);

        //写入订单记录表
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'cache_key_create_order',
            'change_message' => '订单生成',
            'change_time' => time()
        ]);

        //订单自动取消
        $this->pushJob($order['id'], $combinationId, $seckillId, $bargainId);

        //计算订单实际金额
        //OrderCreateAfterJob::dispatch([$order, $group, $combinationId || $seckillId || $bargainId]);

        //下单记录
        ProductLogJob::dispatch(['order', ['uid' => $uid, 'order_id' => $order['id']]]);
    }

    /**
     * 订单自动取消加入延迟消息队列
     * @param int $orderId
     * @param int $combinationId
     * @param int $seckillId
     * @param int $bargainId
     * @return mixed
     */
    public function pushJob(int $orderId, int $combinationId, int $seckillId, int $bargainId)
    {
        //系统预设取消订单时间段
        $keyValue = ['order_cancel_time', 'order_activity_time', 'order_bargain_time', 'order_seckill_time', 'order_pink_time'];
        //获取配置
        $systemValue = SystemConfigService::more($keyValue);
        //格式化数据
        $systemValue = Arr::setValeTime($keyValue, is_array($systemValue) ? $systemValue : []);
        if ($combinationId) {
            $secs = $systemValue['order_pink_time'] ?: $systemValue['order_activity_time'];
        } elseif ($seckillId) {
            $secs = $systemValue['order_seckill_time'] ?: $systemValue['order_activity_time'];
        } elseif ($bargainId) {
            $secs = $systemValue['order_bargain_time'] ?: $systemValue['order_activity_time'];
        } else {
            $secs = $systemValue['order_cancel_time'];
        }
        //未支付10分钟后发送短信
        UnpaidOrderSend::dispatchSecs(600, [$orderId]);
        //未支付根据系统设置事件取消订单
        UnpaidOrderCancelJob::dispatchSecs((int)($secs * 3600), [$orderId]);
    }
}
