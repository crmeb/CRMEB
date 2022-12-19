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

namespace crmeb\services\pay\storage;


use crmeb\exceptions\AdminException;
use crmeb\services\pay\BasePay;
use crmeb\exceptions\PayException;
use crmeb\services\pay\PayInterface;
use crmeb\services\app\MiniProgramService;
use crmeb\services\app\WechatService;
use crmeb\services\SystemConfigService;
use EasyWeChat\Payment\API;
use EasyWeChat\Payment\Order;
use EasyWeChat\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * 微信支付
 * Class WechatPay
 * @package crmeb\services\pay\storage
 */
class WechatPay extends BasePay implements PayInterface
{

    protected function initialize(array $config)
    {
        // TODO: Implement initialize() method.
    }

    /**
     * 创建订单进行支付
     * @param string $orderId
     * @param string $totalFee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array|mixed|string
     */
    public function create(string $orderId, string $totalFee, string $attach, string $body, string $detail, array $options = [])
    {
        $this->authSetPayType();

        switch ($this->payType) {
            case Order::NATIVE:
                return WechatService::nativePay(null, $orderId, $totalFee, $attach, $body, $detail);
            case Order::APP:
                return WechatService::appPay($options['openid'], $orderId, $totalFee, $attach, $body, $detail);
            case Order::JSAPI:
                if (empty($options['openid'])) {
                    throw new PayException('缺少openid');
                }
                if (request()->isRoutine()) {
                    // 获取配置  判断是否为新支付
                    if ($options['pay_new_weixin_open']) {
                        return MiniProgramService::newJsPay($options['openid'], $orderId, $totalFee, $attach, $body, $detail, $options);
                    }
                    return MiniProgramService::jsPay($options['openid'], $orderId, $totalFee, $attach, $body, $detail);
                }
                return WechatService::jsPay($options['openid'], $orderId, $totalFee, $attach, $body, $detail);
            case 'h5':
                return WechatService::paymentPrepare(null, $orderId, $totalFee, $attach, $body, $detail, 'MWEB');
            default:
                throw new PayException('微信支付:支付类型错误');
        }
    }

    /**
     * 支付到零钱
     * @param string $openid
     * @param string $orderId
     * @param string $amount
     * @param array $options
     * @return bool|mixed
     */
    public function merchantPay(string $openid, string $orderId, string $amount, array $options = [])
    {
        return WechatService::merchantPay($openid, $orderId, $amount, $options['desc'] ?? '');
    }

    /**
     * 退款
     * @param string $outTradeNo
     * @param array $opt
     * @return Collection|mixed|ResponseInterface
     */
    public function refund(string $outTradeNo, array $opt = [])
    {
        if (!isset($opt['pay_price'])) throw new PayException(400730);
        $totalFee = floatval(bcmul($opt['pay_price'], 100, 0));
        $refundFee = isset($opt['refund_price']) ? floatval(bcmul($opt['refund_price'], 100, 0)) : null;
        $refundReason = $opt['desc'] ?? '';
        $refundNo = $opt['refund_id'] ?? $outTradeNo;
        $opUserId = $opt['op_user_id'] ?? null;
        $type = $opt['type'] ?? 'out_trade_no';
        /*仅针对老资金流商户使用
        REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
        REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款
        */
        $refundAccount = $opt['refund_account'] ?? 'REFUND_SOURCE_UNSETTLED_FUNDS';
        if (isset($opt['wechat'])) {
            return WechatService::refund($outTradeNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundReason, $type, $refundAccount);
        } else {
            if ($opt['pay_new_weixin_open']) {
                return MiniProgramService::miniRefund($outTradeNo, $totalFee, $refundFee, $opt);
            } else {
                return MiniProgramService::refund($outTradeNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundReason, $type, $refundAccount);
            }
        }
    }

    /**
     * 查询退款订单
     * @param string $outTradeNo
     * @param string $outRequestNo
     * @param array $other
     * @return Collection|mixed|ResponseInterface
     */
    public function queryRefund(string $outTradeNo, string $outRequestNo, array $other = [])
    {
        return WechatService::queryRefund($outTradeNo, $other['type'] ?? API::OUT_TRADE_NO);
    }

    /**
     * 异步回调
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function handleNotify()
    {
        return WechatService::handleNotify();
    }
}
