<?php

namespace app\jobs;

use app\services\order\OutStoreOrderRefundServices;
use app\services\order\OutStoreOrderServices;
use app\services\user\UserServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class OutPushJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 订单推送
     * @param int $oid
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function orderCreate(int $oid, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('订单' . $oid . '推送失败');
            return true;
        }

        try {
            /** @var OutStoreOrderServices $services */
            $services = app()->make(OutStoreOrderServices::class);
            if (!$services->orderCreatePush($oid, $pushUrl)) {
                OutPushJob::dispatchSecs(($step + 1) * 5, 'orderCreate', [$oid, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            Log::error('订单' . $oid . '推送失败,失败原因:' . $e->getMessage());
            OutPushJob::dispatchSecs(($step + 1) * 5, 'orderCreate', [$oid, $pushUrl, $step + 1]);
        }

        return true;
    }

    /**
     * 订单支付推送
     * @param int $oid
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function paySuccess(int $oid, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('订单支付' . $oid . '推送失败');
            return true;
        }

        try {
            /** @var OutStoreOrderServices $services */
            $services = app()->make(OutStoreOrderServices::class);
            if (!$services->paySuccessPush($oid, $pushUrl)) {
                OutPushJob::dispatchSecs(($step + 1) * 5, 'paySuccess', [$oid, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            Log::error('订单支付' . $oid . '推送失败,失败原因:' . $e->getMessage());
            OutPushJob::dispatchSecs(($step + 1) * 5, 'paySuccess', [$oid, $pushUrl, $step + 1]);
        }

        return true;
    }

    /**
     * 售后单生成
     * @param int $oid
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function refundCreate(int $oid, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('售后单' . $oid . '推送失败');
            return true;
        }

        try {
            /** @var OutStoreOrderRefundServices $services */
            $services = app()->make(OutStoreOrderRefundServices::class);
            if (!$services->refundCreatePush($oid, $pushUrl)) {
                OutPushJob::dispatchSecs(($step + 1) * 5, 'refundCreate', [$oid, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            Log::error('售后单' . $oid . '推送失败,失败原因:' . $e->getMessage());
            OutPushJob::dispatchSecs(($step + 1) * 5, 'refundCreate', [$oid, $pushUrl, $step + 1]);
        }
        return true;
    }

    /**
     * 取消申请
     * @param int $oid
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function refundCancel(int $oid, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('取消售后单' . $oid . '推送失败');
            return true;
        }

        try {
            /** @var OutStoreOrderRefundServices $services */
            $services = app()->make(OutStoreOrderRefundServices::class);
            if (!$services->cancelApplyPush($oid, $pushUrl)) {
                OutPushJob::dispatchSecs(($step + 1) * 5, 'refundCancel', [$oid, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            Log::error('取消售后单' . $oid . '推送失败,失败原因:' . $e->getMessage());
            OutPushJob::dispatchSecs(($step + 1) * 5, 'refundCancel', [$oid, $pushUrl, $step + 1]);
        }
        return true;
    }

    /**
     * 余额，积分，佣金，经验变动推送
     * @param array $data
     * @param string $pushUrl
     * @param int $step
     * @return bool
     */
    public function userUpdate(array $data, string $pushUrl, int $step = 0): bool
    {
        if ($step > 2) {
            Log::error('用户变动推送失败');
            return true;
        }

        try {
            /** @var UserServices $services */
            $services = app()->make(UserServices::class);
            if (!$services->userUpdate($data, $pushUrl)) {
                OutPushJob::dispatchSecs(($step + 1) * 5, 'userUpdate', [$data, $pushUrl, $step + 1]);
            }
        } catch (\Exception $e) {
            OutPushJob::dispatchSecs(($step + 1) * 5, 'userUpdate', [$data, $pushUrl, $step + 1]);
        }
        return true;
    }
}