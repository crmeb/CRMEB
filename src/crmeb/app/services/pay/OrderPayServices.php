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

namespace app\services\pay;


use app\services\order\StoreOrderCartInfoServices;
use app\services\wechat\WechatUserServices;
use crmeb\utils\Str;
use think\exception\ValidateException;

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
     * @return array|string
     */
    public function orderPay(array $orderInfo, string $payType)
    {
        if ($orderInfo['paid']) {
            throw new ValidateException('订单已支付!');
        }
        if ($orderInfo['pay_price'] <= 0) {
            throw new ValidateException('该支付无需支付!');
        }
        if ($payType !== 'weixinh5' && $payType !== 'pc') {
            if ($payType === 'weixin') {
                $userType = 'wechat';
            } else {
                $userType = $payType;
            }
            /** @var WechatUserServices $services */
            $services = app()->make(WechatUserServices::class);
            $openid = $services->uidToOpenid($orderInfo['uid'], $userType);
            if (!$openid) {
                throw new ValidateException('获取用户openid失败,无法支付');
            }
        } else {
            $openid = '';
        }
        $site_name = sys_config('site_name');
        if (isset($orderInfo['member_type'])) {
            $body = Str::substrUTf8($site_name . '--' . $orderInfo['member_type'], 30);
            $successAction = "member";
        } else {
            /** @var StoreOrderCartInfoServices $orderInfoServices */
            $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
            $body = $orderInfoServices->getCarIdByProductTitle($orderInfo['cart_id']);
            $body = Str::substrUTf8($site_name . '--' . $body, 30);
            $successAction = "product";
        }

        if (!$body) {
            throw new ValidateException('支付参数缺少：请前往后台设置->系统设置-> 填写 网站名称');
        }
        return $this->payServices->pay($payType, $openid, $orderInfo['order_id'], $orderInfo['pay_price'], $successAction, $body);
    }

}
