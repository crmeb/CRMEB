<?php
declare(strict_types=1);

namespace app\dao\driver\printer;

use app\model\driver\printer\PrinterTask;
use crmeb\basic\BaseDao;
use think\db\Raw;

/**
 * 小票提交进度持久化，使用数据库唯一键和租约防止并发重复提交。
 */
class PrinterTaskDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return PrinterTask::class;
    }

    /**
     * 批量准备设备任务，重复调度不覆盖已成功的状态
     * @param array $tasks 设备任务
     * @return void
     */
    public function prepareTasks(array $tasks): void
    {
        if ($tasks) {
            $this->getModel()->duplicate(['task_key' => new Raw('task_key')])->insertAll($tasks);
        }
    }

    /**
     * 批量读取状态，并将崩溃后过期的提交租约标记为待确认
     * @param array $keys 任务标识
     * @param int $now 当前时间
     * @return array
     */
    public function getTaskStates(array $keys, int $now): array
    {
        if (!$keys) return [];
        // 无法确认崩溃发生在接单前还是接单后，不能自动重发过期提交。
        $this->getModel()->whereIn('task_key', $keys)->where('is_del', 0)
            ->where('status', 'processing')->where('lease_until', '<=', $now)
            ->update(['status' => 'uncertain', 'last_error' => '提交租约过期，需确认云端接单状态', 'update_time' => $now]);
        return $this->getModel()->whereIn('task_key', $keys)->where('is_del', 0)
            ->column('status', 'task_key');
    }

    /**
     * 原子领取尚未成功且结果明确的设备任务
     * @param string $key 任务标识
     * @param string $token 本次租约标识
     * @param int $now 当前时间
     * @return bool
     */
    public function claimTask(string $key, string $token, int $now): bool
    {
        return $this->getModel()->where('task_key', $key)->where('is_del', 0)
            ->whereIn('status', ['pending', 'failed'])->inc('attempts')
            ->update(['status' => 'processing', 'lease_token' => $token, 'lease_until' => $now + 120,
                'last_error' => '', 'update_time' => $now]) === 1;
    }

    /**
     * 仅允许租约持有者写回提交结果
     * @param string $key 任务标识
     * @param string $token 本次租约标识
     * @param string $status submitted/failed/uncertain
     * @param string $error 已脱敏的失败说明
     * @param string $providerJobId 云端接单号
     * @return void
     */
    public function finishTask(string $key, string $token, string $status, string $error = '', string $providerJobId = ''): void
    {
        $updated = $this->getModel()->where('task_key', $key)->where('is_del', 0)
            ->where('status', 'processing')->where('lease_token', $token)
            ->update(['status' => $status, 'last_error' => $error, 'provider_job_id' => $providerJobId,
                'lease_until' => 0, 'lease_token' => '', 'update_time' => time()]);
        if ($updated !== 1) throw new \RuntimeException('小票提交状态保存失败');
    }
}
