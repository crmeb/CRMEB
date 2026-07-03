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

use app\services\order\StoreOrderServices;
use crmeb\basic\BaseJobs;
use crmeb\services\easywechat\orderShipping\MiniOrderService;
use crmeb\traits\QueueTrait;
use EasyWeChat\Core\Exceptions\HttpException;
use think\Exception;

class MiniOrderJob extends BaseJobs
{
    use QueueTrait;

    /**
     * @throws HttpException
     */
    public function doJob(string $out_trade_no, int $logistics_type, array $shipping_list, string $payer_openid, string $path, int $delivery_mode = 1, bool $is_all_delivered = true)
    {
        try {
            MiniOrderService::shippingByTradeNo($out_trade_no, $logistics_type, $shipping_list, $payer_openid, $path, $delivery_mode, $is_all_delivered);
            return true;
        } catch (HttpException $e) {
            // 订单异常处理
            throw new HttpException($e);
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
            if (sys_config('order_shipping_open')) {
                $time = time();
                $params = [
                    'order_state' => 1,
                    'pay_time_range' => [
                        'begin_time' => $time - (86400 * 7),
                        'end_time' => $time
                    ]
                ];
                $res = MiniOrderService::shippingOrderList($params);
                if ($res['errcode'] == 0 && isset($res['order_list']) && count($res['order_list']) > 0) {
                    foreach ($res['order_list'] as $item) {
                        $shipping_list = [
                            ['item_desc' => '未知']
                        ];
                        $path = '/pages/index/index';
                        if (strpos($item['merchant_trade_no'], 'hy') !== false) {
                            $shipping_list = [
                                ['item_desc' => '用户购买付费会员']
                            ];
                            $path = '/pages/annex/vip_paid/index';
                        }
                        if (strpos($item['merchant_trade_no'], 'cz') !== false) {
                            $shipping_list = [
                                ['item_desc' => '用户充值']
                            ];
                            $path = '/pages/users/user_bill/index?type=2';
                        }
                        if (strpos($item['merchant_trade_no'], 'cp') !== false) {
                            $is_shipping = app()->make(StoreOrderServices::class)->value(['order_id' => $item['merchant_trade_no']], 'status');
                            if ((int)$is_shipping === 0) continue;
                            $shipping_list = [
                                ['item_desc' => '购买商品']
                            ];
                            $path = 'pages/goods/order_details/index?order_id=' . $item['merchant_trade_no'];
                        }
                        MiniOrderService::shippingByTradeNo($item['merchant_trade_no'], 4, $shipping_list, $item['openid'], $path, 1, true);
                    }
                }
            }
            return true;
        } catch (HttpException $e) {
            return true;
        }
    }
}
