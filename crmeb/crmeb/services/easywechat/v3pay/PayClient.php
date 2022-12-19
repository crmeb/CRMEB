<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\easywechat\v3pay;


use crmeb\exceptions\PayException;

/**
 * v3支付
 * Class PayClient
 * @package crmeb\services\easywechat\v3pay
 */
class PayClient extends BaseClient
{
    //app支付
    const API_APP_APY_URL = 'v3/pay/transactions/app';
    //二维码支付截图
    const API_NATIVE_URL = 'v3/pay/transactions/native';
    //h5支付接口
    const API_H5_URL = 'v3/pay/transactions/h5';
    //jsapi支付接口
    const API_JSAPI_URL = 'v3/pay/transactions/jsapi';
    //发起商家转账API
    const API_BATCHES_URL = 'v3/transfer/batches';
    //退款
    const API_REFUND_URL = 'v3/refund/domestic/refunds';
    //退款查询接口
    const API_REFUND_QUERY_URL = 'v3/refund/domestic/refunds/{out_refund_no}';

    /**
     * 公众号jsapi支付下单
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return mixed
     */
    public function jsapiPay(string $openid, string $outTradeNo, string $total, string $description, string $attach)
    {
        $appId = $this->app['config']['wechat']['appid'];
        $res = $this->pay('jsapi', $appId, $outTradeNo, $total, $description, $attach, ['openid' => $openid]);
        return $this->configForJSSDKPayment($appId, $res['prepay_id']);
    }

    /**
     * 小程序支付
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return array|false|string
     */
    public function miniprogPay(string $openid, string $outTradeNo, string $total, string $description, string $attach)
    {
        $appId = $this->app['config']['miniprog']['appid'];
        $res = $this->pay('jsapi', $appId, $outTradeNo, $total, $description, $attach, ['openid' => $openid]);
        return $this->configForJSSDKPayment($appId, $res['prepay_id']);
    }

    /**
     * APP支付下单
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return mixed
     */
    public function appPay(string $outTradeNo, string $total, string $description, string $attach)
    {
        $res = $this->pay('app', $this->app['config']['app']['appid'], $outTradeNo, $total, $description, $attach);
        return $this->configForAppPayment($res['prepay_id']);
    }

    /**
     * native支付下单
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return mixed
     */
    public function nativePay(string $outTradeNo, string $total, string $description, string $attach)
    {
        return $this->pay('native', $this->app['config']['web']['appid'], $outTradeNo, $total, $description, $attach);
    }

    /**
     * h5支付下单
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return mixed
     */
    public function h5Pay(string $outTradeNo, string $total, string $description, string $attach)
    {
        return $this->pay('h5', $this->app['config']['wechat']['appid'], $outTradeNo, $total, $description, $attach);
    }

    /**
     * 下单
     * @param string $type
     * @param string $appid
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @param array $payer
     * @return mixed
     */
    public function pay(string $type, string $appid, string $outTradeNo, string $total, string $description, string $attach, array $payer = [])
    {
        $totalFee = (int)bcmul($total, '100');

        $data = [
            'appid' => $appid,
            'mchid' => $this->app['config']['v3_payment']['mchid'],
            'out_trade_no' => $outTradeNo,
            'attach' => $attach,
            'description' => $description,
            'notify_url' => $this->app['config']['v3_payment']['notify_url'],
            'amount' => [
                'total' => $totalFee,
                'currency' => 'CNY'
            ],
        ];

        if ($payer) {
            $data['payer'] = $payer;
        }

        $url = '';
        switch ($type) {
            case 'h5':
                $url = self::API_H5_URL;
                $data['scene_info'] = [
                    'payer_client_ip' => request()->ip(),
                    'h5_info' => [
                        'type' => 'Wap'
                    ]
                ];
                break;
            case 'native':
                $url = self::API_NATIVE_URL;
                break;
            case 'app':
                $url = self::API_APP_APY_URL;
                break;
            case 'jsapi':
                $url = self::API_JSAPI_URL;
                break;
        }

        if (!$url) {
            throw new PayException('缺少请求地址');
        }

        $res = $this->request($url, 'POST', ['json' => $data]);

        if (!$res) {
            throw new PayException('微信支付:下单失败');
        }
        if (isset($res['code']) && isset($res['message'])) {
            throw new PayException($res['message']);
        }

        return $res;
    }

    /**
     * 发起商家转账API
     * @param string $outBatchNo
     * @param string $amount
     * @param string $batchName
     * @param string $remark
     * @param array $transferDetailList
     * @return mixed
     */
    public function batches(string $outBatchNo, string $amount, string $batchName, string $remark, array $transferDetailList)
    {
        $totalFee = '0';
        $amount = bcadd($amount, '0', 2);
        foreach ($transferDetailList as &$item) {
            if ($item['transfer_amount'] >= 2000 && !empty($item['user_name'])) {
                throw new PayException('明细金额大于等于2000时,收款人姓名必须填写');
            }
            $totalFee = bcadd($totalFee, $item['transfer_amount'], 2);
            $item['transfer_amount'] = (int)bcmul($item['transfer_amount'], 100, 0);
            if (isset($item['user_name'])) {
                $item['user_name'] = $this->encryptor($item['user_name']);
            }
        }

        if ($totalFee !== $amount) {
            throw new PayException('转账明细金额总和和转账总金额不一致');
        }

        $amount = (int)bcmul($amount, 100, 0);

        $data = [
            'appid' => $this->app['config']['wechat']['appid'],
            'out_batch_no' => $outBatchNo,
            'batch_name' => $batchName,
            'batch_remark' => $remark,
            'total_amount' => $amount,
            'total_num' => count($transferDetailList),
            'transfer_detail_list' => $transferDetailList
        ];

        $res = $this->request(self::API_BATCHES_URL, 'POST', ['json' => $data]);

        if (!$res) {
            throw new PayException('微信支付:发起商家转账失败');
        }

        if (isset($res['code']) && isset($res['message'])) {
            throw new PayException($res['message']);
        }

        return $res;

    }

    /**
     * 退款
     * @param string $outTradeNo
     * @param array $options
     * @return mixed
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        if (!isset($options['pay_price'])) {
            throw new PayException(400730);
        }
        $totalFee = floatval(bcmul($options['pay_price'], 100, 0));
        $refundFee = isset($options['refund_price']) ? floatval(bcmul($options['refund_price'], 100, 0)) : null;
        $refundReason = $options['desc'] ?? '';
        $refundNo = $options['refund_id'] ?? $outTradeNo;
        /*仅针对老资金流商户使用
        REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
        REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款
        */
        $refundAccount = $opt['refund_account'] ?? 'AVAILABLE';

        $data = [
            'transaction_id' => $outTradeNo,
            'out_refund_no' => $refundNo,
            'amount' => [
                'refund' => (int)$refundFee,
                'currency' => 'CNY',
                'total' => (int)$totalFee
            ],
            'funds_account' => $refundAccount
        ];

        if ($refundReason) {
            $data['reason'] = $refundReason;
        }

        $res = $this->request(self::API_REFUND_URL, 'POST', ['json' => $data]);

        if (!$res) {
            throw new PayException('微信支付:发起退款失败');
        }

        if (isset($res['code']) && isset($res['message'])) {
            throw new PayException($res['message']);
        }

        return $res;
    }

    /**
     * 查询退款
     * @param string $outRefundNo
     * @return mixed
     */
    public function queryRefund(string $outRefundNo)
    {
        $res = $this->request($this->getApiUrl(self::API_REFUND_QUERY_URL, ['out_refund_no'], [$outRefundNo]), 'GET');

        if (!$res) {
            throw new PayException(500000);
        }

        if (isset($res['code']) && isset($res['message'])) {
            throw new PayException($res['message']);
        }

        return $res;
    }

    /**
     * jsapi支付
     * @param string $appid
     * @param string $prepayId
     * @param bool $json
     * @return array|false|string
     */
    public function configForPayment(string $appid, string $prepayId, bool $json = true)
    {
        $params = [
            'appId' => $appid,
            'timeStamp' => strval(time()),
            'nonceStr' => uniqid(),
            'package' => "prepay_id=$prepayId",
            'signType' => 'RSA',
        ];
        $message = $params['appId'] . "\n" .
            $params['timeStamp'] . "\n" .
            $params['nonceStr'] . "\n" .
            $params['package'] . "\n";
        openssl_sign($message, $raw_sign, $this->getPrivateKey(), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);

        $params['paySign'] = $sign;

        return $json ? json_encode($params) : $params;
    }

    /**
     * Generate app payment parameters.
     * @param string $prepayId
     * @return array
     */
    public function configForAppPayment(string $prepayId): array
    {
        $params = [
            'appid' => $this->app['config']['app']['appid'],
            'partnerid' => $this->app['config']['v3_payment']['mchid'],
            'prepayid' => $prepayId,
            'noncestr' => uniqid(),
            'timestamp' => time(),
            'package' => 'Sign=WXPay',
        ];
        $message = $params['appid'] . "\n" .
            $params['timestamp'] . "\n" .
            $params['noncestr'] . "\n" .
            $params['prepayid'] . "\n";
        openssl_sign($message, $raw_sign, $this->getPrivateKey(), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);

        $params['sign'] = $sign;

        return $params;
    }

    /**
     * 小程序支付
     * @param string $appid
     * @param string $prepayId
     * @return array|false|string
     */
    public function configForJSSDKPayment(string $appid, string $prepayId)
    {
        $config = $this->configForPayment($appid, $prepayId, false);

        $config['timestamp'] = $config['timeStamp'];
        unset($config['timeStamp']);

        return $config;
    }

    /**
     * @param $callback
     * @return \think\Response
     */
    public function handleNotify($callback)
    {
        $request = request();
        $success = $request->post('event_type') === 'TRANSACTION.SUCCESS';
        $data = $this->decrypt($request->post('resource', []));

        $handleResult = call_user_func_array($callback, [json_decode($data), $success]);
        if (is_bool($handleResult) && $handleResult) {
            $response = [
                'code' => 'SUCCESS',
                'message' => 'OK',
            ];
        } else {
            $response = [
                'code' => 'FAIL',
                'message' => $handleResult,
            ];
        }

        return response($response, 200, [], 'json');
    }
}
