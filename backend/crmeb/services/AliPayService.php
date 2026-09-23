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

namespace crmeb\services;

use Alipay\EasySDK\Payment\Wap\Models\AlipayTradeWapPayResponse;
use think\facade\Db;
use think\facade\Event;
use think\facade\Log;
use think\facade\Route as Url;
use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use crmeb\exceptions\PayException;
use crmeb\services\pay\PayChannelConfig;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;

/**
 * 支付宝支付 SDK 封装
 * 位于 crmeb 核心层，作为 driver/pay/AliPay 的底层封装，不依赖 app 层服务
 * Class AliPayService
 * @package crmeb\services
 */
class AliPayService
{

    /**
     * 支付宝渠道标识（与 app\services\pay\PayServices::ALIAPY_PAY 保持一致）
     */
    const ALIPAY_CHANNEL_KEY = 'alipay';

    /**
     * 配置
     * @var array
     */
    protected $config = [
        'appId' => '',
        'merchantPrivateKey' => '',//应用私钥
        'alipayPublicKey' => '',//支付宝公钥
        'notifyUrl' => '',//可设置异步通知接收服务地址
        'encryptKey' => '',//可设置AES密钥，调用AES加解密相关接口时需要（可选）
        'alipayCertPath' => '',//支付宝证书路径(可选)
        'alipayRootCertPath' => '',//支付宝根证书路径(可选)
        'merchantCertPath' => '',//商户证书路径(可选)
        'signType' => 0,//加签类型：0=>密钥 1=>证书
    ];

    /**
     * @var ResponseChecker
     */
    protected $response;

    /**
     * @var static
     */
    protected static $instance;

    /**
     * AliPayService constructor.
     * @param array $config
     */
    protected function __construct(array $config = [])
    {
        if (!$config) {
            // 支付配置统一存储于 eb_pay_channel.config_fields，不再读取 system_config
            $alipayConfig = PayChannelConfig::get(self::ALIPAY_CHANNEL_KEY);
            $config = [
                'appId' => $alipayConfig['ali_pay_appid'] ?? '',
                'merchantPrivateKey' => $alipayConfig['alipay_merchant_private_key'] ?? '',
                'alipayPublicKey' => $alipayConfig['alipay_public_key'] ?? '',
                'signType' => (int)($alipayConfig['alipay_sign_type'] ?? 0),
                'notifyUrl' => sys_config('site_url') . Url::buildUrl('/api/pay/notify/alipay'),
                'alipayCertPath' => $this->getPemPath('alipay_cert_path'),
                'alipayRootCertPath' => $this->getPemPath('alipay_root_cert_path'),
                'merchantCertPath' => $this->getPemPath('merchant_cert_path'),
            ];
        }
        $this->config = array_merge($this->config, $config);
        $this->initialize();
        $this->response = new ResponseChecker();
    }

    public function getPemPath(string $name)
    {
        // 架构豁免说明：本类位于 crmeb 核心层，依赖铁律禁止调用 app 层（SystemPemDao/SystemPemServices 均在 app 层），
        // 核心层无 DAO 体系，故此处 Db 直查为合规做法（同 ModuleStateChecker/MysqlBackupService 模式），禁止"修复"为 app 层 DAO
        $info = Db::name('system_pem')->where('name', $name)->find();
        if ($info && !empty($info['path'])) {
            $path = root_path('runtime/pem') . $info['path'] . '.pem';
            if (!file_exists($path)) {
                // 如果 runtime/pem 文件夹不存在，创建文件夹
                if (!file_exists(root_path('runtime/pem'))) {
                    mkdir(root_path('runtime/pem'), 0777, true);
                }
                file_put_contents($path, $info['content'] ?? '');
            }
            if ($path) return $path;
        }
        // 回退：从支付渠道配置读取证书路径（支付宝证书统一存储于 eb_pay_channel.config_fields）
        $alipayConfig = PayChannelConfig::get(self::ALIPAY_CHANNEL_KEY);
        $path = $alipayConfig[$name] ?? '';
        if (strstr($path, 'http://') || strstr($path, 'https://')) {
            $path = parse_url($path)['path'] ?? '';
        }
        $path = root_path('runtime/pem') . ltrim($path, '/');
        if (!file_exists($path)) {
            $path = public_path('uploads') . ltrim($path, '/');
        }
        return $path;
    }

    /**
     * 实例化
     * @param array $config
     * @return static
     */
    public static function instance(array $config = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
        Factory::setOptions($this->getOptions());
    }

    /**
     * 设置配置
     * @return Config
     */
    protected function getOptions()
    {
        $options = new Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';

        $options->appId = $this->config['appId'];
        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $options->merchantPrivateKey = $this->config['merchantPrivateKey'];

        if ($this->config['signType'] == 0) {
            // 密钥模式
            $options->alipayPublicKey = $this->config['alipayPublicKey'];
        } else {
            // 证书模式
            $options->alipayCertPath = $this->config['alipayCertPath'];
            $options->alipayRootCertPath = $this->config['alipayRootCertPath'];
            $options->merchantCertPath = $this->config['merchantCertPath'];
            $options->alipayPublicKey = '';
        }
        //可设置异步通知接收服务地址（可选）
        $options->notifyUrl = $this->config['notifyUrl'];
        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
        if ($this->config['encryptKey']) {
            $options->encryptKey = $this->config['encryptKey'];
        }

        return $options;
    }

    /**
     * 创建订单
     * @param string $title 商品名称
     * @param string $orderId 订单号
     * @param string $totalAmount 支付金额
     * @param string $passbackParams 备注
     * @param string $quitUrl 同步跳转地址
     * @param string $returnUrl
     * @param bool $isCode
     * @return AlipayTradeWapPayResponse
     */
    public function create(string $title, string $orderId, string $totalAmount, string $passbackParams, string $quitUrl = '', string $returnUrl = '', bool $isCode = false)
    {
        $title = trim($title);
        try {
            if ($isCode) {
                //二维码支付
                $result = Factory::payment()->faceToFace()->optional('passback_params', $passbackParams)->precreate($title, $orderId, $totalAmount);
            } else if (request()->isApp()) {
                //app支付
                $result = Factory::payment()->app()->optional('passback_params', $passbackParams)->pay($title, $orderId, $totalAmount);
            } else {
                //h5支付
                $result = Factory::payment()->wap()->optional('passback_params', $passbackParams)->pay($title, $orderId, $totalAmount, $quitUrl, $returnUrl);
            }
            if ($this->response->success($result)) {
                return $result->body ?? $result;
            } else {
                throw new PayException('失败原因:' . $result->msg . ',' . $result->subMsg);
            }
        } catch (\Exception $e) {
            throw new PayException($e->getMessage());
        }
    }

    /**
     * 订单退款
     * @param string $outTradeNo 订单号
     * @param string $totalAmount 退款金额
     * @param string $refund_id 退款单号
     * @return \Alipay\EasySDK\Payment\Common\Models\AlipayTradeRefundResponse
     */
    public function refund(string $outTradeNo, string $totalAmount, string $refund_id)
    {
        try {
            $result = Factory::payment()->common()->refund($outTradeNo, $totalAmount, $refund_id);
            if ($this->response->success($result)) {
                return $result;
            } else {
                throw new PayException('失败原因:' . $result->msg . ',' . $result->subMsg);
            }
        } catch (\Exception $e) {
            throw new PayException($e->getMessage());
        }
    }

    /**
     * 查询交易退款单号信息
     * @param string $outTradeNo
     * @param string $outRequestNo
     * @return \Alipay\EasySDK\Payment\Common\Models\AlipayTradeFastpayRefundQueryResponse
     */
    public function queryRefund(string $outTradeNo, string $outRequestNo)
    {
        try {
            $result = Factory::payment()->common()->queryRefund($outTradeNo, $outRequestNo);
            if ($this->response->success($result)) {
                return $result;
            } else {
                throw new PayException('失败原因:' . $result->msg . ',' . $result->subMsg);
            }
        } catch (\Exception $e) {
            throw new PayException($e->getMessage());
        }
    }

    /**
     * 支付异步回调
     * @return string
     */
    public static function handleNotify()
    {
        return self::instance()->notify(function ($notify) {
            if (isset($notify->out_trade_no)) {

                $data = [
                    'attach' => $notify->attach,
                    'out_trade_no' => $notify->out_trade_no,
                    'transaction_id' => $notify->trade_no
                ];

                return Event::until('Notify', [$data, self::ALIPAY_CHANNEL_KEY]);
            }
            return false;
        });
    }

    /**
     * 异步回调
     * @param callable $notifyFn
     * @return string
     */
    public function notify(callable $notifyFn)
    {
        app()->request->filter(['trim']);
        $paramInfo = app()->request->param();
        if (isset($paramInfo['type'])) {
            unset($paramInfo['type']);
        }
        //商户订单号
        $postOrder['out_trade_no'] = $paramInfo['out_trade_no'] ?? '';
        //支付宝交易号
        $postOrder['trade_no'] = $paramInfo['trade_no'] ?? '';
        //交易状态
        $postOrder['trade_status'] = $paramInfo['trade_status'] ?? '';
        //备注
        $postOrder['attach'] = isset($paramInfo['passback_params']) ? urldecode($paramInfo['passback_params']) : '';
        if (in_array($paramInfo['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])
            && $this->verifyNotify($paramInfo)
            && $this->validateNotifyBusinessData($paramInfo)) {
            try {
                if ($notifyFn((object)$postOrder)) {
                    return 'success';
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                Log::error('支付宝异步会回调成功,执行函数错误。错误单号：' . $postOrder['out_trade_no']);
            }
        }
        return 'fail';

    }

    /**
     * 校验回调的商户身份和金额，避免仅凭合法签名就给错误金额订单入账。
     */
    protected function validateNotifyBusinessData(array $param): bool
    {
        $config = PayChannelConfig::get(self::ALIPAY_CHANNEL_KEY);
        $appId = trim((string)($config['ali_pay_appid'] ?? $this->config['appId'] ?? ''));
        if ($appId === '' || (string)($param['app_id'] ?? '') !== $appId) {
            Log::warning('支付宝回调 app_id 不匹配', ['out_trade_no' => $param['out_trade_no'] ?? '']);
            return false;
        }

        // seller_id/seller_email 只有商户配置了对应字段时才校验，兼容现有配置结构。
        foreach (['seller_id', 'seller_email'] as $sellerField) {
            $expected = trim((string)($config[$sellerField] ?? ''));
            if ($expected !== '' && (string)($param[$sellerField] ?? '') !== $expected) {
                Log::warning('支付宝回调商户标识不匹配', ['field' => $sellerField, 'out_trade_no' => $param['out_trade_no'] ?? '']);
                return false;
            }
        }

        $orderNo = trim((string)($param['out_trade_no'] ?? ''));
        $paidAmount = trim((string)($param['total_amount'] ?? ''));
        if ($orderNo === '' || $paidAmount === '') return false;

        // 不同业务订单使用不同表和金额字段；至少命中一条本地记录才能确认金额。
        $candidates = [
            ['order_center', 'order_no', ['pay_price', 'paid_price', 'total_price']],
            ['member_order', 'order_id', ['pay_price', 'member_price']],
            ['user_recharge', 'order_id', ['price']],
            ['offline_receipt', 'order_no', ['paid_price', 'total_price']],
        ];
        foreach ($candidates as [$table, $key, $amountFields]) {
            try {
                $row = Db::name($table)->where($key, $orderNo)->find();
                if (!$row) continue;
                foreach ($amountFields as $field) {
                    if (array_key_exists($field, $row) && $row[$field] !== null) {
                        $expected = number_format((float)$row[$field], 2, '.', '');
                        $actual = number_format((float)$paidAmount, 2, '.', '');
                        if ($expected !== $actual) {
                            Log::warning('支付宝回调金额不匹配', ['out_trade_no' => $orderNo, 'expected' => $expected, 'actual' => $actual]);
                            return false;
                        }
                        return true;
                    }
                }
            } catch (\Throwable $e) {
                // 兼容未安装某类业务表，继续检查其他订单类型。
            }
        }
        Log::warning('支付宝回调找不到本地订单', ['out_trade_no' => $orderNo]);
        return false;
    }

    /**
     * 验签
     * @return bool
     */
    protected function verifyNotify(array $param)
    {
        try {
            return Factory::payment()->common()->verifyNotify($param);
        } catch (\Exception $e) {
            Log::error('支付宝回调成功,验签发生错误，错误原因:' . $e->getMessage());
        }
        return false;
    }

    /**
     * 商家支付接口
     *
     * @param array $bizParams 业务参数
     * @return mixed|false 支付结果或者false
     * @throws PayException 支付异常
     */
    public function merchantPay(array $bizParams, $alipaySignType = 0)
    {
        try {
            // 调用工厂类的通用方法执行支付宝转账操作
            $method = $alipaySignType == 0 ? 'alipay.fund.trans.toaccount.transfer' : 'alipay.fund.trans.uni.transfer';
            $result = Factory::util()->generic()->execute($method, [], $bizParams);
            // 判断支付是否成功
            if ($this->response->success($result)) {
                return $result;
            } else {
                Log::error('支付宝转账失败，失败原因:' . $result->msg . ' | ' . $result->subCode . ' | ' . $result->subMsg);
                return false;
            }
        } catch (\Exception $e) {
            // 记录日志并返回false
            Log::error('支付宝转账失败，失败原因:' . $e->getMessage());
            return false;
        }
    }

}
