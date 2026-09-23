<?php
declare(strict_types=1);

namespace modules\aiagent\dao;

use crmeb\basic\BaseDao;
use modules\aiagent\model\AiAgentConversation;

/**
 * AI Agent 会话 Dao（eb_ai_agent_conversation）
 */
class AiAgentConversationDao extends BaseDao
{
    /**
     * 绑定模型
     * @return string
     */
    protected function setModel(): string
    {
        return AiAgentConversation::class;
    }

    /**
     * 会话列表（置顶优先，组内按更新时间倒序）
     * @param int $adminId 管理员 ID
     * @param int $agentId Agent ID（0 = 该管理员全部会话）
     * @return array
     */
    public function getListByAdmin(int $adminId, int $agentId = 0): array
    {
        $query = $this->search()->where('admin_id', $adminId);
        if ($agentId > 0) {
            $query->where('agent_id', $agentId);
        }
        return $query->order('is_pin', 'desc')->order('update_time', 'desc')->select()->toArray();
    }

    /**
     * 按归属读取会话（管理员必填，Agent 可选）
     * @param int $id 会话 ID
     * @param int $adminId 管理员 ID
     * @param int $agentId Agent ID（大于 0 时附加校验）
     * @return array|null
     */
    public function getById(int $id, int $adminId, int $agentId = 0): ?array
    {
        $query = $this->search()
            ->where('id', $id)
            ->where('admin_id', $adminId);
        if ($agentId > 0) {
            $query->where('agent_id', $agentId);
        }
        $conversation = $query->find();
        return $conversation ? $conversation->toArray() : null;
    }

    /**
     * 新增会话
     * @param array $data 会话数据
     * @return int 会话 ID
     */
    public function insertConversation(array $data): int
    {
        return (int)$this->insertGetId($data);
    }

    /**
     * 按主键更新会话（返回影响行数，记录不存在时静默为 0）
     * @param int $id 会话 ID
     * @param array $data 更新数据
     * @return int
     */
    public function updateById(int $id, array $data): int
    {
        return (int)$this->search()->where('id', $id)->update($data);
    }

    /**
     * 按主键删除会话
     * @param int $id 会话 ID
     * @return int
     */
    public function deleteById(int $id): int
    {
        return (int)$this->search()->where('id', $id)->delete();
    }
}
