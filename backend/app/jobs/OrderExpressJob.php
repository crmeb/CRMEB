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

use app\services\order\OrderDeliveryServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;

/**
 * 订单发货消息队列
 * Class OrderExpressJob
 * @package app\jobs
 */
class OrderExpressJob extends BaseJobs
{

    public function doJob($expressInfo)
    {
        $id = $expressInfo['id'];
        $data = [
            'type' => 1,
            'delivery_name' => $expressInfo['delivery_name'],
            'delivery_code' => $expressInfo['delivery_code'],
            'delivery_id' => $expressInfo['delivery_id'],
            'express_record_type' => 1,
            'express_temp_id' => '',
            'to_name' => '',
            'to_tel' => '',
            'to_addr' => '',
            'sh_delivery_name' => '',
            'sh_delivery_id' => '',
            'sh_delivery_uid' => '',
            'fictitious_content' => '',
            'cart_ids' => [],
            'day_type' => 0,
            'pickup_time' => [],
            'service_type' => '',
        ];
        try {
            app()->make(OrderDeliveryServices::class)->splitDelivery($id, $data, false);
        } catch (\Throwable $e) {
            Log::error('订单ID' . $id . '发货失败,失败原因:' . $e->getMessage());
        }
        return true;
    }
}