<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
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
use crmeb\services\CacheService;
use crmeb\services\pay\extend\allinpay\AllinPay;
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
     * 获取支付方式
     * @param string $payType
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/15
     */
    public function getPayType(string $payType)
    {
        //微信支付没有开启，通联支付开启，用户访问端在小程序或者公众号的时候，使用通联微信H5支付
        if ($payType == PayServices::WEIXIN_PAY && !request()->isH5() && !request()->isApp()) {
            $payType = sys_config('pay_weixin_open', 0);
        }

        //支付宝没有开启，通联支付开了，用户使用支付宝支付，并且在app端访问的时候，使用通联app支付宝支付
        if ($payType == PayServices::ALIAPY_PAY && request()->isApp()) {
            $payType = sys_config('ali_pay_status', 0);
        }

        return $payType;
    }

    /**
     * 获取返回类型
     * @param string $payType
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/15
     */
    public function payStatus(string $payType)
    {
        if ($payType == PayServices::WEIXIN_PAY) {
            if (request()->isH5()) {
                $payStstus = 'wechat_h5_pay';
            } else if (request()->isPc()) {
                $payStstus = 'wechat_pc_pay';
            } else {
                $payStstus = 'wechat_pay';
            }
        } else if ($payType == PayServices::ALIAPY_PAY) {
            $payStstus = 'alipay_pay';
        } else if ($payType == PayServices::ALLIN_PAY) {
            $payStstus = 'allinpay_pay';
        } else {
            throw new ValidateException('获取支付返回类型失败');
        }
        return $payStstus;
    }

    /**
     * 发起支付前
     * @param array $orderInfo
     * @param string $payType
     * @param array $options
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/15
     */
    public function beforePay(array $orderInfo, string $payType, array $options = [])
    {
        $wechat = $payType == PayServices::WEIXIN_PAY;

        $payType = $this->getPayType($payType);

        if ($orderInfo['paid']) {
            throw new ApiException(410174);
        }
        if ($orderInfo['pay_price'] <= 0) {
            throw new ApiException(410274);
        }

        switch ($payType) {
            case PayServices::WEIXIN_PAY:
                $openid = '';
                if (request()->isWechat() || request()->isRoutine()) {
                    if (request()->isWechat()) {
                        $userType = 'wechat';
                    } else {
                        $userType = 'routine';
                    }
                    /** @var WechatUserServices $services */
                    $services = app()->make(WechatUserServices::class);
                    $openid = $services->uidToOpenid($orderInfo['pay_uid'] ?? $orderInfo['uid'], $userType);
                    if (!$openid) {
                        throw new ApiException(410275);
                    }
                }
                $options['openid'] = $openid;
                break;
            case PayServices::ALLIN_PAY:
                if ($wechat) {
                    $options['wechat'] = $wechat;
                }
                break;
        }


        $site_name = sys_config('site_name');
        if (isset($orderInfo['member_type'])) {
            $body = Str::substrUTf8($site_name . '--' . $orderInfo['member_type'], 20);
            $successAction = "member";
            /** @var OtherOrderServices $otherOrderServices */
            $otherOrderServices = app()->make(OtherOrderServices::class);
            $otherOrderServices->update($orderInfo['id'], ['pay_type' => $payType]);
        } else {
            /** @var StoreOrderCartInfoServices $orderInfoServices */
            $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
            $body = $orderInfoServices->getCarIdByProductTitle((int)$orderInfo['id']);
            $body = Str::substrUTf8($site_name . '--' . $body, 20);
            $successAction = "product";
            /** @var StoreOrderServices $orderServices */
            $orderServices = app()->make(StoreOrderServices::class);
            $orderServices->update($orderInfo['id'], ['pay_type' => $payType]);
        }

        if (!$body) {
            throw new ApiException(410276);
        }

        //发起支付
        $jsConfig = $this->payServices->pay($payType, $orderInfo['order_id'], $orderInfo['pay_price'], $successAction, $body, $options);

        //发起支付后处理返回参数
        $payInfo = $this->afterPay($orderInfo, $jsConfig, $payType);
        $statusType = $this->payStatus($payType);

        return [
            'status' => $statusType,
            'payInfo' => $payInfo,
        ];
    }

    /**
     * 支付发起后处理返回参数
     * @param $order
     * @param $jsConfig
     * @param string $payType
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/15
     */
    public function afterPay($order, $jsConfig, string $payType)
    {
        $payKey = md5($order['order_id']);
        switch ($payType) {
            case PayServices::ALIAPY_PAY:
                if (request()->isPc()) $jsConfig->invalid = time() + 60;
                CacheService::set($payKey, ['order_id' => $order['order_id'], 'other_pay_type' => false], 300);
                break;
            case PayServices::ALLIN_PAY:
                if (request()->isWechat()) {
                    $payUrl = AllinPay::UNITODER_H5UNIONPAY;
                }
                break;
            case PayServices::WEIXIN_PAY:
                if (isset($jsConfig['mweb_url'])) {
                    $jsConfig['h5_url'] = $jsConfig['mweb_url'];
                }
        }

        return ['jsConfig' => $jsConfig, 'order_id' => $order['order_id'], 'pay_key' => $payKey, 'pay_url' => $payUrl ?? ''];
    }
}
