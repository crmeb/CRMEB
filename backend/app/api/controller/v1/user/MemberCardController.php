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

namespace app\api\controller\v1\user;

use app\Request;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\pay\OrderPayServices;
use app\services\pay\PayCredentialService;
use app\services\pay\PayServices;
use app\services\pay\YuePayServices;
use app\services\user\MemberOrderServices;
use app\services\other\AgreementServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserServices;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/** 会员卡
 * Class MemberCardController
 * @package app\api\controller\v1\user
 */
class MemberCardController
{
    /**
     * @var MemberCardServices
     */
    protected $services;

    protected $channelType = ['weixin' => 'wechat', 'weixinh5' => 'weixinh5', 'routine' => 'routine', 'h5' => 'h5'];

    /** 初始化service层句柄
     * MemberCardController constructor.
     * @param MemberCardServices $memberCardServices
     */
    public function __construct(MemberCardServices $memberCardServices)
    {
        $this->services = $memberCardServices;
    }

    /**
     * 会员卡主页数据接口
     * @param Request $request
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(Request $request)
    {
        $member_rights = $this->services->getMemberRightsInfo();
        /** @var AgreementServices $agreementService */
        $agreementService = app()->make(AgreementServices::class);
        $member_explain = $agreementService->getAgreementBytype(1);
        $member_explain = (isset($member_explain['status']) && $member_explain['status'] == 1) ? $member_explain : "";
        $member_type = $this->services->DoMemberType();
        /** @var MemberOrderServices $MemberOrderServices */
        $MemberOrderServices = app()->make(MemberOrderServices::class);
        $is_get_free = $MemberOrderServices->isCanGetFree((int)$request->uid());
        /** @var StoreCouponUserServices $couponUserService */
        $couponUserService = app()->make(StoreCouponUserServices::class);
        $coupons = $couponUserService->getMemberCoupon($request->uid());
        return app('json')->success([
            'member_rights' => $member_rights['member_right'],
            'is_get_free' => $is_get_free,
            'member_explain' => $member_explain,
            'member_type' => $member_type,
            'member_coupons' => $coupons
        ]);
    }

    /**
     * 卡密领取会员卡
     * @param Request $request
     * @return mixed
     */
    public function drawMemberCard(Request $request)
    {
        $data = $request->postMore([
            ['member_card_code', ''],
            ['member_card_pwd', ''],
            ['from', 'weixin'],
        ]);
        $data['from'] = strtolower(trim($data['from']));
        if (!array_key_exists($data['from'], $this->channelType)) return app('json')->fail('非法操作');
        $data['from'] = $this->channelType[$data['from']];
        $uid = (int)$request->uid();
        $this->services->drawMemberCard($data, $uid);
        return app('json')->success('激活成功');
    }

    /**
     * 会员券接口
     * @param Request $request
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function memberCouponList(Request $request)
    {
        /** @var StoreCouponUserServices $couponUserService */
        $couponUserService = app()->make(StoreCouponUserServices::class);
        $coupons = $couponUserService->getMemberCoupon($request->uid());
        return app('json')->success($coupons);
    }

    /**
     * 计算会员天数
     * @param Request $request
     * @return mixed
     */
    public function getOverdueTime(Request $request)
    {
        $params = $request->getMore([
            ['member_type', ''],
            ['vip_day', ''],
            ['mc_id', 0]
        ]);
        $member_type = $params['member_type'];
        $vip_day = $params['vip_day'];
        /** @var \app\services\user\UserServices $userServices */
        $userServices = app()->make(\app\services\user\UserServices::class);
        $user_info = $userServices->getUserInfo($request->uid());
        if ($member_type == 'ever') {
            $overdue_time = 0;
            $is_ever_level = 1;
        } else {
            if ($user_info['is_money_level'] == 0) {
                $overdue_time = bcadd(bcmul($vip_day, 86400, 0), time(), 0);
            } else {
                $overdue_time = bcadd(bcmul($vip_day, 86400, 0), $user_info['overdue_time'], 0);
            }
            $is_ever_level = 0;
        }
        if ($is_ever_level == 1 || $user_info['is_ever_level']) {
            $res = "永久会员";
        } else {
            $res = date('Y-m-d', $overdue_time);
        }

        return app('json')->success(['data' => $res]);
    }

    /**
     * 购买会员卡创建订单
     * 创建会员购买订单（type=1/2 等，走 MemberOrderServices），支持余额/微信/支付宝/线下付款
     * @param Request $request
     * @return mixed
     */
    public function createOrder(Request $request)
    {
        $uid = (int)$request->uid();
        /** @var MemberOrderServices $memberOrderServices */
        $memberOrderServices = app()->make(MemberOrderServices::class);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        [$payType, $type, $from, $memberType, $price, $money, $quitUrl, $mcId] = $request->postMore([
            ['pay_type', 'yue'],
            ['type', 1],
            ['from', 'weixin'],
            ['member_type', ''],
            ['price', 0.00],
            ['money', 0.00],
            ['quitUrl', ''],
            ['mc_id', 0]
        ], true);
        if ($money <= 0.00) return app('json')->fail('支付金额不能为0元');
        $payType = strtolower($payType);
        if (in_array($type, [1, 2])) {
            /** @var MemberCardServices $memberCardService */
            $memberCardService = app()->make(MemberCardServices::class);
            $isOpenMember = $memberCardService->isOpenMemberCard();
            if (!$isOpenMember) return app('json')->fail('付费会员功能暂未开启');
        }
        $channelType = $userServices->getUserInfo($uid)['user_type'];
        //会员订单创建（订单号存 order_id 列）
        $order = $memberOrderServices->createOrder($uid, $channelType, $memberType, $price, $payType, $type, $money, $mcId);
        if ($order === false) return app('json')->fail('支付数据生成失败');
        $order_id = $order['order_id'];
        $orderInfo = $memberOrderServices->getOne(['order_id' => $order_id]);
        if (!$orderInfo) return app('json')->fail('支付订单不存在');
        $orderInfo = $orderInfo->toArray();

        $info = compact('order_id');

        if (bcsub((string)$orderInfo['pay_price'], '0', 2) <= 0) {
            //会员免费领取走零元支付
            $payPriceStatus = $memberOrderServices->zeroYuanPayment($orderInfo);
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


}
