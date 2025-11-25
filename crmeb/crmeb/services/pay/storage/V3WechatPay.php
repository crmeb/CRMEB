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
use app\services\system\SystemPemServices;
use crmeb\exceptions\PayException;
use crmeb\services\app\MiniProgramService;
use crmeb\services\easywechat\Application;
use crmeb\services\pay\BasePay;
use crmeb\services\pay\PayInterface;
use EasyWeChat\Payment\Order;
use think\facade\Event;

/**
 * Class 微信支付v3
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2022/9/22
 * @package crmeb\services\pay\storage
 */
class V3WechatPay extends BasePay implements PayInterface
{

    /**
     * @var Application
     */
    protected $instance;

    /**
     * @param array $config
     * @return mixed|void
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/9/22
     */
    protected function initialize(array $config = [])
    {
        $wechatAppid = sys_config('wechat_appid');
        $config = [
            'app' => [
                'appid' => sys_config('wechat_app_appid'),
            ],
            'wechat' => [
                'appid' => $wechatAppid,
            ],
            'miniprog' => [
                'appid' => sys_config('routine_appId'),
            ],
            'web' => [
                'appid' => $wechatAppid,
            ],
            'v3_payment' => [
                'mchid' => sys_config('pay_weixin_mchid'),
                'key' => sys_config('pay_weixin_key_v3'),
                'serial_no' => sys_config('pay_weixin_serial_no'),
                'cert_path' => $this->getPemPath('pay_weixin_client_cert'),
                'key_path' => $this->getPemPath('pay_weixin_client_key'),
                'notify_url' => trim(sys_config('site_url')) . '/api/pay/notify/v3wechat',
                'v3_pay_public_key' => sys_config('v3_pay_public_key'),
                'v3_pay_public_pem' => $this->getPemPath('v3_pay_public_pem'),
            ]
        ];

        $config['v3_payment']['mer_type'] = $merType = sys_config('mer_type');
        if ($merType) {
            $config['v3_payment']['sub_mch_id'] = trim(sys_config('pay_sub_merchant_id'));
            $config['v3_payment']['sp_appid'] = trim(sys_config('sp_appid'));
        }

        $this->instance = new Application($config);
    }

    /**
     * 获取证书不带域名的路径
     * @param string $path
     * @return mixed|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/9/22
     */
    public function getPemPath(string $name)
    {
        $systemPemServices = app()->make(SystemPemServices::class);
        $path = $systemPemServices->getPemPath($name);
        if ($path) return $path;
        $path = sys_config($name);
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
     * 创建订单返回支付参数
     * @param string $orderId
     * @param string $totalFee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array|false|mixed|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/9/22
     */
    public function create(string $orderId, string $totalFee, string $attach, string $body, string $detail, array $options = [])
    {
        $this->authSetPayType();

        switch ($this->payType) {
            case Order::NATIVE:
                $res = $this->instance->v3pay->nativePay($orderId, $totalFee, $body, $attach);
                $res['invalid'] = time() + 60;
                $res['logo'] = sys_config('wap_login_logo');
                return $res;
            case Order::APP:
                return $this->instance->v3pay->appPay($orderId, $totalFee, $body, $attach);
            case Order::JSAPI:
                if (empty($options['openid'])) {
                    throw new PayException('缺少openid');
                }
                if (request()->isRoutine()) {
                    // 获取配置  判断是否为新支付
                    if ($options['pay_new_weixin_open']) {
                        return MiniProgramService::newJsPay($options['openid'], $orderId, $totalFee, $attach, $body, $detail, $options);
                    }
                    return $this->instance->v3pay->miniprogPay($options['openid'], $orderId, $totalFee, $body, $attach);
                }
                return $this->instance->v3pay->jsapiPay($options['openid'], $orderId, $totalFee, $body, $attach);
            case 'h5':
                return $this->instance->v3pay->h5Pay($orderId, $totalFee, $body, $attach);
            default:
                throw new PayException('微信支付:支付类型错误');
        }
    }

    /**
     * @param string $openid
     * @param string $orderId
     * @param string $amount
     * @param array $options
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/9/22
     */
    public function merchantPay(string $openid, string $orderId, string $amount, array $options = [])
    {
        return $this->instance->v3pay->setType($options['type'])->batches(
            $orderId,
            $amount,
            $options['batch_name'],
            $options['batch_remark'],
            [
                [
                    'out_detail_no' => $orderId,
                    'transfer_amount' => $amount,
                    'transfer_remark' => $options['batch_remark'],
                    'openid' => $openid
                ]
            ]
        );
    }

    public function merchantPayNew($type, $order_id, $transfer_scene_id, $openid, $user_name, $transfer_amount, $transfer_remark, $notify_url, $user_recv_perception, $transfer_scene_report_infos)
    {
        return $this->instance->v3pay->setType($type)->transferBills(
            $order_id,
            $transfer_scene_id,
            $openid,
            $user_name,
            $transfer_amount,
            $transfer_remark,
            $notify_url,
            $user_recv_perception,
            $transfer_scene_report_infos
        );
    }

    public function queryTransferBills($order_id)
    {
        return $this->instance->v3pay->queryTransferBills((string)$order_id);
    }

    /**
     * 发起退款
     * @param string $outTradeNo
     * @param array $options
     * @return mixed
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        return $this->instance->v3pay->refund($outTradeNo, $options);
    }

    /**
     * 查询退款
     * @param string $outTradeNo
     * @param string|null $outRequestNo
     * @param array $other
     * @return mixed
     */
    public function queryRefund(string $outTradeNo, string $outRequestNo = null, array $other = [])
    {
        return $this->instance->v3pay->queryRefund($outTradeNo);
    }

    /**
     * @return mixed|\think\Response
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/9/22
     */
    public function handleNotify()
    {
        return $this->instance->v3pay->handleNotify(function ($notify, $successful) {

            if ($successful) {
                $data = [
                    'attach' => $notify->attach,
                    'out_trade_no' => $notify->out_trade_no,
                    'transaction_id' => $notify->transaction_id
                ];

                return Event::until('NotifyListener', [$data, PayServices::WEIXIN_PAY]);
            }

            return false;
        });
    }

    public function handleTransferNotify()
    {
        return $this->instance->v3pay->handleTransferNotify(function ($notify, $successful) {
            if ($successful) {
                $data = [
                    'out_bill_no' => $notify->out_bill_no,
                    'transfer_bill_no' => $notify->transfer_bill_no,
                    'state' => $notify->state,
                    'fail_reason' => $notify->fail_reason ?? ''
                ];
                return Event::until('NotifyListener', [$data, PayServices::WEIXIN_PAY]);
            }

            return false;
        });
    }
}
