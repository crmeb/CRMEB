<?php


namespace app\listener\user;


use app\jobs\RoutineTemplateJob;
use app\jobs\WechatTemplateJob as TemplateJob;
use app\services\coupon\StoreCouponIssueServices;
use app\services\user\UserBillServices;
use app\services\wechat\WechatUserServices;
use crmeb\interfaces\ListenerInterface;

/**
 * 注册完成后置事件
 * Class Register
 * @package app\listener\user
 */
class Register implements ListenerInterface
{
    /**
     * 注册完成后置事件
     * @param $event
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handle($event): void
    {
        [$spreadUid, $userType, $name, $uid, $isNew] = $event;

        if ($spreadUid) {
            if ($isNew) {
                //邀请新用户增加经验
                /** @var UserBillServices $userBill */
                $userBill = app()->make(UserBillServices::class);
                $userBill->inviteUserIncExp((int)$spreadUid);
            }

            //绑定成功发送消息
            /** @var  WechatUserServices $wechatServices */
            $wechatServices = app()->make(WechatUserServices::class);
            if (strtolower($userType) == 'routine') {
                $openid = $wechatServices->uidToOpenid($spreadUid, 'routine');
                RoutineTemplateJob::dispatchDo('sendBindSpreadUidSuccess', [$openid, $name ?? '']);
            } else {
                $openid = $wechatServices->uidToOpenid($spreadUid, 'wechat');
                TemplateJob::dispatchDo('sendBindSpreadUidSuccess', [$openid, $name ?? '']);
            }
        }

        if ($isNew) {
            //新人优惠券发送
            /**@var StoreCouponIssueServices $storeCoupon */
            $storeCoupon = app()->make(StoreCouponIssueServices::class);
            $storeCoupon->userFirstSubGiveCoupon((int)$uid);
        }
    }
}