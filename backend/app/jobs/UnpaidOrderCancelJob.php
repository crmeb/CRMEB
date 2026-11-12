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

namespace app\jobs;


use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;
use think\facade\Env;

/**
 * 未支付订单到期取消
 * Class UnpaidOrderCancelJob
 * @package crmeb\jobs
 */
class UnpaidOrderCancelJob extends BaseJobs
{


    /**
     * 按订单创建时间核对超时后执行取消
     * @param int|string $orderId 中台主键或订单号
     * @param int|null $delaySeconds 下单时的取消时长，旧任务按系统配置兼容
     * @return bool
     */
    public function doJob($orderId, ?int $delaySeconds = null)
    {
        /** @var OrderCenterDao $services */
        $services = app()->make(OrderCenterDao::class);

        // 兼容新中台架构：参数可能是 order_no 字符串
        if (is_string($orderId) && !is_numeric($orderId)) {
            $orderInfo = $services->get(['order_no' => $orderId]);
        } else {
            $orderInfo = $services->get((int)$orderId);
        }

        if (!$orderInfo) {
            return true;
        }

        $orderInfo = $orderInfo->toArray();
        if ((int)($orderInfo['pay_status'] ?? 0) !== 0
            || in_array($orderInfo['status_code'] ?? '', ['canceled', 'refunded'], true)) {
            return true;
        }

        // 不能仅信任队列的执行时机，防止同步降级或提前投递误取消新订单。
        $delaySeconds ??= (int)ceil((float)sys_config('order_cancel_time', 0) * 3600);
        $createdAt = (int)($orderInfo['add_time'] ?? 0);
        if ($delaySeconds <= 0 || $createdAt <= 0) {
            return true;
        }
        $remaining = $createdAt + $delaySeconds - time();
        if ($remaining > 0) {
            // 异步环境提前收到任务时重新延迟；同步环境不递归调用 dispatchSecs。
            if (sys_config('queue_open', 0) == 1 && Env::get('cache.driver', 'file') === 'redis') {
                return (bool)static::buildQueue([$orderId, $delaySeconds], [], null, $remaining);
            }
            Log::warning('[UnpaidOrderCancelJob] 订单尚未到期且异步队列未启用，跳过取消，order_id=' . $orderId);
            return true;
        }

        // 统一走中台架构订单处理
        return $this->handleCenterOrder($orderInfo);
    }

    /**
     * 到期后交给中台的同一次未支付取消。
     * @param array $orderInfo 订单中台记录
     * @return bool
     */
    protected function handleCenterOrder(array $orderInfo): bool
    {
        $orderNo = (string)($orderInfo['order_no'] ?? '');
        if ($orderNo === '') {
            return true;
        }

        try {
            app()->make(OrderCenterServices::class)->cancelUnpaidOrder($orderNo, 0, 'system');
            return true;
        } catch (\Throwable $e) {
            Log::error('[UnpaidOrderCancelJob] 中台订单自动取消失败, order_no=' . $orderNo . ', 原因:' . $e->getMessage());
            return false;
        }
    }
}
