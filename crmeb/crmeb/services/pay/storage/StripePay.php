<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\pay\storage;

use crmeb\exceptions\PayException;
use crmeb\services\pay\BasePay;
use crmeb\services\pay\PayInterface;
use think\facade\Event;
use think\facade\Log;

/**
 * Stripe 支付
 * Class StripePay
 * @package crmeb\services\pay\storage
 */
class StripePay extends BasePay implements PayInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $baseUrl = 'https://api.stripe.com';

    /**
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        $this->config = [
            'secret_key' => sys_config('stripe_secret_key'),
            'publishable_key' => sys_config('stripe_publishable_key'),
            'webhook_secret' => sys_config('stripe_webhook_secret'),
        ];
    }

    /**
     * 创建订单（Checkout Session）
     * @param string $orderId
     * @param string $totalFee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array|mixed
     */
    public function create(string $orderId, string $totalFee, string $attach, string $body, string $detail, array $options = [])
    {
        $this->authSetPayType();

        $currency = $options['currency'] ?? 'USD';
        $amount = (int)bcmul($totalFee, 100, 0);
        if ($amount <= 0) {
            throw new PayException('支付金额错误');
        }

        $successUrl = $options['success_url'] ?? (sys_config('site_url') . '/pages/index/index');
        $cancelUrl = $options['cancel_url'] ?? $successUrl;

        $payload = [
            'mode' => 'payment',
            'client_reference_id' => $orderId,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $body ?: ($detail ?: 'Order'),
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => $options['quantity'] ?? 1,
                ],
            ],
            'metadata' => [
                'attach' => $attach,
                'out_trade_no' => $orderId,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'attach' => $attach,
                    'out_trade_no' => $orderId,
                ],
            ],
        ];

        if (!empty($options['customer_email'])) {
            $payload['customer_email'] = $options['customer_email'];
        }
        if (!empty($options['locale'])) {
            $payload['locale'] = $options['locale'];
        }

        return $this->request('POST', '/v1/checkout/sessions', $payload);
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
     * 退款（Refunds API）
     * @param string $outTradeNo
     * @param array $options
     * @return array|mixed
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        $payload = [];

        if (!empty($options['charge'])) {
            $payload['charge'] = $options['charge'];
        } else {
            $payload['payment_intent'] = $options['payment_intent'] ?? $outTradeNo;
        }

        $refundAmount = $options['refund_amount'] ?? ($options['refund_price'] ?? null);
        if ($refundAmount !== null) {
            $payload['amount'] = (int)bcmul((string)$refundAmount, 100, 0);
        }

        if (!empty($options['reason'])) {
            $payload['reason'] = $options['reason'];
        }

        return $this->request('POST', '/v1/refunds', $payload);
    }

    /**
     * 查询退款
     * @param string $outTradeNo
     * @param string $outRequestNo
     * @param array $other
     * @return mixed
     */
    public function queryRefund(string $outTradeNo, string $outRequestNo, array $other = [])
    {
        if (!empty($outRequestNo)) {
            return $this->request('GET', '/v1/refunds/' . $outRequestNo);
        }

        $payload = [];
        if (!empty($other['payment_intent'])) {
            $payload['payment_intent'] = $other['payment_intent'];
        } elseif (!empty($outTradeNo)) {
            $payload['payment_intent'] = $outTradeNo;
        }

        return $this->request('GET', '/v1/refunds', $payload);
    }

    /**
     * 支付异步回调
     * @return mixed|string
     */
    public function handleNotify()
    {
        $payload = request()->getContent();
        $event = json_decode($payload, true);
        if (!$event) return 'fail';

        $sigHeader = (string)request()->header('Stripe-Signature');
        if (!$this->verifyWebhookSignature($payload, $sigHeader)) return 'fail';

        $type = $event['type'] ?? '';
        $object = $event['data']['object'] ?? [];

        if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'])) {
            $outTradeNo = $object['client_reference_id'] ?? ($object['metadata']['out_trade_no'] ?? '');
            $transactionId = $object['payment_intent'] ?? ($object['id'] ?? '');
            $attach = $object['metadata']['attach'] ?? '';
            $paymentStatus = $object['payment_status'] ?? '';

            if ($outTradeNo && $transactionId && ($paymentStatus === '' || $paymentStatus === 'paid')) {
                $data = [
                    'attach' => $attach,
                    'out_trade_no' => $outTradeNo,
                    'transaction_id' => $transactionId,
                ];

                if (Event::until('NotifyListener', [$data, 'stripe'])) {
                    return 'success';
                }

                return 'fail';
            }
        }

        return 'success';
    }

    /**
     * 验证 webhook 签名
     * @param string $payload
     * @param string $sigHeader
     * @return bool
     */
    protected function verifyWebhookSignature(string $payload, string $sigHeader): bool
    {
        try {
            $secret = $this->config['webhook_secret'] ?? '';
            if ($secret === '' || $sigHeader === '') return false;

            $timestamp = 0;
            $signatures = [];
            $parts = explode(',', $sigHeader);
            foreach ($parts as $part) {
                $item = explode('=', trim($part), 2);
                if (count($item) !== 2) continue;
                if ($item[0] === 't') {
                    $timestamp = (int)$item[1];
                } elseif ($item[0] === 'v1') {
                    $signatures[] = $item[1];
                }
            }

            if ($timestamp <= 0 || empty($signatures)) return false;
            if (abs(time() - $timestamp) > 300) return false;

            $signedPayload = $timestamp . '.' . $payload;
            $expected = hash_hmac('sha256', $signedPayload, $secret);
            foreach ($signatures as $signature) {
                if (hash_equals($expected, $signature)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook verify error:' . $e->getMessage());
        }

        return false;
    }

    /**
     * 请求 Stripe API
     * @param string $method
     * @param string $path
     * @param array|null $payload
     * @return array|mixed
     */
    protected function request(string $method, string $path, array $payload = null)
    {
        $secretKey = $this->config['secret_key'] ?? '';
        if ($secretKey === '') {
            throw new PayException('Stripe secret key missing');
        }

        $url = $this->baseUrl . $path;
        if ($method === 'GET' && !empty($payload)) {
            $url .= '?' . http_build_query($payload);
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $headers = [
            'Authorization: Bearer ' . $secretKey,
        ];

        if ($payload !== null && $method !== 'GET') {
            $body = http_build_query($payload);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new PayException('Stripe request error:' . $error);
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($response, true);
        if ($result === null) {
            $result = $response;
        }

        if ($status >= 400) {
            $message = is_array($result)
                ? ($result['error']['message'] ?? ($result['error'] ?? 'Stripe error'))
                : 'Stripe error';
            throw new PayException($message);
        }

        return $result;
    }
}
