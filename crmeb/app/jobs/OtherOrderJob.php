<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\jobs;

use app\services\coupon\StoreCouponIssueServices;
use app\services\message\sms\SmsSendServices;
use app\services\order\OtherOrderServices;
use app\services\order\StoreOrderEconomizeServices;
use app\services\order\StoreOrderServices;
use app\services\user\MemberCardServices;
use app\services\user\MemberRightServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;
use think\facade\Log;

/**
 * 订单消息队列
 * Class OrderJob
 * @package app\jobs
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
        //发送模版消息
        try {
            $this->sendServicesAndTemplate($order);
        } catch (\Throwable $e) {
            Log::error('发送客服消息,短信消息失败,失败原因:' . $e->getMessage());
        }
        //支付成功发送短信
        $this->mssageSendPaySuccess($order);
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


    /**
     * 发送模板消息和客服消息
     * @param $order
     * @return bool
     */
    public function sendServicesAndTemplate($order)
    {
        try {
            /** @var WechatUserServices $wechatUserServices */
            $wechatUserServices = app()->make(WechatUserServices::class);
            if ($order['is_channel'] == "wechat") {//公众号发送模板消息
                $openid = $wechatUserServices->uidToOpenid($order['uid'], 'wechat');
                if (!$openid) {
                    return true;
                }
                $wechatTemplate = new WechatTemplateJob();
                $wechatTemplate->sendOrderPaySuccess($openid, $order);
            } else if ($order['is_channel'] == "routine") {//小程序发送模板消息
                $openid = $wechatUserServices->uidToOpenid($order['uid'], 'routine');
                if (!$openid) {
                    return true;
                }
                $tempJob = new RoutineTemplateJob();
                $tempJob->sendMemberOrderSuccess($openid, $order['pay_price'], $order['order_id']);
            }
        } catch (\Exception $e) {
        }

    }

    /**
     *  支付成功短信提醒
     * @param string $order_id
     */
    public function mssageSendPaySuccess($order)
    {
        $switch = sys_config('lower_order_switch') ? true : false;
        //模板变量
        $pay_price = $order['pay_price'];
        $order_id = $order['order_id'];
        /** @var SmsSendServices $smsServices */
        $smsServices = app()->make(SmsSendServices::class);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userPhone = $userService->value(['uid' => $order['uid']], 'phone');
        if ($userPhone) {
            $smsServices->send($switch, $userPhone, compact('order_id', 'pay_price'), 'PAY_SUCCESS_CODE');
        }

    }


}
