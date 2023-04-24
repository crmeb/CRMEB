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
use crmeb\exceptions\AdminException;
use crmeb\exceptions\PayException;
use crmeb\services\pay\BasePay;
use crmeb\services\pay\PayInterface;
use crmeb\services\pay\extend\allinpay\AllinPay as AllinPayService;
use EasyWeChat\Payment\Order;
use think\facade\Event;

/**
 * 通联支付
 * Class AllinPay
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/2/1
 * @package crmeb\services\pay\storage
 */
class AllinPay extends BasePay implements PayInterface
{

    /**
     * @var AllinPayService
     */
    protected $pay;

    /**
     * @param array $config
     * @return mixed|void
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    protected function initialize(array $config)
    {
        $this->pay = new AllinPayService([
            'appid' => sys_config('allin_appid'),
            'cusid' => sys_config('allin_cusid'),
            'privateKey' => sys_config('allin_private_key'),
            'publicKey' => sys_config('allin_public_key'),
            'notifyUrl' => trim(sys_config('site_url')) . '/api/pay/notify/allin',
            'isBeta' => false,
        ]);
    }

    /**
     * 创建支付
     * @param string $orderId
     * @param string $totalFee
     * @param string $attach
     * @param string $body
     * @param string $detail
     * @param array $options
     * @return array|mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    public function create(string $orderId, string $totalFee, string $attach, string $body, string $detail, array $options = [])
    {
        $this->authSetPayType();

        $options['returl'] = sys_config('site_url') . '/pages/index/index';
        if ($options['returl']) {
            $options['returl'] = str_replace('http://', 'https://', $options['returl']);
        }
        $options['appid'] = sys_config('routine_appId');


        $notifyUrl = trim(sys_config('site_url')) . '/api/pay/notify/allin' . $attach;
        $this->pay->setNotifyUrl($notifyUrl);

        switch ($this->payType) {
            case Order::APP:
                return $this->pay->appPay($totalFee, $orderId, $body, '', '', false, $attach);
            case Order::JSAPI:
                if (request()->isRoutine()) {
                    return $this->pay->miniproPay($totalFee, $orderId, $body, $attach);
                } else {
                    return $this->pay->h5Pay($totalFee, $orderId, $body, $options['returl'] ?? '', $attach);
                }
            case Order::NATIVE:
                return $this->pay->pcPay($totalFee, $orderId, $body, $attach, !empty($options['wechat']));
            default:
                throw new PayException('通联支付:支付类型错误或者暂不支持此环境下支付');
        }
    }

    public function merchantPay(string $openid, string $orderId, string $amount, array $options = [])
    {
        throw new PayException('通联支付:暂不支持商家转账');
    }

    /**
     * 发起退款
     * @param string $outTradeNo
     * @param array $options
     * @return array|mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    public function refund(string $outTradeNo, array $options = [])
    {
        $result = $this->pay->refund($options['refund_price'], $options['order_id'], $outTradeNo);
        if ($result['retcode'] != 'SUCCESS') throw new AdminException($result['retmsg']);
    }

    public function queryRefund(string $outTradeNo, string $outRequestNo, array $other = [])
    {
        // TODO: Implement queryRefund() method.
    }

    /**
     * 异步回调
     * @return mixed|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/1/15
     */
    public function handleNotify(string $attach = '')
    {
        $attach = str_replace('allin', '', $attach);

        return $this->pay->handleNotify(function ($notify) use ($attach) {

            if (isset($notify['cusorderid'])) {

                $data = [
                    'attach' => $attach,
                    'out_trade_no' => $notify['cusorderid'],
                    'transaction_id' => $notify['trxid']
                ];

                return Event::until('NotifyListener', [$data, PayServices::ALLIN_PAY]);
            }
            return false;
        });
    }
}
