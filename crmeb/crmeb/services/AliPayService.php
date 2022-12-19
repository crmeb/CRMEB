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

namespace crmeb\services;

use Alipay\EasySDK\Payment\Wap\Models\AlipayTradeWapPayResponse;
use crmeb\utils\Hook;
use think\facade\Event;
use think\facade\Log;
use think\facade\Route as Url;
use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use crmeb\exceptions\PayException;
use app\services\pay\PayNotifyServices;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;

/**
 * Class AliPayService
 * @package crmeb\services
 */
class AliPayService
{

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
            $config = [
                'appId' => sys_config('ali_pay_appid'),
                'merchantPrivateKey' => sys_config('alipay_merchant_private_key'),
                'alipayPublicKey' => sys_config('alipay_public_key'),
                'notifyUrl' => sys_config('site_url') . Url::buildUrl('/api/pay/notify/alipay'),
            ];
        }
        $this->config = array_merge($this->config, $config);
        $this->initialize();
        $this->response = new ResponseChecker();
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
        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        $options->alipayPublicKey = $this->config['alipayPublicKey'];
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
     * @param string $siteUrl
     * @param bool $isCode
     * @return AlipayTradeWapPayResponse
     */
    public function create(string $title, string $orderId, string $totalAmount, string $passbackParams, string $quitUrl = '', string $siteUrl = '', bool $isCode = false)
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
                $result = Factory::payment()->wap()->optional('passback_params', $passbackParams)->pay($title, $orderId, $totalAmount, $quitUrl, $siteUrl);
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
                if (isset($notify->attach) && $notify->attach) {
                    if (($count = strpos($notify->out_trade_no, '_')) !== false) {
                        $notify->trade_no = $notify->out_trade_no;
                        $notify->out_trade_no = substr($notify->out_trade_no, $count + 1);
                    }
                    return (new Hook(PayNotifyServices::class, 'aliyun'))->listen($notify->attach, $notify->out_trade_no, $notify->trade_no);
                }
                return false;
            }
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
        $paramInfo = app()->request->postMore([
            ['gmt_create', ''],
            ['charset', ''],
            ['seller_email', ''],
            ['subject', ''],
            ['sign', ''],
            ['buyer_id', ''],
            ['invoice_amount', ''],
            ['notify_id', ''],
            ['fund_bill_list', ''],
            ['notify_type', ''],
            ['trade_status', ''],
            ['receipt_amount', ''],
            ['buyer_pay_amount', ''],
            ['app_id', ''],
            ['seller_id', ''],
            ['sign_type', ''],
            ['gmt_payment', ''],
            ['notify_time', ''],
            ['passback_params', ''],
            ['version', ''],
            ['out_trade_no', ''],
            ['total_amount', ''],
            ['trade_no', ''],
            ['auth_app_id', ''],
            ['buyer_logon_id', ''],
            ['point_amount', ''],
        ], false, false);

        //商户订单号
        $postOrder['out_trade_no'] = $paramInfo['out_trade_no'] ?? '';
        //支付宝交易号
        $postOrder['trade_no'] = $paramInfo['trade_no'] ?? '';
        //交易状态
        $postOrder['trade_status'] = $paramInfo['trade_status'] ?? '';
        //备注
        $postOrder['attach'] = isset($paramInfo['passback_params']) ? urldecode($paramInfo['passback_params']) : '';
        if (in_array($paramInfo['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED']) && $this->verifyNotify($paramInfo)) {
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
}
