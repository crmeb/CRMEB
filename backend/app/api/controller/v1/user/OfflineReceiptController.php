<?php
declare(strict_types=1);

namespace app\api\controller\v1\user;

use app\services\user\OfflineReceiptServices;
use app\services\pay\OrderPayServices;
use app\services\pay\PayCredentialService;
use app\services\pay\PayServices;
use app\services\pay\YuePayServices;
use app\services\driver\pay\PayChannelServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserServices;
use app\Request;

/**
 * 线下收银控制器
 * 负责线下扫码收款单的创建、会员折扣计算、支付方式查询
 * 会员卡购买订单由 v1\user\MemberCardController::createOrder 处理
 */
class OfflineReceiptController
{
    protected $services;
    protected $channelType = ['weixin' => 'wechat', 'weixinh5' => 'weixinh5', 'routine' => 'routine'];

    public function __construct(OfflineReceiptServices $services)
    {
        $this->services = $services;
    }

    /**
     * 计算线下收银会员优惠价
     * 付费会员且开启线下折扣时，按会员卡折扣率计算优惠后金额
     * @param Request $request
     * @return mixed
     */
    public function computedOfflinePayPrice(Request $request)
    {
        list($pay_price) = $request->getMore([['pay_price', 0]], true);
        $old_price = $pay_price;
        if (!$pay_price || !is_numeric($pay_price)) return app('json')->fail('请输入付款金额');
        $uid = $request->uid();
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $user_info = $userService->get($uid, ['is_money_level']);
        if ($user_info->is_money_level > 0) {
            /** @var MemberCardServices $memberCardService */
            $memberCardService = app()->make(MemberCardServices::class);
            $offline_rule_number = $memberCardService->isOpenMemberCard('offline');
            if ($offline_rule_number) {
                $pay_price = bcmul($pay_price, bcdiv($offline_rule_number, '100', 2), 2);
            }
        }
        $show = true;
        if ($old_price == $pay_price) $show = false;
        return app('json')->success(['pay_price' => $pay_price, 'show' => $show]);
    }

    /**
     * 创建线下收银订单
     * 用户扫码自填金额创建收款单（type=3），支持余额/微信/支付宝/线下付款
     * @param Request $request
     * @return mixed
     */
    public function create(Request $request)
    {
        $uid = (int)$request->uid();
        /** @var OfflineReceiptServices $offlineReceiptServices */
        $offlineReceiptServices = app()->make(OfflineReceiptServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        [$payType, $type, $from, $price, $money, $quitUrl] = $request->postMore([
            ['pay_type', 'yue'],
            ['type', 3],
            ['from', 'weixin'],
            ['price', 0.00],
            ['money', 0.00],
            ['quitUrl', ''],
        ], true);
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', (string)$money) || bccomp((string)$money, '0', 2) <= 0) {
            return app('json')->fail('请输入有效的付款金额');
        }
        // 原价由用户自填，会员优惠必须在服务端按当前权益重新计算。
        $price = bcadd((string)$money, '0', 2);
        $user = $userServices->get($uid, ['is_money_level']);
        if ((int)$user['is_money_level'] > 0) {
            $discount = app()->make(MemberCardServices::class)->isOpenMemberCard('offline');
            if ($discount && bccomp((string)$discount, '0', 2) > 0 && bccomp((string)$discount, '100', 2) <= 0) {
                $price = bcmul($price, bcdiv((string)$discount, '100', 4), 2);
            }
        }
        if (bccomp($price, '0', 2) <= 0) return app('json')->fail('支付金额不能为0元');
        $payType = strtolower($payType);
        $channelType = $userServices->getUserInfo($uid)['user_type'];
        //线下收银订单创建（固定 type=3）
        $order = $offlineReceiptServices->createOrder($uid, $channelType, false, $price, $payType, 3, $money, 0);
        if ($order === false) return app('json')->fail('支付数据生成失败');
        //线下收银订单号存 order_no 列
        $order_id = $order['order_no'];
        $orderInfo = $offlineReceiptServices->getOne(['order_no' => $order_id]);
        if (!$orderInfo) return app('json')->fail('支付订单不存在');
        $orderInfo = $orderInfo->toArray();
        //线下表无 pay_type 列，附加支付方式供支付链路识别余额支付
        $orderInfo['pay_type'] = $payType;
        $orderInfo['pay_price'] = (string)$orderInfo['paid_price'];
        $orderInfo['order_id'] = $orderInfo['order_no'];

        $info = compact('order_id');

        if (bcsub((string)$orderInfo['pay_price'], '0', 2) <= 0) {
            //线下收银无支付金额直接支付成功
            $payPriceStatus = $offlineReceiptServices->paySuccess($orderInfo, PayServices::YUE_PAY);
            if ($payPriceStatus)
                return app('json')->status('success', '支付成功', $info);
            else
                return app('json')->status('pay_error');
        }

        if ($order_id) {
            switch ($payType) {
                case PayServices::YUE_PAY:
                    /** @var YuePayServices $yueServices */
                    $yueServices = app()->make(YuePayServices::class);
                    $pay = $yueServices->yueOrderPay($orderInfo, $uid);
                    if ($pay['status'] === true)
                        return app('json')->status('success', '余额支付成功', $info);
                    else {
                        if (is_array($pay))
                            return app('json')->status($pay['status'], $pay['msg'], $info);
                        else
                            return app('json')->status('pay_error', $pay);
                    }
                case PayServices::OFFLINE_PAY:
                    return app('json')->status('success', '前往支付', $info);
                default:
                    $payOptions = array_merge(PayCredentialService::getCredentials($uid, $payType), ['quitUrl' => $quitUrl]);
                    $payServices = app()->make(OrderPayServices::class);
                    $payInfo = $payServices->beforePay($order->toArray(), $payType, $payOptions);
                    return app('json')->status($payInfo['status'], $payInfo['payInfo']);
            }
        } else return app('json')->fail('订单生成失败');
    }

    /**
     * 获取线下收银支付方式配置
     * 返回支付宝/微信/线下/余额支付开关及站点信息、用户余额
     * @param Request $request
     * @return mixed
     */
    public function payType(Request $request)
    {
        /** @var PayChannelServices $payChannelServices */
        $payChannelServices = app()->make(PayChannelServices::class);
        $payType['ali_pay_status'] = $payChannelServices->getChannelStatus('alipay');
        $payType['pay_weixin_open'] = $payChannelServices->getChannelStatus('weixin') || $payChannelServices->getChannelStatus('weixin_v3');
        $payType['site_name'] = sys_config('site_name');
        $payType['now_money'] = $request->user('now_money');
        $payType['offline_pay_status'] = $payChannelServices->getChannelStatus('offline');
        $payType['yue_pay_status'] = $payChannelServices->getChannelStatus('yue') ? 1 : 0;
        return app('json')->success($payType);
    }
}
