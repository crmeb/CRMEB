<?php

namespace app\jobs;

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
        } catch (HttpException $e) {
            // 订单异常处理
            throw new HttpException($e);
        }
    }
}
