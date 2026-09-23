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

namespace app\listener\pay;


use app\services\pay\PayNotifyServices;
use app\services\pay\PayRecordServices;
use app\services\order\RefundOrderServices;
use app\services\pay\PayTransferNotifyServices;
use crmeb\utils\Hook;

/**
 * 支付异步回调
 * Class NotifyListener
 * @package app\listener\pay
 */
class NotifyListener
{
    /**
     * @param $event
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle($event)
    {
        [$notify, $payType] = $event;

        if (in_array($payType, ['weixin', 'weixin_v3'], true) && ($notify['event_type'] ?? '') === 'REFUND.SUCCESS') {
            $record = app()->make(PayRecordServices::class)->find((string)($notify['out_trade_no'] ?? ''));
            if ($record && ($record['refund']['refund_no'] ?? '') === ($notify['out_refund_no'] ?? '')) {
                if ($payType === 'weixin') {
                    return app()->make(PayRecordServices::class)->handleWechatRefundNotify($notify, $payType);
                }
                app()->make(PayRecordServices::class)->processRefund($record['order_no']);
                return true;
            }
            return app()->make(RefundOrderServices::class)->handleWechatRefundNotify($notify, $payType);
        }

        if (isset($notify['out_bill_no']) && $notify['out_bill_no']) {
            return (new Hook(PayTransferNotifyServices::class, 'wechat'))->listen(
                substr($notify['out_bill_no'], 0, 2),
                $notify['out_bill_no'],
                $notify['transfer_bill_no'],
                $notify['state'],
                $notify['fail_reason'] ?? '',
                $notify
            );
        } else {
            if (isset($notify['attach']) && $notify['attach']) {
                if (($count = strpos($notify['out_trade_no'], '_')) !== false) {
                    $notify['out_trade_no'] = substr($notify['out_trade_no'], $count + 1);
                }
                return (new Hook(PayNotifyServices::class, 'wechat'))->listen(
                    $notify['attach'], $notify['out_trade_no'], $notify['transaction_id'], $payType,
                    ['payment_mode' => $notify['payment_mode'] ?? '', 'openid' => $notify['openid'] ?? '',
                        'amount' => $notify['amount'] ?? [], 'mchid' => $notify['mchid'] ?? '',
                        'sub_mchid' => $notify['sub_mchid'] ?? '', 'appid' => $notify['sub_appid'] ?? $notify['appid'] ?? '']
                );
            }

            if (isset($notify['attach']) && $notify['attach'] === 'wechat' && isset($notify['out_trade_no'])) {
                //通过事件代理发送微信模板消息，避免 app 层直接引用 modules
                event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatMessageServices', 'action' => 'setOnceMessage', 'args' => [$notify, $notify['openid'], 'payment_success', $notify['out_trade_no']]]);
            }
        }

        return false;
    }
}
