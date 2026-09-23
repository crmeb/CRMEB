<?php
namespace app\jobs;

use crmeb\basic\BaseJobs;
use crmeb\services\CacheService;

/** 仅写诊断回执的队列探针，不调用任何订单或通知逻辑。 */
class QueueProbeJob extends BaseJobs
{
    /**
     * 写入消费者回执；写入失败交给队列重试。
     * @param string $id 测试标识
     * @param string $kind immediate 或 delayed
     * @param int $dueAt 预期最早执行时间
     * @return bool
     */
    public function doJob(string $id, string $kind, int $dueAt): bool
    {
        $key = 'system:queue_probe:' . $id . ':' . $kind;
        // 重复消费保留第一次回执，避免覆盖提前执行的证据。
        if (CacheService::get($key, null) !== null) {
            return true;
        }
        $now = time();
        if (!CacheService::set($key, [
            'executed_at' => $now,
            'due_at' => $dueAt,
            'early' => $now < $dueAt,
            'php_version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
        ], 3600)) {
            throw new \RuntimeException('队列诊断回执写入失败');
        }
        return true;
    }
}
