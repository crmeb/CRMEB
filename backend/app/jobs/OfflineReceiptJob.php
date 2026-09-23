<?php
declare(strict_types=1);

namespace app\jobs;

use app\services\user\OfflineReceiptServices;
use app\services\order\OrderLifecycleServices;
use app\services\user\MemberOrderServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserServices;
use app\services\user\UserBillServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;

/**
 * 线下收银订单异步任务
 * 处理线下收银订单（会员订单共用）支付成功后的异步操作
 */
class OfflineReceiptJob extends BaseJobs
{
    /**
     * 执行异步任务
     * 依次执行：更新用户订单数、计算节省金额、返还消费积分
     * @param array $order 订单数据
     * @return bool 返回任务执行结果
     */
    public function doJob($order)
    {
        try {
            $this->setUserPayCountAndPromoter($order);
        } catch (\Throwable $e) {
            Log::error('更新用户订单数失败,失败原因:' . $e->getMessage());
        }

        try {
            $this->setEconomizeMoney($order);
        } catch (\Throwable $e) {
            Log::error('计算节省金额,失败原因:' . $e->getMessage());
        }

        try {
            $this->sendMemberIntegral($order);
        } catch (\Throwable $e) {
            Log::error('消费积分返还失败,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 更新用户订单数和推广员状态
     * 增加用户订单计数，并判断是否达到推广员门槛
     * @param array $order 订单数据
     */
    public function setUserPayCountAndPromoter($order)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($order['uid']);
        if ($userInfo) {
            $userInfo->pay_count = $userInfo->pay_count + 1;
            if (!$userInfo->is_promoter) {
                /** @var OfflineReceiptServices $orderServices */
                $orderServices = app()->make(OfflineReceiptServices::class);
                /** @var MemberOrderServices $memberOrderServices */
                $memberOrderServices = app()->make(MemberOrderServices::class);
                // 累计消费金额 = 会员订单 + 线下收银订单（两表合计）
                $price = $orderServices->sum(['paid' => 1, 'uid' => $userInfo['uid']], 'paid_price')
                    + $memberOrderServices->sum(['paid' => 1, 'uid' => $userInfo['uid']], 'pay_price');
                $status = is_brokerage_statu($price);
                if ($status) {
                    $userInfo->is_promoter = 1;
                }
            }
            $userInfo->save();
        }
    }

    /**
     * 发送会员积分
     * 线下收银订单（type=3）根据配置返还积分，会员享受额外倍数
     * @param array $order 订单数据
     * @return bool 返回积分发送结果
     */
    public function sendMemberIntegral($order)
    {
        if ($order['type'] == 3) {
            $order_give_integral = sys_config('order_give_integral');
            $order_integral = bcmul($order_give_integral, (string)$order['pay_price'], 0);
            /** @var UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userInfo = $userService->getUserInfo($order['uid']);
            if (!$userInfo) return false;
            if ($userInfo['is_money_level'] > 0) {
                /** @var MemberCardServices $memberCardService */
                $memberCardService = app()->make(MemberCardServices::class);
                $integral_rule_number = $memberCardService->isOpenMemberCard('integral');
                if ($integral_rule_number) {
                    $order_integral = bcadd($order_integral, $integral_rule_number, 2);
                }
            }
            if ($order_integral > 0) {
                /** @var UserBillServices $userBillServices */
                $userBillServices = app()->make(UserBillServices::class);
                $userBillServices->billIntegralIncome('order_give_integral', $order['uid'], $order_integral, $order['id']);
            }
        }
    }

    /**
     * 设置节省金额（转调订单生命周期服务）
     * 线下收银订单（type=3）计算会员线下支付节省的金额
     * @param array $order 订单数据
     * @return bool
     */
    public function setEconomizeMoney($order)
    {
        /** @var OrderLifecycleServices $lifecycleServices */
        $lifecycleServices = app()->make(OrderLifecycleServices::class);
        return $lifecycleServices->recordOfflineEconomize((array)$order);
    }
}
