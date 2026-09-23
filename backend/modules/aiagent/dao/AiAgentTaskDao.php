<?php
declare(strict_types=1);

namespace modules\aiagent\dao;

use crmeb\basic\BaseDao;
use modules\aiagent\model\AiAgentTask;

/**
 * AI 自动化任务 Dao（eb_ai_agent_task）
 */
class AiAgentTaskDao extends BaseDao
{
    /**
     * 绑定模型
     * @return string
     */
    protected function setModel(): string
    {
        return AiAgentTask::class;
    }

    /**
     * 任务列表（当前管理员，按创建时间倒序）
     * @param int $adminId 管理员 ID
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array{list:array,count:int}
     */
    public function getPageByAdmin(int $adminId, int $page, int $limit): array
    {
        $query = $this->search()->where('admin_id', $adminId);
        $count = (int)(clone $query)->count();
        $list = $query
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 按主键读取任务（可附加管理员归属校验）
     * @param int $id 任务 ID
     * @param int $adminId 管理员 ID（withAdmin 为 true 时参与过滤）
     * @param bool $withAdmin 是否附加归属校验
     * @return array|null
     */
    public function getById(int $id, int $adminId = 0, bool $withAdmin = true): ?array
    {
        $query = $this->search()->where('id', $id);
        if ($withAdmin) {
            $query->where('admin_id', $adminId);
        }
        $task = $query->find();
        return $task ? $task->toArray() : null;
    }

    /**
     * 新增任务
     * @param array $data 任务数据
     * @return int 任务 ID
     */
    public function insertTask(array $data): int
    {
        return (int)$this->insertGetId($data);
    }

    /**
     * 按主键更新任务（返回影响行数，记录不存在时静默为 0）
     * @param int $id 任务 ID
     * @param array $data 更新数据
     * @return int
     */
    public function updateById(int $id, array $data): int
    {
        return (int)$this->search()->where('id', $id)->update($data);
    }

    /**
     * 按主键删除任务
     * @param int $id 任务 ID
     * @return int
     */
    public function deleteById(int $id): int
    {
        return (int)$this->search()->where('id', $id)->delete();
    }

    /**
     * 读取到期待投递的启用任务
     * @param int $now 当前时间戳
     * @param int $limit 单次扫描上限
     * @return array
     */
    public function getDueTasks(int $now, int $limit = 50): array
    {
        return $this->search()
            ->where('status', 1)
            ->where('next_run_time', '>', 0)
            ->where('next_run_time', '<=', $now)
            ->limit($limit)
            ->select()
            ->toArray();
    }
}
