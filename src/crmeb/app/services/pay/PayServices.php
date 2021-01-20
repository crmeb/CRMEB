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
declare (strict_types=1);

namespace app\services\pay;

use crmeb\services\AliPayService;
use crmeb\services\MiniProgramService;
use crmeb\services\WechatService;
use think\exception\ValidateException;

/**
 * 支付统一入口
 * Class PayServices
 * @package app\services\pay
 */
class PayServices
{
    /**
     * 微信支付类型
     */
    const WEIXIN_PAY = 'weixin';

    /**
     * 余额支付
     */
    const YUE_PAY = 'yue';

    /**
     * 线下支付
     */
    const OFFLINE_PAY = 'offline';

    /**
     * 支付方式
     * @var string[]
     */
    const PAY_TYPE = [
        PayServices::WEIXIN_PAY => '微信支付',
        PayServices::YUE_PAY => '余额支付',
        PayServices::OFFLINE_PAY => '线下支付',
    ];

    /**
     * 发起支付
     * @param string $payType
     * @param string $openid
     * @param string $orderId
     * @param string $price
     * @param string $successAction
     * @param string $body
     * @return array|string
     */
    public function pay(string $payType, string $openid, string $orderId, string $price, string $successAction, string $body, bool $isCode = false)
    {
        try {
            switch ($payType) {
                case 'routine':
                    return MiniProgramService::jsPay($openid, $orderId, $price, $successAction, $body);
                case 'weixinh5':
                    return WechatService::paymentPrepare(null, $orderId, $price, $successAction, $body, '', 'MWEB');
                case 'weixin':
                    return WechatService::jsPay($openid, $orderId, $price, $successAction, $body);
                default:
                    throw new ValidateException('支付方式不存在');
            }
        } catch (\Exception $e) {
            throw new ValidateException($e->getMessage());
        }
    }
}
