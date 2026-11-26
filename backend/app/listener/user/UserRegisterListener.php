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
namespace app\listener\user;


use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\user\UserBillServices;
use app\services\user\UserServices;
use app\services\user\UserSpreadServices;
use crmeb\interfaces\ListenerInterface;

/**
 * 用户注册后置事件
 * Class UserRegisterListener
 * @package app\listener\user
 */
class UserRegisterListener implements ListenerInterface
{
    /**
     * 用户注册后置事件
     * @param $event
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handle($event): void
    {
        [$spreadUid, $userType, $name, $uid, $isNew] = $event;

        $spreadUid = (int)$spreadUid;
        $uid = (int)$uid;
        if ($spreadUid > 0 && $uid > 0) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getUserInfo($uid);
            $legacy = $userInfo ? (is_object($userInfo) ? $userInfo->toArray() : (array)$userInfo) : [];
            $relation = event('DistributionServiceProxy', [
                'service' => 'DistributionRelationServices',
                'action' => 'relation',
                'args' => [$uid, $legacy],
            ]);
            $relationRow = is_array($relation[0] ?? null) ? $relation[0] : [];
            $decision = $userServices->decideSpreadBind($uid, $spreadUid, [
                'is_new' => (bool)$isNew,
                'current_spread_uid' => (int)($relationRow['spread_uid'] ?? ($legacy['spread_uid'] ?? 0)),
                'current_spread_time' => (int)($relationRow['spread_time'] ?? ($legacy['spread_time'] ?? 0)),
            ]);
            if ($decision['allow']) {
                if ($isNew) {
                    /** @var UserBillServices $userBill */
                    $userBill = app()->make(UserBillServices::class);
                    $userBill->inviteUserIncExp($spreadUid);
                    event('UserRegisterDistributionJob', ['uid' => $uid]);
                }
                $handled = event('DistributionServiceProxy', [
                    'service' => 'DistributionRelationServices',
                    'action' => 'bind',
                    'args' => [$uid, $spreadUid, time()],
                ]);
                if (!is_array($handled) || ($handled[0] ?? null) === null) {
                    /** @var UserSpreadServices $userSpreadServices */
                    $userSpreadServices = app()->make(UserSpreadServices::class);
                    $userSpreadServices->setSpread($uid, $spreadUid);
                }
            }
        }

        if ($isNew) {
            /**@var StoreCouponIssueServices $storeCoupon */
            $storeCoupon = app()->make(StoreCouponIssueServices::class);
            $storeCoupon->userFirstSubGiveCoupon((int)$uid);

            if (sys_config('brokerage_func_status') && sys_config('store_brokerage_statu') == 2) {
                $handled = event('DistributionServiceProxy', [
                    'service' => 'DistributionUserServices',
                    'action' => 'saveProfile',
                    'args' => [(int)$uid, ['is_promoter' => 1, 'spread_open' => 1]],
                ]);
                // is_promoter 列已移除：插件未接管时不落用户表
            }
        }
    }
}
