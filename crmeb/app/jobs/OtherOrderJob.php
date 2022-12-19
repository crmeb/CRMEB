<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\jobs;

use app\services\order\OtherOrderServices;
use app\services\order\StoreOrderEconomizeServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

/**
 * 订单消息队列
 * Class OrderJob
 * @package crmeb\jobs
 */
class OtherOrderJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 执行订单支付成功发送消息
     * @param $order
     * @return bool
     */
    public function doJob($order)
    {
        //更新用户支付订单数量
        try {
            $this->setUserPayCountAndPromoter($order);
        } catch (\Throwable $e) {
            Log::error('更新用户订单数失败,失败原因:' . $e->getMessage());
        }

        // 计算用户节省金额
        try {
            $this->setEconomizeMoney($order);
        } catch (\Throwable $e) {
            Log::error('计算节省金额,失败原因:' . $e->getMessage());
        }

        //收银订单赠送积分
        try {
            $this->sendMemberIntegral($order);
        } catch (\Throwable $e) {
            Log::error('消费积分返还失败,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 设置用户购买次数和检测时候成为推广人
     * @param $order
     */
    public function setUserPayCountAndPromoter($order)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($order['uid']);
        if ($userInfo) {
            $userInfo->pay_count = $userInfo->pay_count + 1;
            if (!$userInfo->is_promoter) {
                /** @var OtherOrderServices $orderServices */
                $orderServices = app()->make(OtherOrderServices::class);
                $price = $orderServices->sum(['paid' => 1, 'uid' => $userInfo['uid']], 'pay_price');
                $status = is_brokerage_statu($price);
                if ($status) {
                    $userInfo->is_promoter = 1;
                }
            }
            $userInfo->save();
        }
    }

    /** 线下付款奖励积分
     * @param $order
     * @return bool
     */
    public function sendMemberIntegral($order)
    {
        //只有线下付款才奖励
        if ($order['type'] == 3) {
            $order_give_integral = sys_config('order_give_integral');
            $order_integral = bcmul($order_give_integral, (string)$order['pay_price'], 0);
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userInfo = $userService->getUserInfo($order['uid']);
            if (!$userInfo) return false;
            if ($userInfo['is_money_level'] > 0) {
                //看是否开启消费返积分翻倍奖励
                /** @var MemberCardServices $memberCardService */
                $memberCardService = app()->make(MemberCardServices::class);
                $integral_rule_number = $memberCardService->isOpenMemberCard('integral');
                if ($integral_rule_number) {
                    $order_integral = bcadd($order_integral, $integral_rule_number, 2);
                }
            }
            if ($order_integral > 0) {
                $integral = bcadd(abs($userInfo['integral']), abs($order_integral), 2);
                $userService->update(['uid' => $order['uid']], ['integral' => $integral]);
            }
        }
    }

    /**
     * 计算节省金额
     * @param $order
     */
    public function setEconomizeMoney($order)
    {
        //只有线下付款才计算节省
        if ($order['type'] == 3) {
            /** @var StoreOrderEconomizeServices $economizeService */
            $economizeService = app()->make(StoreOrderEconomizeServices::class);
            /** @var MemberCardServices $memberRightService */
            $memberRightService = app()->make(MemberCardServices::class);
            $isOpenOfflin = $memberRightService->isOpenMemberCard('offline');
            if ($isOpenOfflin) {
                $save = [
                    'uid' => $order['uid'],
                    'order_id' => $order['order_id'],
                    'order_type' => 2,
                    'pay_price' => $order['pay_price'],
                    'offline_price' => bcsub($order['money'], $order['pay_price'], 2)
                ];
                $economizeService->addEconomize($save);
            }

        }
    }
}
