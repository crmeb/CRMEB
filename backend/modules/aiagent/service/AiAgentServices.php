<?php
declare(strict_types=1);

namespace modules\aiagent\service;

use crmeb\exceptions\AdminException;
use modules\aiagent\dao\AiAgentConversationDao;
use modules\aiagent\dao\AiAgentMessageDao;
use think\facade\Db;

/** AI Agent 会话和消息管理。 */
class AiAgentServices
{
    /** @var AiAgentConversationDao 会话数据 Dao */
    protected AiAgentConversationDao $conversationDao;

    /** @var AiAgentMessageDao 消息数据 Dao */
    protected AiAgentMessageDao $messageDao;

    /**
     * 构造函数（容器注入）
     * @param AiAgentConversationDao $conversationDao
     * @param AiAgentMessageDao $messageDao
     */
    public function __construct(AiAgentConversationDao $conversationDao, AiAgentMessageDao $messageDao)
    {
        $this->conversationDao = $conversationDao;
        $this->messageDao = $messageDao;
    }

    /**
     * 会话列表（置顶优先，组内按更新时间倒序）
     * @param int $adminId 管理员 ID
     * @param int $agentId Agent ID（0 = 该管理员全部会话）
     * @return array
     */
    public function conversations(int $adminId, int $agentId = 0): array
    {
        return $this->conversationDao->getListByAdmin($adminId, $agentId);
    }

    /**
     * 修改会话（标题/置顶，title 与 is_pin 至少生效一项）
     * @param int $conversationId 会话 ID
     * @param int $adminId 管理员 ID
     * @param string $title 新标题（空串表示不修改）
     * @param int $isPin 置顶状态：-1 不修改，0 取消置顶，1 置顶
     */
    public function updateConversation(int $conversationId, int $adminId, string $title, int $isPin = -1): void
    {
        $this->assertConversation($conversationId, $adminId);
        $data = [];
        if ($title !== '') {
            $data['title'] = mb_substr($title, 0, 40);
        }
        if (in_array($isPin, [0, 1], true)) {
            $data['is_pin'] = $isPin;
        }
        if (!$data) {
            throw new AdminException('请指定要修改的内容');
        }
        $data['update_time'] = time();
        $this->conversationDao->updateById($conversationId, $data);
    }

    /**
     * 会话消息明细（tool_payload 解码为数组）
     * @param int $conversationId 会话 ID
     * @param int $adminId 管理员 ID
     * @return array
     */
    public function messages(int $conversationId, int $adminId): array
    {
        $this->assertConversation($conversationId, $adminId);
        $list = $this->messageDao->getListByConversation($conversationId);
        foreach ($list as &$message) {
            $message['tool_payload'] = $this->decodeArray($message['tool_payload'] ?? '');
        }
        return $list;
    }

    /**
     * 删除会话及其全部消息（事务）
     * @param int $conversationId 会话 ID
     * @param int $adminId 管理员 ID
     */
    public function deleteConversation(int $conversationId, int $adminId): void
    {
        $this->assertConversation($conversationId, $adminId);
        Db::transaction(function () use ($conversationId) {
            $this->messageDao->deleteByConversationId($conversationId);
            $this->conversationDao->deleteById($conversationId);
        });
    }

    /**
     * 校验会话归属（必须属于当前管理员，可附加 Agent 归属校验）
     * @param int $conversationId 会话 ID
     * @param int $adminId 管理员 ID
     * @param int $agentId Agent ID（大于 0 时附加校验）
     * @return array 会话记录
     * @throws AdminException 会话不存在或无权访问时抛出
     */
    public function assertConversation(int $conversationId, int $adminId, int $agentId = 0): array
    {
        $conversation = $this->conversationDao->getById($conversationId, $adminId, $agentId);
        if (!$conversation) {
            throw new AdminException('会话不存在或无权访问');
        }
        return $conversation;
    }

    /**
     * 创建会话（标题取首条用户消息前 40 字）
     * @param int $agentId Agent ID
     * @param int $adminId 管理员 ID
     * @param string $message 首条用户消息（用于生成标题）
     * @return int 新会话 ID
     */
    public function createConversation(int $agentId, int $adminId, string $message): int
    {
        return $this->conversationDao->insertConversation([
            'agent_id' => $agentId,
            'admin_id' => $adminId,
            'title' => mb_substr(trim($message), 0, 40) ?: '新会话',
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }

    /**
     * 追加一条会话消息并刷新会话更新时间
     * @param int $conversationId 会话 ID
     * @param string $role 角色（user/assistant/tool/confirm）
     * @param string $content 消息内容
     * @param string $toolName 工具名（tool 角色消息使用）
     * @param array $toolPayload 工具载荷（JSON 存储）
     * @return int 新消息 ID
     */
    public function addMessage(int $conversationId, string $role, string $content, string $toolName = '', array $toolPayload = []): int
    {
        $this->conversationDao->updateById($conversationId, ['update_time' => time()]);
        return $this->messageDao->insertMessage([
            'conversation_id' => $conversationId,
            'role' => $role,
            'content' => $content,
            'tool_name' => $toolName,
            'tool_payload' => $toolPayload ? json_encode($toolPayload, JSON_UNESCAPED_UNICODE) : '',
            'create_time' => time(),
        ]);
    }

    /**
     * 取会话最近 N 条消息（按消息 ID 升序返回）
     * @param int $conversationId 会话 ID
     * @param int $limit 最大条数
     * @return array
     */
    public function recentMessages(int $conversationId, int $limit = 20): array
    {
        return $this->messageDao->getRecentByConversation($conversationId, $limit);
    }

    /**
     * 最近一条用户消息（供长工具链恢复原问题）
     * @param int $conversationId 会话 ID
     * @return array|null
     */
    public function latestUserMessage(int $conversationId): ?array
    {
        return $this->messageDao->getLatestUserByConversation($conversationId);
    }

    /**
     * 取待确认消息：校验归属、角色与待确认状态（状态机 pending → approved/rejected/expired）
     * @param int $conversationId 会话 ID
     * @param int $messageId confirm 消息 ID
     * @param int $adminId 管理员 ID
     * @return array confirm 消息（tool_payload 已解码，status 必为 pending）
     */
    public function getPendingConfirm(int $conversationId, int $messageId, int $adminId): array
    {
        $this->assertConversation($conversationId, $adminId);
        $message = $this->messageDao->getConfirmById($messageId, $conversationId);
        if (!$message) {
            throw new AdminException('待确认操作不存在');
        }
        $message['tool_payload'] = $this->decodeArray($message['tool_payload'] ?? '');
        if (($message['tool_payload']['status'] ?? '') !== 'pending') {
            throw new AdminException('该操作已处理或已过期，请勿重复操作');
        }
        return $message;
    }

    /**
     * 更新待确认消息状态（仅允许从 pending 迁移，防重放）
     * @param int $messageId confirm 消息 ID
     * @param string $status 目标状态：approved/rejected/expired
     */
    public function updateConfirmStatus(int $messageId, string $status): void
    {
        $message = $this->messageDao->getConfirmById($messageId);
        if (!$message) {
            throw new AdminException('待确认操作不存在');
        }
        $payload = $this->decodeArray($message['tool_payload'] ?? '');
        if (($payload['status'] ?? '') !== 'pending') {
            throw new AdminException('该操作已处理或已过期，请勿重复操作');
        }
        $payload['status'] = $status;
        if (!$this->messageDao->transitionConfirmPayload(
            $messageId,
            (string)$message['tool_payload'],
            (string)json_encode($payload, JSON_UNESCAPED_UNICODE)
        )) {
            throw new AdminException('该操作已处理或已过期，请勿重复操作');
        }
    }

    /**
     * 使会话内全部待确认操作过期（新用户消息到达时调用，防止旧确认卡与新上下文错位）
     * @param int $conversationId 会话 ID
     */
    public function expirePendingConfirms(int $conversationId): void
    {
        $pendings = $this->messageDao->getConfirmList($conversationId);
        foreach ($pendings as $message) {
            $payload = $this->decodeArray($message['tool_payload'] ?? '');
            if (($payload['status'] ?? '') === 'pending') {
                $payload['status'] = 'expired';
                $this->messageDao->updateById((int)$message['id'], [
                    'tool_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    /**
     * JSON 字符串/数组安全解码为数组
     * @param mixed $value JSON 字符串或已是数组
     * @return array
     */
    private function decodeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string)$value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
