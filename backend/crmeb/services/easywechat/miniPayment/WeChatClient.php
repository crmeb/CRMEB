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
declare(strict_types=1);

namespace crmeb\services\easywechat\miniPayment;

use crmeb\services\easywechat\AbstractApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 小程序合单支付(shop/pay,新版小程序支付通道)
 * Class WeChatClient
 * @package crmeb\services\easywechat\miniPayment
 */
class WeChatClient extends AbstractApiClient
{
    /**
     * 创建订单 支付
     */
    const API_SET_CREATE_ORDER = 'shop/pay/createorder';
    /**
     * 退款
     */
    const API_SET_REFUND_ORDER = 'shop/pay/refundorder';

    private $expire_time = 7000;

    /**
     * 商户号
     * @var string
     */
    protected string $merchantId;

    public function __construct(HttpClientInterface $api, string $merchantId = '')
    {
        parent::__construct($api);
        $this->merchantId = $merchantId;
    }

    /**
     * 支付
     * @param array $order [
     *                     'openid'=>'支付者的openid',
     *                     'out_trade_no'=>'商家合单支付总交易单号',
     *                     'total_fee'=>'支付金额(分)',
     *                     'body'=>'商品描述',
     *                     ]
     * @return array
     */
    public function createorder($order)
    {
        $params = [
            'openid' => $order['openid'],    // 支付者的openid
            'combine_trade_no' => $order['out_trade_no'],  // 商家合单支付总交易单号
            'expire_time' => (int)($order['expire_time'] ?? (time() + $this->expire_time)),
            'sub_orders' => [
                [
                    'mchid' => $this->merchantId,
                    'amount' => (int)$order['total_fee'],
                    'trade_no' => $order['out_trade_no'],
                    'description' => $order['body'],
                ],
            ],
        ];
        return $this->postJson(self::API_SET_CREATE_ORDER, $params);
    }

    /**
     * 退款
     * @param array $order [
     *                     'openid'=>'退款者的openid',
     *                     'trade_no'=>'商家交易单号',
     *                     'transaction_id'=>'支付单号',
     *                     'refund_no'=>'商家退款单号',
     *                     'total_amount'=>'订单总金额',
     *                     'refund_amount'=>'退款金额',
     *                     ]
     * @return array
     */
    public function refundorder(array $order)
    {
        $params = [
            'openid' => $order['openid'],
            'mchid' => $this->merchantId,
            'trade_no' => $order['trade_no'],
            'transaction_id' => $order['transaction_id'],
            'refund_no' => $order['refund_no'],
            'total_amount' => $order['total_amount'],
            'refund_amount' => $order['refund_amount'],
        ];
        return $this->postJson(self::API_SET_REFUND_ORDER, $params);
    }

    /**
     * 查询支付订单及退款记录。
     * @param string $tradeNo 原商家交易单号
     * @return array
     */
    public function getorder(string $tradeNo): array
    {
        return $this->postJson('shop/pay/getorder', ['trade_no' => $tradeNo]);
    }
}
