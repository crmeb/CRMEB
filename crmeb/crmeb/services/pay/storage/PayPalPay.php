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

use app\services\pay\PayServices;
use crmeb\exceptions\PayException;
use crmeb\services\pay\BasePay;
use crmeb\services\pay\PayInterface;
use think\facade\Event;
use think\facade\Log;

/**
 * PayPal支付
 * Class PayPalPay
 * @package crmeb\services\pay\storage
 */
class PayPalPay extends BasePay implements PayInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var string
     */
    protected $accessToken = '';

    /**
     * @var int
     */
    protected $accessTokenExpiresAt = 0;

    /**
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        $this->config = [
            'client_id' => sys_config('paypal_client_id'),
            'client_secret' => sys_config('paypal_client_secret'),
            'mode' => sys_config('paypal_mode') ?: 'sandbox',
            'webhook_id' => sys_config('paypal_webhook_id'),
        ];

        $this->baseUrl = $this->config['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * 创建订单
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
        $payload = [
            'intent' => $options['intent'] ?? 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $orderId,
                    'invoice_id' => $orderId,
                    'custom_id' => $attach,
                    'description' => $body,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $totalFee,
                    ],
                ],
            ],
        ];

        $returnUrl = $options['return_url'] ?? (sys_config('site_url') . '/pages/index/index');
        $cancelUrl = $options['cancel_url'] ?? $returnUrl;
        $payload['application_context'] = [
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
        ];

        return $this->request('POST', '/v2/checkout/orders', $payload);
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
     * @param array $options
     * @return array|mixed
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        $captureId = $options['capture_id'] ?? $outTradeNo;
        $payload = [];
        $amount = $options['refund_amount'] ?? ($options['refund_price'] ?? null);
        if ($amount !== null) {
            $payload['amount'] = [
                'currency_code' => $options['currency'] ?? 'USD',
                'value' => $amount,
            ];
        }

        if (!empty($options['invoice_id'])) {
            $payload['invoice_id'] = $options['invoice_id'];
        }

        return $this->request('POST', '/v2/payments/captures/' . $captureId . '/refund', $payload);
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
        return $this->request('GET', '/v2/payments/refunds/' . $outRequestNo);
    }

    /**
     * 支付异步回调
     * @return string
     */
    public function handleNotify()
    {
        $payload = request()->getContent();
        $event = json_decode($payload, true);
        if (!$event) return 'fail';

        $headers = request()->header();
        if (!$this->verifyWebhookSignature($headers, $event)) return 'fail';

        $resource = $event['resource'] ?? [];
        $outTradeNo = $resource['invoice_id']
            ?? ($resource['supplementary_data']['related_ids']['order_id'] ?? '');
        $transactionId = $resource['id'] ?? '';
        $attach = $resource['custom_id'] ?? '';

        if ($outTradeNo && $transactionId) {
            $data = [
                'attach' => $attach,
                'out_trade_no' => $outTradeNo,
                'transaction_id' => $transactionId,
            ];

            if (Event::until('NotifyListener', [$data, 'paypal'])) {
                return 'success';
            }
            return 'fail';
        }

        return 'success';
    }

    /**
     * 验证 webhook 签名
     * @param array $headers
     * @param array $event
     * @return bool
     */
    protected function verifyWebhookSignature(array $headers, array $event): bool
    {
        try {
            $payload = [
                'auth_algo' => $this->getHeaderValue($headers, 'PAYPAL-AUTH-ALGO'),
                'cert_url' => $this->getHeaderValue($headers, 'PAYPAL-CERT-URL'),
                'transmission_id' => $this->getHeaderValue($headers, 'PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $this->getHeaderValue($headers, 'PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $this->getHeaderValue($headers, 'PAYPAL-TRANSMISSION-TIME'),
                'webhook_id' => $this->config['webhook_id'],
                'webhook_event' => $event,
            ];

            $result = $this->request('POST', '/v1/notifications/verify-webhook-signature', $payload);
            return isset($result['verification_status']) && $result['verification_status'] === 'SUCCESS';
        } catch (\Exception $e) {
            Log::error('PayPal webhook verify error:' . $e->getMessage());
        }

        return false;
    }

    /**
     * 获取访问令牌
     * @return string
     */
    protected function getAccessToken(): string
    {
        if ($this->accessToken && $this->accessTokenExpiresAt > time()) {
            return $this->accessToken;
        }

        $result = $this->request('POST', '/v1/oauth2/token', 'grant_type=client_credentials', [], 'basic');
        if (empty($result['access_token'])) {
            throw new PayException('PayPal token error');
        }

        $this->accessToken = $result['access_token'];
        $expiresIn = intval($result['expires_in'] ?? 0);
        $this->accessTokenExpiresAt = time() + max($expiresIn - 60, 0);

        return $this->accessToken;
    }

    /**
     * 请求 PayPal REST API
     * @param string $method
     * @param string $path
     * @param array|string|null $payload
     * @param array $headers
     * @param string $authType
     * @return array|mixed
     */
    protected function request(string $method, string $path, $payload = null, array $headers = [], string $authType = 'bearer')
    {
        $url = $this->baseUrl . $path;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $requestHeaders = $headers;

        if ($authType === 'basic') {
            curl_setopt($curl, CURLOPT_USERPWD, $this->config['client_id'] . ':' . $this->config['client_secret']);
        } else {
            $token = $this->getAccessToken();
            $requestHeaders[] = 'Authorization: Bearer ' . $token;
        }

        if ($payload !== null) {
            if (is_array($payload)) {
                $payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
                $requestHeaders[] = 'Content-Type: application/json';
            } else {
                $requestHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        if (!empty($requestHeaders)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);
        }

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new PayException('PayPal request error:' . $error);
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($response, true);
        if ($result === null) {
            $result = $response;
        }

        if ($status >= 400) {
            $message = is_array($result) ? ($result['message'] ?? $result['error'] ?? 'PayPal error') : 'PayPal error';
            throw new PayException($message);
        }

        return $result;
    }

    /**
     * @param array $headers
     * @param string $name
     * @return string
     */
    protected function getHeaderValue(array $headers, string $name): string
    {
        $target = strtolower($name);
        foreach ($headers as $key => $value) {
            if (strtolower($key) === $target) {
                if (is_array($value)) {
                    return (string)($value[0] ?? '');
                }
                return (string)$value;
            }
        }
        return '';
    }
}
