<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\listener\order;


use app\jobs\notice\PrintJob;
use app\jobs\ProductLogJob;
use app\jobs\UnpaidOrderCancelJob;
use app\jobs\UnpaidOrderSend;
use app\dao\order\OrderLogDao;
use app\model\order\OrderCenter;
// @deprecated eb_store_order_status 表已删除
use crmeb\interfaces\ListenerInterface;
use crmeb\services\CacheService;
use crmeb\utils\Arr;
use think\facade\Log;
use think\facade\Env;

/**
 * 订单创建后置事件
 * Class OrderCreateAfterListener
 * @package app\listener\order
 */
class OrderCreateAfterListener implements ListenerInterface
{
    public function handle($event): void
    {
        // 新中台架构统一新格式：['order_no' => ..., 'order_id' => ..., ...]（由各产品模块 OrderCreateServices 触发）
        if (is_array($event) && isset($event['order_no'])) {
            $this->handleNewFormat($event);
            return;
        }

        // 旧格式（[$order, $group, ...]）已全部淘汰：modules 层脱壳后不再触发，兼容分支移除
    }

    /**
     * 处理新中台架构的订单创建后置事件
     * 仅执行通用后续操作（旧订单创建逻辑已由各产品模块内化）
     * @param array $event 事件参数
     */
    protected function handleNewFormat(array $event): void
    {
        $orderNo = $event['order_no'] ?? '';
        $uid = (int)($event['uid'] ?? 0);
        $key = $event['key'] ?? '';
        $cartInfo = $event['cart_info'] ?? [];
        $priceData = $event['price_data'] ?? [];
        $orderId = (int)($event['order_id'] ?? 0);
        $productType = $event['product_type'] ?? '';

        // key 已是 cacheOrderInfo 返回的完整缓存 key，不能再次拼接用户前缀。
        if ($key !== '') {
            try {
                CacheService::delete($key);
            } catch (\Throwable $e) {
                Log::warning('[OrderCreateAfterListener] 订单确认缓存清理失败: ' . $e->getMessage());
            }
        }

        //写入订单状态日志表
        /** @var OrderLogDao $logDao */
        $logDao = app()->make(OrderLogDao::class);
        $logDao->save([
            'order_no'      => $orderNo,
            'uid'           => $uid,
            'operator_id'   => $uid,
            'operator_type' => 'user',
            'from_status'   => '',
            'to_status'     => OrderCenter::STATUS_PENDING_PAYMENT,
            'content'       => '订单生成',
            'add_time'      => time(),
        ]);

        //订单自动取消：新架构普通商品使用系统默认取消时间
        if ($orderId > 0) {
            $this->pushJob($orderId, 0, 0, 0);
        }

        // 以下异步任务均为旧架构设计，新架构下先捕获异常避免影响主流程，待后续全面适配
        try {
            //下单记录：新架构使用 order_no 作为订单标识
            if (!empty($cartInfo)) {
                ProductLogJob::dispatch(['order', ['uid' => $uid, 'order_id' => $orderNo, 'order_no' => $orderNo]]);
            }

            //小票打印
            if ($orderId > 0) {
                PrintJob::dispatch([$orderId, 2]);
            }
        } catch (\Throwable $e) {
            Log::warning('[OrderCreateAfterListener] 新架构异步任务调度失败: ' . $e->getMessage());
        }
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
        // 消费者启动不代表业务已启用队列；延迟任务不能降级为同步执行。
        $queueOpen = sys_config('queue_open', 0);
        $cacheDriver = Env::get('cache.driver', 'file');
        if ($queueOpen != 1 || $cacheDriver !== 'redis') {
            Log::warning('[OrderCreateAfterListener] 跳过订单延迟任务：需开启 queue_open 并设置 cache.driver=redis，order_id=' . $orderId);
            return;
        }
        //系统预设取消订单时间段
        $keyValue = ['order_cancel_time', 'order_activity_time', 'order_bargain_time', 'order_seckill_time', 'order_pink_time'];
        //获取配置
        $systemValue = sys_config_more($keyValue);
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
        $delaySeconds = (int)ceil((float)$secs * 3600);
        // 0、负数或缺失配置均不启用自动取消，保留小数小时精度。
        if ($delaySeconds > 0) {
            UnpaidOrderCancelJob::dispatchSecs($delaySeconds, [$orderId, $delaySeconds]);
        }
    }
}
