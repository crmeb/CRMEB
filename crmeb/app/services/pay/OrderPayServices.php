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

namespace app\services\pay;


use app\services\order\OtherOrderServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderServices;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\ApiException;
use crmeb\utils\Str;

/**
 * 订单发起支付
 * Class OrderPayServices
 * @package app\services\pay
 */
class OrderPayServices
{
    /**
     * 支付
     * @var PayServices
     */
    protected $payServices;

    public function __construct(PayServices $services)
    {
        $this->payServices = $services;
    }

    /**
     * 订单发起支付
     * @param array $orderInfo
     * @param string $payType
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderPay(array $orderInfo, string $payType, array $options = [])
    {
        if ($orderInfo['paid']) {
            throw new ApiException(410174);
        }
        if ($orderInfo['pay_price'] <= 0) {
            throw new ApiException(410274);
        }

        $openid = '';
        if (!in_array($payType, ['weixinh5', 'pc', 'allinpay']) && !request()->isApp()) {
            if ($payType === 'weixin') {
                $userType = 'wechat';
            } else {
                $userType = $payType;
            }
            /** @var WechatUserServices $services */
            $services = app()->make(WechatUserServices::class);
            $openid = $services->uidToOpenid($orderInfo['pay_uid'] ?? $orderInfo['uid'], $userType);
            if (!$openid) {
                throw new ApiException(410275);
            }
        }
        $site_name = sys_config('site_name');
        if (isset($orderInfo['member_type'])) {
            $body = Str::substrUTf8($site_name . '--' . $orderInfo['member_type'], 20);
            $successAction = "member";
            /** @var OtherOrderServices $otherOrderServices */
            $otherOrderServices = app()->make(OtherOrderServices::class);
            $otherOrderServices->update($orderInfo['id'], ['pay_type' => 'alipay']);
        } else {
            /** @var StoreOrderCartInfoServices $orderInfoServices */
            $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
            $body = $orderInfoServices->getCarIdByProductTitle((int)$orderInfo['id']);
            $body = Str::substrUTf8($site_name . '--' . $body, 20);
            $successAction = "product";
            /** @var StoreOrderServices $orderServices */
            $orderServices = app()->make(StoreOrderServices::class);
            $orderServices->update($orderInfo['id'], ['pay_type' => 'weixin']);
        }

        if (!$body) {
            throw new ApiException(410276);
        }

        return $this->payServices->setOptions($options)->pay($payType, $openid, $orderInfo['order_id'], $orderInfo['pay_price'], $successAction, $body);
    }

    /**
     * 支付宝支付
     * @param array $orderInfo
     * @param string $quitUrl
     * @return array|string
     */
    public function alipayOrder(array $orderInfo, string $quitUrl, bool $isCode = false)
    {
        if ($orderInfo['paid']) {
            throw new ApiException(410174);
        }
        if ($orderInfo['pay_price'] <= 0) {
            throw new ApiException(410274);
        }
        $site_name = sys_config('site_name');
        if (isset($orderInfo['member_type'])) {
            $body = Str::substrUTf8($site_name . '--' . $orderInfo['member_type'], 30);
            $successAction = "member";
            /** @var OtherOrderServices $otherOrderServices */
            $otherOrderServices = app()->make(OtherOrderServices::class);
            $otherOrderServices->update($orderInfo['id'], ['pay_type' => 'alipay']);
        } else {
            /** @var StoreOrderCartInfoServices $orderInfoServices */
            $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
            $body = $orderInfoServices->getCarIdByProductTitle((int)$orderInfo['id']);
            $body = Str::substrUTf8($site_name . '--' . $body, 30);
            $successAction = "product";
            /** @var StoreOrderServices $orderServices */
            $orderServices = app()->make(StoreOrderServices::class);
            $orderServices->update($orderInfo['id'], ['pay_type' => 'alipay']);
        }

        if (!$body) {
            throw new ApiException(410276);
        }

        return $this->payServices->pay('alipay', $quitUrl, $orderInfo['order_id'], $orderInfo['pay_price'], $successAction, $body, $isCode);
    }
}
