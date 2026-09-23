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
namespace app\jobs;

use app\dao\order\OrderCenterDao;
use app\services\pay\PayRecordServices;
use crmeb\basic\BaseJobs;
use crmeb\services\easywechat\orderShipping\MiniOrderService;
use crmeb\services\pay\PayChannelConfig;
use EasyWeChat\Kernel\Exceptions\HttpException;
use think\Exception;

class MiniOrderJob extends BaseJobs
{

    /**
     * @throws HttpException
     */
    public function doJob(string $out_trade_no, int $logistics_type, array $shipping_list, string $payer_openid, string $path, int $delivery_mode = 1, bool $is_all_delivered = true, array $context = [])
    {
        try {
            $context = $context ?: (app()->make(PayRecordServices::class)->find($out_trade_no)['context'] ?? []);
            if ($context && ($context['platform'] ?? '') !== 'wechat_mini') return true;
            $payer_openid = $context['openid'] ?? $payer_openid;
            MiniOrderService::shippingByTradeNo($out_trade_no, $logistics_type, $shipping_list, $payer_openid, $path, $delivery_mode, $is_all_delivered, $context);
            return true;
        } catch (HttpException $e) {
            // 订单异常处理
            throw $e;
        }
    }

    /**
     * 同步商城发货但是小程序未发货的订单
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/8/14
     */
    public function syncOrderShipping()
    {
        try {
            if (!sys_config('order_shipping_open')) return true;
            $records = app()->make(PayRecordServices::class);
            foreach (['weixin', 'weixin_v3'] as $channel) {
                $config = PayChannelConfig::get($channel);
                if (empty($config['routine_appId']) || empty($config['routine_appsecret'])) continue;
                $context = ['channel_key' => $channel];
                $params = ['order_state' => 1, 'page_size' => 100,
                    'pay_time_range' => ['begin_time' => time() - 86400 * 7, 'end_time' => time()]];
                for ($page = 0; $page < 10; ++$page) {
                    $res = MiniOrderService::shippingOrderList($params, $context);
                    foreach ($res['order_list'] ?? [] as $item) {
                        $orderNo = (string)($item['merchant_trade_no'] ?? '');
                        $record = $records->find($orderNo);
                        // 历史订单缺少合同不能靠 hy/cp 前缀猜业务，更不能伪报自提。
                        if (!$record || $record['channel_key'] !== $channel || ($record['context']['platform'] ?? '') !== 'wechat_mini') continue;
                        $kind = $record['business_type'];
                        if (!in_array($kind, ['product', 'member', 'user_recharge', 'offline_scan'], true)) continue;
                        $order = $records->source($kind, $orderNo);
                        if ((int)($order[$kind === 'product' ? 'pay_status' : 'paid'] ?? 0) !== 1) continue;
                        $order['order_id'] = $orderNo;
                        $order['payment_context'] = $record['context'];
                        $order['pay_type'] = $channel;
                        $deliveryType = 3;
                        $delivery = [];
                        if ($kind === 'product') {
                            if (empty($order['delivery_time']) && empty($order['take_time'])) continue;
                            $extra = $order['extra_info'] ?? [];
                            if (is_string($extra)) $extra = json_decode($extra, true) ?: [];
                            $delivery = $extra['delivery'] ?? [];
                            $deliveryType = ['express' => 1, 'send' => 2, 'fictitious' => 3][$delivery['delivery_type'] ?? ''] ?? 0;
                            if (($order['shipping_type'] ?? 0) == 2) $deliveryType = 4;
                            if (!$deliveryType) continue;
                            $order['user_phone'] = $extra['contact']['phone'] ?? '';
                        }
                        \crmeb\services\pay\PaymentDispatch::replay(fn () => event('OrderShipping', [
                            $kind === 'user_recharge' ? 'recharge' : $kind, $order, $deliveryType,
                            $delivery['delivery_id'] ?? '', $delivery['delivery_name'] ?? '',
                        ]));
                    }
                    if (empty($res['has_more']) || empty($res['last_index'])) break;
                    $params['last_index'] = $res['last_index'];
                }
            }
            return true;
        } catch (\Throwable $e) {
            \think\facade\Log::error('[MiniOrder] 发货同步待重试 type=' . get_class($e));
            return false;
        }
    }
}
