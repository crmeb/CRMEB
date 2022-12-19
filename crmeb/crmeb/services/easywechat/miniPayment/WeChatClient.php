<?php

namespace crmeb\services\easywechat\miniPayment;

use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Core\AccessToken;
use EasyWeChat\Kernel\Support;
use EasyWeChat\Kernel\Support\Collection;
use EasyWeChat\Kernel\Traits\HasHttpRequests;
use EasyWeChat\Payment\Application;
use EasyWeChat\Payment\Kernel\BaseClient;
use EasyWeChat\Payment\Merchant;
use GuzzleHttp\Client;
use think\Exception;
use think\Facade\Cache;
use think\facade\Log;

class WeChatClient extends AbstractAPI
{
    private $expire_time = 7000;


    /**
     * 创建订单 支付
     */
    const API_SET_CREATE_ORDER = 'https://api.weixin.qq.com/shop/pay/createorder';
    /**
     * 退款
     */
    const API_SET_REFUND_ORDER = 'https://api.weixin.qq.com/shop/pay/refundorder';


    /**
     * Merchant instance.
     *
     * @var \EasyWeChat\Payment\Merchant
     */
    protected $merchant;

    /**
     * ProgramSubscribeService constructor.
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken, Merchant $merchant)
    {
        parent::__construct($accessToken);
        $this->merchant = $merchant;
    }

    /**
     * 支付
     * @param array $params [
     *                      'openid'=>'支付者的openid',
     *                      'out_trade_no'=>'商家合单支付总交易单号',
     *                      'total_fee'=>'支付金额',
     *                      'wx_out_trade_no'=>'商家交易单号',
     *                      'body'=>'商品描述',
     *                      'attach'=>'支付类型',  //product 产品  member 会员
     *                      ]
     * @param $isContract
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createorder($order)
    {
        $params = [
            'openid' => $order['openid'],    // 支付者的openid
            'combine_trade_no' => $order['out_trade_no'],  // 商家合单支付总交易单号
            'expire_time' => time() + $this->expire_time,
            'sub_orders' => [
                [
                    'mchid' => $this->merchant->merchant_id,
                    'amount' => (int)$order['total_fee'],
                    'trade_no' => $order['out_trade_no'],
                    'description' => $order['body']
                ]
            ]
        ];
        return $this->parseJSON('post', [self::API_SET_CREATE_ORDER, json_encode($params)]);
    }

    /**
     * 退款
     * @param array $params [
     *                      'openid'=>'退款者的openid',
     *                      'trade_no'=>'商家交易单号',
     *                      'transaction_id'=>'支付单号',
     *                      'refund_no'=>'商家退款单号',
     *                      'total_amount'=>'订单总金额',
     *                      'refund_amount'=>'退款金额',  //product 产品  member 会员
     *                      ]
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refundorder(array $order)
    {
        $params = [
            'openid' => $order['openid'],
            'mchid' => $this->merchant->merchant_id,
            'trade_no' => $order['trade_no'],
            'transaction_id' => $order['transaction_id'],
            'refund_no' => $order['refund_no'],
            'total_amount' => $order['total_amount'],
            'refund_amount' => $order['refund_amount'],
        ];
        return $this->parseJSON('post', [self::API_SET_REFUND_ORDER, json_encode($params)]);
    }


}