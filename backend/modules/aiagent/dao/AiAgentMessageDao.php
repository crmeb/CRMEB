<?php
declare(strict_types=1);

namespace modules\aiagent\dao;

use crmeb\basic\BaseDao;
use modules\aiagent\model\AiAgentMessage;

/**
 * AI Agent 会话消息 Dao（eb_ai_agent_message）
 */
class AiAgentMessageDao extends BaseDao
{
    /**
     * 绑定模型
     * @return string
     */
    protected function setModel(): string
    {
        return AiAgentMessage::class;
    }

    /**
     * 会话全部消息（按消息 ID 升序）
     * @param int $conversationId 会话 ID
     * @return array
     */
    public function getListByConversation(int $conversationId): array
    {
        return $this->search()
            ->where('conversation_id', $conversationId)
            ->order('id', 'asc')
            ->select()
            ->toArray();
    }

    /**
     * 会话最近 N 条消息（按消息 ID 升序返回）
     * @param int $conversationId 会话 ID
     * @param int $limit 最大条数
     * @return array
     */
    public function getRecentByConversation(int $conversationId, int $limit): array
    {
        return array_reverse($this->search()
            ->where('conversation_id', $conversationId)
            ->order('id', 'desc')
            ->limit($limit)
            ->select()
            ->toArray());
    }

    /**
     * 最近一条用户消息（长工具链中原问题可能不在最近 N 条消息窗口内）
     * @param int $conversationId 会话 ID
     * @return array|null
     */
    public function getLatestUserByConversation(int $conversationId): ?array
    {
        $message = $this->search()
            ->where('conversation_id', $conversationId)
            ->where('role', 'user')
            ->order('id', 'desc')
            ->find();
        return $message ? $message->toArray() : null;
    }

    /**
     * 按 ID 读取 confirm 角色消息（conversationId 大于 0 时附加会话归属校验）
     * @param int $messageId 消息 ID
     * @param int $conversationId 会话 ID
     * @return array|null
     */
    public function getConfirmById(int $messageId, int $conversationId = 0): ?array
    {
        $query = $this->search()
            ->where('id', $messageId)
            ->where('role', 'confirm');
        if ($conversationId > 0) {
            $query->where('conversation_id', $conversationId);
        }
        $message = $query->find();
        return $message ? $message->toArray() : null;
    }

    /**
     * 会话内最近 N 条 confirm 角色消息（按消息 ID 倒序）
     * @param int $conversationId 会话 ID
     * @param int $limit 最大条数
     * @return array
     */
    public function getConfirmList(int $conversationId, int $limit = 10): array
    {
        return $this->search()
            ->where('conversation_id', $conversationId)
            ->where('role', 'confirm')
            ->order('id', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    /**
     * 新增消息
     * @param array $data 消息数据
     * @return int 消息 ID
     */
    public function insertMessage(array $data): int
    {
        return (int)$this->insertGetId($data);
    }

    /**
     * 按主键更新消息（返回影响行数，记录不存在时静默为 0）
     * @param int $id 消息 ID
     * @param array $data 更新数据
     * @return int
     */
    public function updateById(int $id, array $data): int
    {
        return (int)$this->search()->where('id', $id)->update($data);
    }

    /**
     * 条件迁移确认卡内容；并发批准只有一次能匹配原始快照。
     * @param int $id 消息 ID
     * @param string $original 原始 JSON 文本
     * @param string $updated 新 JSON 文本
     * @return bool 是否完成迁移
     */
    public function transitionConfirmPayload(int $id, string $original, string $updated): bool
    {
        return (int)$this->search()->where('id', $id)->where('role', 'confirm')
            ->where('tool_payload', $original)->update(['tool_payload' => $updated]) === 1;
    }

    /**
     * 按会话删除全部消息
     * @param int $conversationId 会话 ID
     * @return int
     */
    public function deleteByConversationId(int $conversationId): int
    {
        return (int)$this->search()->where('conversation_id', $conversationId)->delete();
    }
}
