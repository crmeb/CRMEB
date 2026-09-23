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

namespace driver\pay\V3WechatPay;

use crmeb\exceptions\PayException;
use crmeb\services\CacheService;
use crmeb\services\wechat\Support;
use EasyWeChat\Kernel\Support\AesGcm;
use EasyWeChat\Pay\Application;
use EasyWeChat\Pay\Message;
use Nyholm\Psr7\Response as Psr7Response;
use think\Response as ThinkResponse;

/**
 * 微信支付 V3 客户端(EasyWeChat 6.x)
 *
 * 替代原 crmeb/services/easywechat/v3pay 手写签名/加解密栈:
 * - 请求签名(/v3/ 自动 RSA 签权)与回调验签解密由 SDK 完成,
 *   handleNotify 现在会执行 Wechatpay-Signature 验签(旧实现缺失);
 * - 微信客户端调起参数生成走 getUtils();
 * - 证书模式短期缓存已验证的平台证书，未知序列号触发刷新。
 *
 * 方法签名与旧 v3pay/PayClient 保持一致,Driver 调用方式不变。
 *
 * Class PayClient
 * @package driver\pay\V3WechatPay
 */
class PayClient
{
    //app支付
    const API_APP_APY_URL = 'v3/pay/transactions/app';
    //app支付-服务商模式
    const API_APP_APY_PARTNER_URL = 'v3/pay/partner/transactions/app';
    //Native下单API
    const API_NATIVE_URL = 'v3/pay/transactions/native';
    //Native下单API-服务商模式
    const API_NATIVE_PARTNER_URL = 'v3/pay/partner/transactions/native';
    //h5支付接口
    const API_H5_URL = 'v3/pay/transactions/h5';
    //h5支付接口-服务商模式
    const API_H5_PARTNER_URL = 'v3/pay/partner/transactions/h5';
    //jsapi支付接口
    const API_JSAPI_URL = 'v3/pay/transactions/jsapi';
    //jsapi支付接口-服务商模式
    const API_JSAPI_PARTNER_URL = 'v3/pay/partner/transactions/jsapi';
    //发起商家转账API
    const API_BATCHES_URL = 'v3/transfer/batches';
    //退款
    const API_REFUND_URL = 'v3/refund/domestic/refunds';
    //退款查询接口
    const API_REFUND_QUERY_URL = 'v3/refund/domestic/refunds/%s';
    //发起转账
    const API_TRANSFER_BILLS_URL = 'v3/fund-app/mch-transfer/transfer-bills';
    //查询转账
    const API_TRANSFER_QUERY_URL = 'v3/fund-app/mch-transfer/transfer-bills/out-bill-no/%s';

    /**
     * 各渠道 appid:['app','wechat','miniprog','web','h5']
     * @var array
     */
    protected array $appids;

    /**
     * 平台公钥模式:公钥 ID(空则证书模式)
     * @var string
     */
    protected string $publicKeyId;

    /**
     * @var string
     */
    protected string $type = 'JSAPI';

    /** @var int 支付截止时间 */
    protected int $expireTime = 0;

    /**
     * 设置业务订单的支付截止时间。
     * @param int $expires 时间戳
     * @return $this
     */
    public function setExpireTime(int $expires)
    {
        $this->expireTime = $expires;
        return $this;
    }

    public function __construct(protected Application $app, array $appids, string $publicKeyId = '')
    {
        $this->appids = $appids;
        $this->publicKeyId = $publicKeyId;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 公众号jsapi支付下单
     * @param string $openid
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return array
     */
    public function jsapiPay(string $openid, string $outTradeNo, string $total, string $description, string $attach)
    {
        $appId = trim((string)($this->appids['wechat'] ?? ''));
        if ($appId === '') {
            throw new PayException('微信内支付需要配置微信公众号AppID；普通浏览器请使用H5支付');
        }
        $res = $this->pay('jsapi', $appId, $outTradeNo, $total, $description, $attach, ['openid' => $openid]);
        return $this->configForJSSDKPayment($appId, $res['prepay_id']);
    }

    /**
     * 小程序支付
     * @param string $openid
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return array
     */
    public function miniprogPay(string $openid, string $outTradeNo, string $total, string $description, string $attach)
    {
        $appId = trim((string)($this->appids['miniprog'] ?? ''));
        if ($appId === '') {
            throw new PayException('微信支付V3小程序需要配置小程序AppID');
        }
        $res = $this->pay('jsapi', $appId, $outTradeNo, $total, $description, $attach, ['openid' => $openid]);
        return $this->configForJSSDKPayment($appId, $res['prepay_id']);
    }

    /**
     * APP支付下单
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return array
     */
    public function appPay(string $outTradeNo, string $total, string $description, string $attach)
    {
        $appId = trim((string)($this->appids['app'] ?? ''));
        if ($appId === '') {
            throw new PayException('微信支付V3 APP支付需要配置APP应用AppID');
        }
        $res = $this->pay('app', $appId, $outTradeNo, $total, $description, $attach);
        return $this->configForAppPayment($res['prepay_id']);
    }

    /**
     * native支付下单
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return array
     */
    public function nativePay(string $outTradeNo, string $total, string $description, string $attach)
    {
        $appId = trim((string)($this->appids['web'] ?? ''));
        if ($appId === '') {
            throw new PayException('微信支付V3 Native支付需要配置公众号AppID');
        }
        return $this->pay('native', $appId, $outTradeNo, $total, $description, $attach);
    }

    /**
     * h5支付下单
     * @param string $outTradeNo
     * @param string $total
     * @param string $description
     * @param string $attach
     * @return array
     */
    public function h5Pay(string $outTradeNo, string $total, string $description, string $attach)
    {
        // 兼容旧调用方未传入 h5 键时沿用公众号 AppID。
        $appId = trim((string)($this->appids['h5'] ?? $this->appids['wechat'] ?? ''));
        if ($appId === '') {
            throw new PayException('H5微信支付需要配置已绑定商户号的AppID（公众号、小程序或移动应用均可）');
        }
        return $this->pay('h5', $appId, $outTradeNo, $total, $description, $attach);
    }

    /**
     * 下单
     * @param string $type jsapi|native|app|h5
     * @param string $appid
     * @param string $outTradeNo
     * @param string $total 金额(元)
     * @param string $description
     * @param string $attach
     * @param array $payer
     * @return array
     */
    public function pay(string $type, string $appid, string $outTradeNo, string $total, string $description, string $attach, array $payer = [])
    {
        $totalFee = (int)bcmul($total, '100');

        $data = [
            'appid' => $appid,
            'mchid' => $this->app->getConfig()->get('mch_id'),
            'out_trade_no' => $outTradeNo,
            'attach' => $attach,
            'description' => $description,
            'notify_url' => $this->app->getConfig()->get('notify_url'),
            'amount' => [
                'total' => $totalFee,
                'currency' => 'CNY',
            ],
        ];

        if ($payer) {
            $data['payer'] = $payer;
        }
        if ($this->expireTime > 0) $data['time_expire'] = date(DATE_RFC3339, $this->expireTime);

        //服务商支付模式
        if ($this->app->getConfig()->get('sub_mch_id')) {
            $mchid = $data['mchid'];
            $subAppid = $data['appid'];
            unset($data['mchid'], $data['appid'], $data['payer']);
            $data['sp_appid'] = $this->app->getConfig()->get('sp_appid');
            $data['sp_mchid'] = $mchid;
            $data['sub_mchid'] = $this->app->getConfig()->get('sub_mch_id');
            $data['sub_appid'] = $subAppid;
            if (empty($data['sp_appid'])) throw new PayException('服务商支付需要配置服务商AppID');
            if (!empty($payer['openid'])) {
                $data['payer']['sub_openid'] = $payer['openid'];
                $data['sub_appid'] = $subAppid;
            }

            $urls = [
                'h5' => self::API_H5_PARTNER_URL,
                'native' => self::API_NATIVE_PARTNER_URL,
                'app' => self::API_APP_APY_PARTNER_URL,
                'jsapi' => self::API_JSAPI_PARTNER_URL,
            ];
        } else {
            $urls = [
                'h5' => self::API_H5_URL,
                'native' => self::API_NATIVE_URL,
                'app' => self::API_APP_APY_URL,
                'jsapi' => self::API_JSAPI_URL,
            ];
        }

        if ($type === 'h5') {
            $data['scene_info'] = [
                'payer_client_ip' => request()->ip(),
                'h5_info' => [
                    'type' => 'Wap',
                ],
            ];
        }

        if (empty($urls[$type])) {
            throw new PayException('缺少请求地址');
        }

        $res = $this->requestJson($urls[$type], $data);

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
     * @return array
     */
    public function batches(string $outBatchNo, string $amount, string $batchName, string $remark, array $transferDetailList)
    {
        $totalFee = '0';
        $amount = bcadd($amount, '0', 2);
        foreach ($transferDetailList as &$item) {
            if ($item['transfer_amount'] >= 2000 && empty($item['user_name'])) {
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

        $appid = $this->appidByType();
        if (!$appid) {
            throw new PayException('暂时只支持微信用户、小程序用户、APP微信登录用户提现');
        }

        $data = [
            'appid' => $appid,
            'out_batch_no' => $outBatchNo,
            'batch_name' => $batchName,
            'batch_remark' => $remark,
            'total_amount' => $amount,
            'total_num' => count($transferDetailList),
            'transfer_detail_list' => $transferDetailList,
        ];

        $res = $this->requestJson(self::API_BATCHES_URL, $data);

        if (!$res) {
            throw new PayException('微信支付:发起商家转账失败');
        }

        if (isset($res['code']) && isset($res['message'])) {
            throw new PayException($res['message']);
        }

        return $res;
    }

    /**
     * 以固定批次号和明细号查询旧商家转账的单笔终态。
     * @param string $orderNo 原批次号及明细号
     * @return array
     */
    public function queryBatchTransfer(string $orderNo): array
    {
        return $this->requestJson('v3/transfer/batches/out-batch-no/' . rawurlencode($orderNo)
            . '/details/out-detail-no/' . rawurlencode($orderNo), [], 'GET');
    }

    /**
     * 发起商家转账(单笔,转账到零钱)
     * @param $order_id
     * @param $transfer_scene_id
     * @param $openid
     * @param $user_name
     * @param $transfer_amount
     * @param $transfer_remark
     * @param $notify_url
     * @param $user_recv_perception
     * @param $transfer_scene_report_infos
     * @return array
     */
    public function transferBills($order_id, $transfer_scene_id, $openid, $user_name, $transfer_amount, $transfer_remark, $notify_url, $user_recv_perception, $transfer_scene_report_infos)
    {
        $appid = $this->appidByType();
        if ($appid === '') {
            throw new PayException('暂时只支持微信用户、小程序用户、APP微信登录用户提现');
        }
        if ($transfer_amount > 200000) {
            if ($user_name === '') {
                throw new PayException('金额大于等于2000时，收款人姓名必须填写');
            }
            $user_name = $this->encryptor($user_name);
        } else {
            $user_name = '';
        }
        $data = [
            'appid' => $appid,
            'out_bill_no' => $order_id,
            'transfer_scene_id' => $transfer_scene_id,
            'openid' => $openid,
            'user_name' => $user_name,
            'transfer_amount' => (int)$transfer_amount,
            'transfer_remark' => $transfer_remark,
            'notify_url' => $notify_url,
            'user_recv_perception' => $user_recv_perception,
            'transfer_scene_report_infos' => $transfer_scene_report_infos,
        ];
        $res = $this->requestJson(self::API_TRANSFER_BILLS_URL, $data);
        if (!$res || isset($res['code'], $res['message'])) {
            throw new PayException($res['message'] ?? '微信支付:发起商家转账失败');
        }
        return $res;
    }

    /**
     * 查询转账单
     * @param string $outBillNo
     * @return array
     */
    public function queryTransferBills(string $outBillNo)
    {
        $res = $this->requestJson(sprintf(self::API_TRANSFER_QUERY_URL, $outBillNo), [], 'GET');

        if (!$res) {
            throw new PayException('发起退款查询失败');
        }

        return $res;
    }

    /**
     * 退款
     * @param string $outTradeNo
     * @param array $options
     * @return array
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        if (!isset($options['pay_price'])) {
            throw new PayException('缺少pay_price');
        }
        $totalFee = (int)bcmul((string)$options['pay_price'], '100', 0);
        $refundFee = isset($options['refund_price']) ? (int)bcmul((string)$options['refund_price'], '100', 0) : null;
        $refundReason = $options['desc'] ?? '';
        $refundNo = $options['refund_id'] ?? $outTradeNo;
        /*仅针对老资金流商户使用
        REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
        REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款
        */
        $refundAccount = $options['refund_account'] ?? 'AVAILABLE';

        // 默认参数为商户订单号；仅上层明确标记 trade_no 时才是微信支付流水号。
        // 两种编号字段互斥，不能把中台 order_no 放入 transaction_id。
        $orderField = ($options['type'] ?? '') === 'trade_no' ? 'transaction_id' : 'out_trade_no';
        $data = [
            $orderField => $outTradeNo,
            'out_refund_no' => $refundNo,
            'amount' => [
                'refund' => (int)$refundFee,
                'currency' => 'CNY',
                'total' => (int)$totalFee,
            ],
            'funds_account' => $refundAccount,
        ];

        // 退款通知复用 V3 验签入口，支付通知地址不会自动成为退款通知地址。
        $notifyUrl = trim((string)$this->app->getConfig()->get('notify_url'));
        if ($notifyUrl !== '') {
            $data['notify_url'] = $notifyUrl;
        }

        if ($refundReason) {
            $data['reason'] = $refundReason;
        }

        //服务商支付退款
        if ($this->app->getConfig()->get('sub_mch_id')) {
            $data['sub_mchid'] = $this->app->getConfig()->get('sub_mch_id');
        }

        $res = $this->requestJson(self::API_REFUND_URL, $data);

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
     * @return array
     */
    public function queryRefund(string $outRefundNo)
    {
        $query = [];
        if ($this->app->getConfig()->get('sub_mch_id')) {
            $query['sub_mchid'] = $this->app->getConfig()->get('sub_mch_id');
        }
        $res = $this->requestJson(sprintf(self::API_REFUND_QUERY_URL, rawurlencode($outRefundNo)), $query, 'GET');

        if (!$res) {
            throw new PayException('发起退款查询失败');
        }

        if (isset($res['code']) && isset($res['message'])) {
            throw new PayException($res['message']);
        }

        return $res;
    }

    /**
     * 小程序/公众号 JSAPI 支付参数(与旧 configForJSSDKPayment 形状一致,timestamp 小写)
     * @param string $appid
     * @param string $prepayId
     * @return array
     */
    public function configForJSSDKPayment(string $appid, string $prepayId)
    {
        $config = $this->app->getUtils()->buildMiniAppConfig($prepayId, $appid);
        $config['timestamp'] = $config['timeStamp'];
        unset($config['timeStamp']);

        return $config;
    }

    /**
     * APP支付参数
     * @param string $prepayId
     * @return array
     */
    public function configForAppPayment(string $prepayId): array
    {
        return $this->app->getUtils()->buildAppConfig($prepayId, $this->appids['app']);
    }

    /**
     * 按原商户订单号查询支付结果。
     * @param string $orderNo 原订单号
     * @return array
     */
    public function queryOrder(string $orderNo): array
    {
        $partner = (bool)$this->app->getConfig()->get('sub_mch_id');
        $params = $partner ? ['sp_mchid' => $this->app->getConfig()->get('mch_id'),
            'sub_mchid' => $this->app->getConfig()->get('sub_mch_id')] : ['mchid' => $this->app->getConfig()->get('mch_id')];
        $result = $this->requestJson('v3/pay/' . ($partner ? 'partner/' : '') . 'transactions/out-trade-no/' . rawurlencode($orderNo), $params, 'GET', ['ORDER_NOT_EXIST']);
        return ($result['code'] ?? '') === 'ORDER_NOT_EXIST' ? ['trade_state' => 'NOTFOUND'] : $result;
    }

    /**
     * 关闭普通支付订单。
     * @param string $orderNo 原订单号
     * @return bool
     */
    public function closeOrder(string $orderNo): bool
    {
        $partner = (bool)$this->app->getConfig()->get('sub_mch_id');
        $params = $partner ? ['sp_mchid' => $this->app->getConfig()->get('mch_id'),
            'sub_mchid' => $this->app->getConfig()->get('sub_mch_id')] : ['mchid' => $this->app->getConfig()->get('mch_id')];
        $result = $this->requestJson('v3/pay/' . ($partner ? 'partner/' : '') . 'transactions/out-trade-no/' . rawurlencode($orderNo) . '/close', $params, 'POST', ['ORDER_CLOSED', 'ORDER_NOT_EXIST']);
        return !$result || in_array($result['code'] ?? '', ['ORDER_CLOSED', 'ORDER_NOT_EXIST'], true);
    }

    /**
     * 敏感字段 RSA 加密(收款人姓名等)
     * @param string $plaintext
     * @return string
     */
    public function encryptor(string $plaintext): string
    {
        return $this->app->getUtils()->encryptWithRsaPublicKey($plaintext, $this->publicKeyId ?: null);
    }

    /**
     * 支付/退款回调
     * 中间件内先验签(Wechatpay-Signature)再交给业务回调,
     * 业务回调返回 true 才应答 SUCCESS，其余结果返回 HTTP 500 供微信重试。
     * @param callable $callback function(Message $message, string $eventType)
     * @return ThinkResponse
     */
    public function handleNotify($callback): ThinkResponse
    {
        return $this->handleServerNotify($callback);
    }

    /**
     * 商家转账回调(MCHTRANSFER.BILL.FINISHED)
     * @param callable $callback
     * @return ThinkResponse
     */
    public function handleTransferNotify($callback): ThinkResponse
    {
        return $this->handleServerNotify($callback);
    }

    /**
     * 回调统一处理
     * @param callable $callback
     * @return ThinkResponse
     */
    protected function handleServerNotify($callback): ThinkResponse
    {
        $this->app->setRequestFromSymfonyRequest(Support::symfonyRequest());
        // 在解密和业务处理前强制验签，证书缺失也必须拒绝回调。
        try {
            $this->validateMessage($this->app->getRequest());
        } catch (\Throwable $e) {
            return Support::toThinkResponse(new Psr7Response(401, [], (string)json_encode([
                'code' => 'FAIL',
                'message' => '验签失败',
            ], JSON_UNESCAPED_UNICODE)));
        }

        $server = $this->app->getServer();
        $server->with(function (Message $message, \Closure $next) use ($callback) {
            $result = call_user_func_array($callback, [$message, $message->getEventType()]);
            if ($result === true) {
                return $next($message);
            }

            return new Psr7Response(500, [], (string)json_encode([
                'code' => 'FAIL',
                'message' => is_string($result) ? $result : '处理失败',
            ], JSON_UNESCAPED_UNICODE));
        });

        try {
            return Support::toThinkResponse($server->serve());
        } catch (\Throwable $e) {
            \think\facade\Log::error('[WechatV3] 通知处理失败 type=' . get_class($e));
            return Support::toThinkResponse(new Psr7Response(500, ['Content-Type' => 'application/json'], '{"code":"FAIL","message":"处理失败"}'));
        }
    }

    /**
     * 证书模式下载并验证平台证书，短期缓存且检查有效期。
     * 公钥模式无需调用(直接用 v3_pay_public_key/v3_pay_public_pem 构造 platform_certs)。
     * 下载或验签失败抛异常，不保存未经验证的证书。
     * @param Application $app 未配置 platform_certs 的应用实例
     * @param bool $force 是否强制刷新
     * @return array<string, string>
     */
    public static function downloadPlatformCerts(Application $app, bool $force = false): array
    {
        $cacheKey = 'wx_v3_platform_certs_' . $app->getConfig()->get('mch_id');
        $cached = $force ? null : CacheService::get($cacheKey);
        if (is_array($cached) && $cached) {
            foreach ($cached as $path) {
                $info = is_file($path) ? openssl_x509_parse((string)file_get_contents($path)) : false;
                if (!$info || $info['validFrom_time_t'] > time() || $info['validTo_time_t'] < time() + 3600) {
                    $cached = null;
                    break;
                }
            }
            if ($cached) {
                return $cached;
            }
        }

        try {
            $response = $app->getClient()->get('v3/certificates');
            if ($response->getStatusCode() !== 200) throw new PayException('微信平台证书下载失败');
            $data = $response->toArray(false);
            $certs = [];
            $plaintextCerts = [];
            foreach (($data['data'] ?? []) as $item) {
                $plaintext = AesGcm::decrypt(
                    $item['encrypt_certificate']['ciphertext'],
                    (string) $app->getConfig()->get('secret_key'),
                    $item['encrypt_certificate']['nonce'],
                    $item['encrypt_certificate']['associated_data']
                );
                $info = openssl_x509_parse($plaintext);
                if (!$info || !preg_match('/^[A-Fa-f0-9]+$/D', (string)$item['serial_no'])
                    || strcasecmp((string)$info['serialNumberHex'], (string)$item['serial_no']) !== 0
                    || $info['validFrom_time_t'] > time() || $info['validTo_time_t'] <= time() + 3600) {
                    throw new PayException('微信平台证书序列号或有效期不正确');
                }
                $path = root_path('runtime/pem') . 'platform_cert_' . $item['serial_no'] . '.pem';
                $certs[$item['serial_no']] = $path;
                $plaintextCerts[$item['serial_no']] = $plaintext;
            }
            if ($certs) {
                // 首次下载由 APIv3 密钥认证解密后，以返回的证书验证本次下载响应。
                $verifier = new Application(['platform_certs' => $plaintextCerts] + $app->getConfig()->all());
                $message = new Psr7Response($response->getStatusCode(), $response->getHeaders(false), $response->getContent(false));
                $timestamp = $message->getHeaderLine('Wechatpay-Timestamp');
                if (!ctype_digit($timestamp) || abs(time() - (int)$timestamp) > 300) throw new PayException('平台证书响应时间无效');
                $verifier->getValidator()->validate($message);
                foreach ($certs as $serial => $path) {
                    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0700, true) && !is_dir(dirname($path))) throw new PayException('平台证书目录创建失败');
                    if (file_put_contents($path, $plaintextCerts[$serial], LOCK_EX) === false) throw new PayException('平台证书保存失败');
                }
                CacheService::set($cacheKey, $certs, 3600);
            }

            return $certs;
        } catch (\Throwable $e) {
            throw new PayException('微信平台证书下载或验签失败', [], 0, $e);
        }
    }

    /**
     * 按当前类型取 appid
     * @return string
     */
    protected function appidByType(): string
    {
        if ($this->type === 'JSAPI') {
            return $this->appids['wechat'] ?? '';
        }
        if ($this->type === 'mini') {
            return $this->appids['miniprog'] ?? '';
        }
        if ($this->type === 'APP') {
            return $this->appids['app'] ?? '';
        }
        return '';
    }

    /**
     * 发起 JSON 请求并统一返回数组(throw=false,业务错误由调用方按 code/message 判断)
     * @param string $url
     * @param array $data
     * @param string $method
     * @return array
     */
    protected function requestJson(string $url, array $data = [], string $method = 'POST', array $allowedErrors = []): array
    {
        $client = $this->app->getClient();
        if ($method === 'GET') {
            $response = $client->get($url, $data ? ['query' => $data] : []);
        } else {
            $response = $client->postJson($url, $data);
        }

        $status = $response->getStatusCode();
        // 微信非 2xx 错误响应可能不带签名，不能将其用于资金终态判断。
        if ($status >= 200 && $status < 300) {
            $this->validateMessage(new Psr7Response($status, $response->getHeaders(false), $response->getContent(false)));
        } else {
            if ($allowedErrors) {
                $this->validateMessage(new Psr7Response($status, $response->getHeaders(false), $response->getContent(false)));
                $error = $response->toArray(false);
                if (is_array($error) && in_array($error['code'] ?? '', $allowedErrors, true)) return $error;
            }
            throw new PayException('微信支付接口请求失败 HTTP ' . $status);
        }
        if ($status === 204) return [];
        $res = $response->toArray(false);

        return is_array($res) ? $res : [];
    }

    /**
     * 验签并仅在未知证书序列号时刷新，拒绝过期和未来签名。
     * @param \Psr\Http\Message\MessageInterface $message 请求或响应
     * @return void
     */
    protected function validateMessage(\Psr\Http\Message\MessageInterface $message): void
    {
        $timestamp = $message->getHeaderLine('Wechatpay-Timestamp');
        if (!ctype_digit($timestamp) || abs(time() - (int)$timestamp) > 300) throw new PayException('微信支付签名时间无效');
        $serial = $message->getHeaderLine('Wechatpay-Serial');
        if ($serial !== '' && !$this->app->getMerchant()->getPlatformCert($serial) && $this->publicKeyId === '') {
            $refreshKey = 'wx_v3_cert_refresh_attempt:' . $this->app->getConfig()->get('mch_id');
            $force = !CacheService::get($refreshKey);
            if ($force) CacheService::set($refreshKey, true, 60);
            $certs = self::downloadPlatformCerts($this->app, $force);
            $config = $this->app->getConfig()->all();
            $config['platform_certs'] = $certs;
            $client = $this->app->getClient();
            $this->app = (new Application($config))->setClient($client);
            $this->app->setRequestFromSymfonyRequest(Support::symfonyRequest());
        }
        if ($this->publicKeyId === '') {
            $certificate = $this->app->getMerchant()->getPlatformCert($serial);
            $info = $certificate ? openssl_x509_parse((string)$certificate) : false;
            if (!$info || strcasecmp((string)$info['serialNumberHex'], $serial) !== 0
                || $info['validFrom_time_t'] > time() || $info['validTo_time_t'] <= time()) {
                throw new PayException('微信支付平台证书无效或已过期');
            }
        }
        $this->app->getValidator()->validate($message);
    }
}
