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
use crmeb\basic\BaseJobs;

/**
 * 未支付10分钟后发送短信
 * Class UnpaidOrderSend
 * @package crmeb\jobs
 */
class UnpaidOrderSend extends BaseJobs
{

    public function doJob($id)
    {
        /** @var OrderCenterDao $services */
        $services = app()->make(OrderCenterDao::class);
        $orderInfo = $services->get($id);
        if (!$orderInfo) {
            return true;
        }
        // 中台表无 paid 字段，未支付判定用 pay_status
        if ((int)($orderInfo->pay_status ?? 0) !== 0) {
            return true;
        }
        if ((int)($orderInfo->is_del ?? 0) === 1) {
            return true;
        }
        // 监听器按 order_id 取订单号，中台主键字段是 order_no，这里统一映射
        $orderArr = $orderInfo->toArray();
        $orderArr['order_id'] = (string)($orderArr['order_no'] ?? '');
        event('UnpaidOrderNotice', [$orderArr]);
        return true;
    }

}
