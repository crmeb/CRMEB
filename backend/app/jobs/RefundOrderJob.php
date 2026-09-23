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

use app\services\order\RefundOrderServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;

/**
 * 售后单消息队列
 * Class RefundOrderJob
 * @package app\jobs
 */
class RefundOrderJob extends BaseJobs
{
    public function process(int $refundOrderId): bool
    {
        /** @var RefundOrderServices $services */
        $services = app()->make(RefundOrderServices::class);
        $services->process($refundOrderId);
        return true;
    }

    public function recover(int $limit = 100): bool
    {
        try {
            /** @var RefundOrderServices $services */
            $services = app()->make(RefundOrderServices::class);
            $services->recover($limit);
        } catch (\Throwable $e) {
            Log::error('[RefundRecovery] 退款恢复任务失败：' . $e->getMessage());
            return false;
        }
        return true;
    }
}
