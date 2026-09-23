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

namespace driver\pay\WechatPay;


use crmeb\exceptions\AdminException;
use crmeb\services\pay\BasePay;
use crmeb\exceptions\PayException;
use crmeb\services\pay\PayInterface;

/**
 * 微信支付
 * Class Driver
 * @package driver\pay\WechatPay
 */
class Driver extends BasePay implements PayInterface
{

    protected function initialize(array $config)
    {
    }

    /**
     * 获取原支付身份，不保存密钥。
     * @param array $options 支付参数
     * @return array
     */
    public function getPaymentContext(array $options): array
    {
        $platform = $options['platform'] ?? '';
        $scene = $platform === 'wechat_mini' ? 'mini' : ($platform === 'app' ? 'app' : 'mp');
        $config = WechatPaySdk::options($scene)['payment'] ?? [];
        return ['channel_key' => 'weixin', 'platform' => $platform,
            'mode' => $scene === 'mini' && !empty($options['pay_new_weixin_open']) ? 'mini_shop' : 'v2',
            'mch_id' => $config['merchant_id'] ?? '', 'sub_mch_id' => $config['sub_mch_id'] ?? '',
            'app_id' => $config['sub_appid'] ?? $config['app_id'] ?? '', 'openid' => (string)($options['openid'] ?? '')];
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
        $this->authSetPayType($options);
        $orderOptions = array_intersect_key($options, array_flip(['time_expire', 'expire_time']));

        switch ($this->payType) {
            case 'NATIVE':
                $res = WechatPaySdk::nativePay('mp', null, $orderId, $totalFee, $attach, $body, $detail, 'NATIVE', $orderOptions);
                // 支付二维码中间 logo 由 options 注入（plugin.json default_options），驱动不读取系统配置
                if (is_array($res)) {
                    $res['logo'] = (string)($options['wap_login_logo'] ?? '');
                }
                return $res;
            case 'APP':
                return WechatPaySdk::appPay('app', null, $orderId, $totalFee, $attach, $body, $detail, 'APP', $orderOptions);
            case 'JSAPI':
                if (empty($options['openid'])) {
                    throw new PayException('缺少openid');
                }
                if (($options['platform'] ?? '') === 'wechat_mini') {
                    if (!empty($options['pay_new_weixin_open'])) {
                        return WechatPaySdk::newJsPay('mini', $options['openid'], $orderId, $totalFee, $attach, $body, $detail, $options);
                    }
                    return WechatPaySdk::jsPay('mini', $options['openid'], $orderId, $totalFee, $attach, $body, $detail, 'JSAPI', $orderOptions);
                }
                return WechatPaySdk::jsPay('mp', $options['openid'], $orderId, $totalFee, $attach, $body, $detail, 'JSAPI', $orderOptions);
            case 'h5':
                return WechatPaySdk::paymentPrepare('mp', null, $orderId, $totalFee, $attach, $body, $detail, 'MWEB', $orderOptions);
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
        $scene = ($options['type'] ?? '') === 'mini' ? 'mini' : (($options['type'] ?? '') === 'APP' ? 'app' : 'mp');
        return WechatPaySdk::merchantPay($scene, $openid, $orderId, $amount, $options['desc'] ?? '');
    }

    /**
     * 获取旧企业付款的普通商户身份，与新版小程序收款配置分离。
     * @param array $options 付款场景及收款者
     * @return array
     */
    public function getTransferContext(array $options): array
    {
        $payment = \crmeb\services\pay\PayChannelConfig::get('weixin');
        $platform = (string)($options['platform'] ?? 'wechat_mp');
        $appField = $platform === 'wechat_mini' ? 'routine_appId' : ($platform === 'app' ? 'wechat_app_appid' : 'wechat_appid');
        if (!empty($payment['mer_type']) || empty($payment[$appField]) || empty($payment['pay_weixin_mchid']) || empty($options['openid'])) {
            throw new PayException('旧企业付款需要有效的直连商户、应用及收款人配置');
        }
        return ['channel_key' => 'weixin', 'platform' => $platform, 'mode' => 'transfer_v2',
            'mch_id' => trim((string)$payment['pay_weixin_mchid']), 'sub_mch_id' => '',
            'app_id' => trim((string)$payment[$appField]), 'openid' => (string)$options['openid'],
            'payer_uid' => (int)($options['payer_uid'] ?? 0)];
    }

    /**
     * 查询旧企业付款终态。
     * @param string $orderNo 固定付款单号
     * @param array $context 原付款身份
     * @return array
     */
    public function queryEnterpriseTransfer(string $orderNo, array $context): array
    {
        return WechatPaySdk::queryEnterpriseTransfer($orderNo, $context);
    }

    /**
     * 退款
     * @param string $outTradeNo
     * @param array $opt
     * @return Collection|mixed|ResponseInterface
     */
    public function refund(string $outTradeNo, array $opt = [])
    {
        if (!isset($opt['pay_price'])) throw new PayException('缺少pay_price');
        $totalFee = floatval(bcmul($opt['pay_price'], 100, 0));
        $refundFee = isset($opt['refund_price']) ? floatval(bcmul($opt['refund_price'], 100, 0)) : null;
        $refundReason = $opt['desc'] ?? '';
        $refundNo = $opt['refund_id'] ?? $outTradeNo;
        $opUserId = $opt['op_user_id'] ?? null;
        $type = $opt['type'] ?? 'out_trade_no';
        /**
         * 仅针对老资金流商户使用
         * REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
         * REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款
         */
        $refundAccount = $opt['refund_account'] ?? 'REFUND_SOURCE_UNSETTLED_FUNDS';
        $context = $opt['payment_context'] ?? [];
        if ($context) {
            $scene = ($context['platform'] ?? '') === 'wechat_mini' ? 'mini' : (($context['platform'] ?? '') === 'app' ? 'app' : 'mp');
            $current = $this->getPaymentContext(['platform' => $context['platform'] ?? '',
                'pay_new_weixin_open' => ($context['mode'] ?? '') === 'mini_shop']);
            foreach (['mode', 'mch_id', 'sub_mch_id', 'app_id'] as $key) {
                if ($current[$key] !== ($context[$key] ?? '')) throw new PayException('V2原支付商户或应用已变化，请恢复原配置后退款');
            }
            $opt['wechat'] = $scene !== 'mini';
            $opt['pay_new_weixin_open'] = ($context['mode'] ?? '') === 'mini_shop';
        }
        if (!empty($opt['wechat'])) {
            $result = WechatPaySdk::refund($scene ?? 'mp', $outTradeNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundReason, $type, $refundAccount);
        } else {
            if (!empty($opt['pay_new_weixin_open'])) {
                $result = WechatPaySdk::miniRefund('mini', $outTradeNo, $totalFee, $refundFee, $opt);
            } else {
                $result = WechatPaySdk::refund('mini', $outTradeNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundReason, $type, $refundAccount);
            }
        }
        if (!empty($opt['pay_new_weixin_open'])) {
            if ($result['errcode'] != 0) throw new AdminException($result['errmsg']);
        } else {
            if (isset($result['return_code']) && $result['return_code'] != 'SUCCESS') throw new AdminException($result['return_msg']);
            if (isset($result['result_code']) && $result['result_code'] != 'SUCCESS') throw new AdminException($result['err_code_des']);
            if (isset($result['status']) && $result['status'] != 'SUCCESS') throw new AdminException($result['status']);
        }
        $result['status'] = 'PROCESSING';
        return $result;
    }

    /**
     * 查询退款订单
     * @param string $outTradeNo
     * @param string $outRequestNo
     * @param array $other
     * @return mixed
     */
    public function queryRefund(string $outTradeNo, string $outRequestNo, array $other = [])
    {
        $context = $other['payment_context'] ?? [];
        if ($context) $this->assertContext($context);
        if (($other['payment_context']['mode'] ?? '') === 'mini_shop') {
            return WechatPaySdk::queryMiniRefund((string)($other['order_id'] ?? ''), $outRequestNo, $other['payment_context']);
        }
        $scene = ($context['platform'] ?? '') === 'wechat_mini' ? 'mini' : (($context['platform'] ?? '') === 'app' ? 'app' : 'mp');
        return WechatPaySdk::queryRefund($scene, $outRequestNo, 'out_refund_no');
    }

    /**
     * 异步回调（统一处理，不区分小程序/公众号）
     * @param string $type 回调类型（预留）
     * @return mixed
     */
    public function handleNotify(string $type = '')
    {
        return WechatPaySdk::handleNotify('notify');
    }

    /**
     * 查询原支付流水。
     * @param string $orderNo 订单号
     * @param array $context 原支付信息
     * @return array
     */
    public function queryPayment(string $orderNo, array $context): array
    {
        $this->assertContext($context);
        if (($context['mode'] ?? '') === 'mini_shop') return WechatPaySdk::getMiniOrder($orderNo, $context);
        $scene = ($context['platform'] ?? '') === 'wechat_mini' ? 'mini' : (($context['platform'] ?? '') === 'app' ? 'app' : 'mp');
        return WechatPaySdk::queryOrder($scene, $orderNo);
    }

    /**
     * 关闭原支付订单，已支付和未知结果不得当作关单成功。
     * @param string $orderNo 订单号
     * @param array $context 原支付信息
     * @return bool
     */
    public function closePayment(string $orderNo, array $context): bool
    {
        $this->assertContext($context);
        if (($context['mode'] ?? '') === 'mini_shop') {
            $result = WechatPaySdk::getMiniOrder($orderNo, $context);
            if (empty($result['transaction_id']) && !empty($context['expires_at']) && time() > (int)$context['expires_at'] + 60) return true;
            throw new PayException('新版小程序订单需等待支付过期并查询确认后取消');
        }
        $scene = ($context['platform'] ?? '') === 'wechat_mini' ? 'mini' : (($context['platform'] ?? '') === 'app' ? 'app' : 'mp');
        $result = WechatPaySdk::closeOrder($scene, $orderNo);
        return (($result['return_code'] ?? '') === 'SUCCESS' && ($result['result_code'] ?? '') === 'SUCCESS')
            || in_array($result['err_code'] ?? '', ['ORDERCLOSED', 'ORDERNOTEXIST'], true);
    }

    /**
     * 查单、关单和退款必须使用原应用和商户。
     * @param array $context 原支付身份
     * @return void
     */
    protected function assertContext(array $context): void
    {
        $current = $this->getPaymentContext(['platform' => $context['platform'] ?? '',
            'pay_new_weixin_open' => ($context['mode'] ?? '') === 'mini_shop']);
        foreach (['mode', 'mch_id', 'sub_mch_id', 'app_id'] as $key) {
            if ($current[$key] !== ($context[$key] ?? '')) throw new PayException('原支付商户或应用已变化，请恢复原配置后处理');
        }
    }
}
