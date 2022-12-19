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


use Alipay\EasySDK\Payment\Common\Models\AlipayTradeFastpayRefundQueryResponse;
use Alipay\EasySDK\Payment\Common\Models\AlipayTradeRefundResponse;
use Alipay\EasySDK\Payment\Wap\Models\AlipayTradeWapPayResponse;
use crmeb\services\pay\BasePay;
use crmeb\services\pay\PayInterface;
use crmeb\services\AliPayService;

/**
 * 支付宝支付
 * Class AliPay
 * @package crmeb\services\pay\storage
 */
class AliPay extends BasePay implements PayInterface
{

    protected function initialize(array $config)
    {
        // TODO: Implement initialize() method.
    }

    /**
     * 创建订单发起支付
     * @param string $orderId
     * @param string $totalFee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param string|null $tradeType
     * @param array $options
     * @return AlipayTradeWapPayResponse|mixed
     */
    public function create(string $orderId, string $totalFee, string $attach, string $body, string $detail, array $options = [])
    {
        $code = false;
        if (request()->isPC() || request()->isRoutine() || !empty($options['isCode'])) {
            $code = true;
        }

        return AliPayService::instance()->create($body, $orderId, $totalFee, $attach, $options['uitUrl'] ?? '', $options['siteUrl'] ?? '', $code);
    }

    /**
     * 企业支付到零钱
     * @param string $openid
     * @param string $orderId
     * @param string $amount
     * @param array $options
     * @return bool|mixed
     */
    public function merchantPay(string $openid, string $orderId, string $amount, array $options = [])
    {
        return false;
    }

    /**
     * 退款
     * @param string $outTradeNo
     * @param string $totalAmount
     * @param string $refund_id
     * @param array $options
     * @return AlipayTradeRefundResponse|mixed
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        return AliPayService::instance()->refund($outTradeNo, $options['totalAmount'], $options['refund_id']);
    }

    /**
     * 查询退款
     * @param string $outTradeNo
     * @param string $outRequestNo
     * @param array $other
     * @return AlipayTradeFastpayRefundQueryResponse|mixed
     */
    public function queryRefund(string $outTradeNo, string $outRequestNo, array $other = [])
    {
        return AliPayService::instance()->queryRefund($outTradeNo, $outRequestNo);
    }

    /**
     * 支付异步回调
     * @return mixed|string
     */
    public function handleNotify()
    {
        return AliPayService::handleNotify();
    }
}
