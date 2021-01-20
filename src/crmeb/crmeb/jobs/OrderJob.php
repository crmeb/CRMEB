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

namespace crmeb\jobs;

use app\services\coupon\StoreCouponUserServices;
use app\services\message\sms\SmsSendServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderEconomizeServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserLabelRelationServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\basic\BaseJob;
use crmeb\services\WechatService;
use crmeb\services\workerman\ChannelService;
use think\facade\Log;

/**
 * 订单消息队列
 * Class OrderJob
 * @package crmeb\jobs
 */
class OrderJob extends BaseJob
{
    /**
     * 执行订单支付成功发送消息
     * @param $order
     * @return bool
     */
    public function doJob($order)
    {
        //计算商品节省金额
        try {
            $this->setEconomizeMoney($order);
        } catch (\Throwable $e) {
            Log::error('计算节省金额,失败原因:' . $e->getMessage());
        };
        //更新用户支付订单数量
        try {
            $this->setUserPayCountAndPromoter($order);
        } catch (\Throwable $e) {
            Log::error('更新用户订单数失败,失败原因:' . $e->getMessage());
        }
        //增加用户标签
        try {
            $this->setUserLabel($order);
        } catch (\Throwable $e) {
            Log::error('用户标签添加失败,失败原因:' . $e->getMessage());
        }
        //发送模版消息、客服消息、短信、小票打印给客户和管理员
        try {
            $this->sendServicesAndTemplate($order);
        } catch (\Throwable $e) {
            Log::error('发送客服消息,短信消息失败,失败原因:' . $e->getMessage());
        }
        //支付成功发送短信
        try {
            $this->mssageSendPaySuccess($order);
        } catch (\Throwable $e) {
            Log::error('支付成功发送短信失败,失败原因:' . $e->getMessage());
        }
        //向后台发送新订单消息
        try {
            ChannelService::instance()->send('NEW_ORDER', ['order_id' => $order['order_id']]);
        } catch (\Throwable $e) {
            Log::error('向后台发送新订单消息失败,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 设置用户购买次数
     * @param $order
     */
    public function setUserPayCountAndPromoter($order)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($order['uid']);
        if ($userInfo) {
            $userInfo->pay_count = $userInfo->pay_count + 1;
            $userInfo->save();
        }
    }

    /**
     * 设置用户购买的标签
     * @param $order
     */
    public function setUserLabel($order)
    {
        /** @var StoreOrderCartInfoServices $cartInfoServices */
        $cartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $productIds = $cartInfoServices->getCartColunm(['oid' => $order['id']], 'product_id', '');
        /** @var StoreProductServices $productServices */
        $productServices = app()->make(StoreProductServices::class);
        $label = $productServices->getColumn([['id', 'in', $productIds]], 'label_id');
        $labelIds = array_unique(explode(',', implode(',', $label)));
        /** @var UserLabelRelationServices $labelServices */
        $labelServices = app()->make(UserLabelRelationServices::class);
        $where = [
            ['label_id', 'in', $labelIds],
            ['uid', '=', $order['uid']]
        ];
        $data = [];
        $userLabel = $labelServices->getColumn($where, 'label_id');
        foreach ($labelIds as $item) {
            if (!in_array($item, $userLabel)) {
                $data[] = ['uid' => $order['uid'], 'label_id' => $item];
            }
        }
        $re = true;
        if ($data) {
            $re = $labelServices->saveAll($data);
        }
        return $re;
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
            if (in_array($order['is_channel'], [0, 2])) {//公众号发送模板消息
                $openid = $wechatUserServices->uidToOpenid($order['uid'], 'wechat');
                if (!$openid) {
                    return true;
                }
                $wechatTemplate = new WechatTemplateJob();
                $wechatTemplate->sendOrderPaySuccess($openid, $order);

            } else if (in_array($order['is_channel'], [1, 2])) {//小程序发送模板消息
                $openid = $wechatUserServices->uidToOpenid($order['uid'], 'routine');
                if (!$openid) {
                    return true;
                }
                $tempJob = new RoutineTemplateJob();
                $tempJob->sendOrderSuccess($openid, $order['pay_price'], $order['order_id']);
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
        $smsServices->send($switch, $order['user_phone'], compact('order_id', 'pay_price'), 'PAY_SUCCESS_CODE');
    }

    /**计算节约金额
     * @param $order
     */
    public function setEconomizeMoney($order)
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        /** @var StoreCouponUserServices $couponService */
        $couponService = app()->make(StoreCouponUserServices::class);
        /** @var StoreOrderEconomizeServices $economizeService */
        $economizeService = app()->make(StoreOrderEconomizeServices::class);
        $getOne = $economizeService->getOne(['order_id' => $order['order_id']]);
        if ($getOne) return false;
        //看是否是会员
        $userInfo = $userService->getUserInfo($order['uid']);
        if ($userInfo && $userInfo['is_money_level'] > 0) {
            $save = [];
            $save['order_type'] = 1;
            $save['add_time'] = time();
            $save['pay_price'] = $order['pay_price'];
            $save['order_id'] = $order['order_id'];
            $save['uid'] = $order['uid'];

            //计算会员券节省金额
            if ($order['coupon_id']) {
                $couponMoney = $couponService->getCouponUserOne($order['coupon_id']);
                if ($couponMoney) {
                    $save['coupon_price'] = $couponMoney['coupon_price'];
                }
            }
            return $economizeService->addEconomize($save);
        }
        return false;

    }
}
