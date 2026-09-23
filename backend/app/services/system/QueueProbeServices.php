<?php
namespace app\services\system;

use app\jobs\QueueProbeJob;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use think\facade\Config;
use think\facade\Env;
use think\facade\Log;

/** 创建和查询短期队列诊断；回执过期或丢失只能视为未知。 */
class QueueProbeServices extends BaseService
{
    /**
     * 创建立即任务和固定 60 秒延迟任务。
     * @return array
     */
    public function create(): array
    {
        $connection = Config::get('queue.default', '');
        if (sys_config('queue_open', 0) != 1 || Env::get('cache.driver', 'file') !== 'redis'
            || Config::get('queue.connections.' . $connection . '.type', 'sync') !== 'redis') {
            throw new AdminException('请开启消息队列并将缓存与队列驱动设为 redis');
        }
        $id = bin2hex(random_bytes(16));
        $now = time();
        $record = ['test_id' => $id, 'created_at' => $now, 'expires_at' => $now + 3600,
            'queue' => Config::get('queue.connections.' . $connection . '.queue'), 'tasks' => []];
        // 先验证共享回执存储，再投递，避免存储故障时产生无从查询的任务。
        if (!CacheService::set('system:queue_probe:' . $id, $record, 3600)) {
            throw new \RuntimeException('队列诊断缓存写入失败，请检查 Redis 连接');
        }
        foreach (['immediate' => 0, 'delayed' => 60] as $kind => $delay) {
            $dueAt = $now + $delay;
            try {
                $jobId = QueueProbeJob::dispatchSecs($delay, [$id, $kind, $dueAt]);
                $record['tasks'][$kind] = ['enqueued' => (bool)$jobId, 'due_at' => $dueAt];
            } catch (\Throwable $e) {
                // 不返回连接异常原文，避免配置凭据被带出；另一探针仍可独立测试。
                Log::error('队列诊断投递异常 test_id=' . $id . ' kind=' . $kind . ' type=' . get_class($e));
                $record['tasks'][$kind] = ['enqueued' => false, 'due_at' => $dueAt];
            }
        }
        if (!CacheService::set('system:queue_probe:' . $id, $record, 3600)) {
            throw new \RuntimeException('探针已尝试投递，但诊断记录保存失败');
        }
        return $record;
    }

    /**
     * 查询回执；未收到回执不等于已经确认消费失败。
     * @param string $id 测试标识
     * @return array
     */
    public function read(string $id): array
    {
        if (!preg_match('/\A[a-f0-9]{32}\z/', $id)) {
            throw new AdminException('测试标识格式错误');
        }
        $record = CacheService::get('system:queue_probe:' . $id, null);
        if (!$record) {
            throw new AdminException('测试记录不存在、已过期或缓存已清理，请重新测试');
        }
        foreach ($record['tasks'] as $kind => &$task) {
            $receipt = CacheService::get('system:queue_probe:' . $id . ':' . $kind, null);
            $task['receipt'] = $receipt;
            $task['state'] = $receipt ? ($receipt['early'] ? 'executed_early' : 'executed')
                : (!$task['enqueued'] ? 'enqueue_failed' : (time() < $task['due_at'] ? 'waiting' : 'awaiting_receipt'));
        }
        unset($task);
        $record['checked_at'] = time();
        return $record;
    }
}
