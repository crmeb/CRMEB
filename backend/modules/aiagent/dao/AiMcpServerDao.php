<?php
declare(strict_types=1);

namespace modules\aiagent\dao;

use crmeb\basic\BaseDao;
use modules\aiagent\model\AiMcpServer;

/**
 * 远程 MCP Server 登记 Dao（eb_ai_mcp_server）
 */
class AiMcpServerDao extends BaseDao
{
    /**
     * 绑定模型
     * @return string
     */
    protected function setModel(): string
    {
        return AiMcpServer::class;
    }

    /**
     * 全部 Server 列表
     * @param string $order 排序字段
     * @param string $dir 排序方向
     * @return array
     */
    public function getAllList(string $order = 'id', string $dir = 'desc'): array
    {
        return $this->search()->order($order, $dir)->select()->toArray();
    }

    /**
     * 启用中的 Server 列表（按 ID 升序）
     * @return array
     */
    public function getEnabledList(): array
    {
        return $this->search()->where('status', 1)->order('id', 'asc')->select()->toArray();
    }

    /**
     * 按命名空间前缀读取启用中的 Server
     * @param string $serverKey Server 标识
     * @return array|null
     */
    public function getEnabledByKey(string $serverKey): ?array
    {
        $server = $this->search()
            ->where('server_key', $serverKey)
            ->where('status', 1)
            ->find();
        return $server ? $server->toArray() : null;
    }

    /**
     * 按主键读取 Server
     * @param int $id Server ID
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $server = $this->search()->where('id', $id)->find();
        return $server ? $server->toArray() : null;
    }

    /**
     * 统计同标识 Server 数量（编辑时排除自身）
     * @param string $serverKey Server 标识
     * @param int $exceptId 排除的 Server ID
     * @return int
     */
    public function countByKey(string $serverKey, int $exceptId = 0): int
    {
        $query = $this->search()->where('server_key', $serverKey);
        if ($exceptId > 0) {
            $query->where('id', '<>', $exceptId);
        }
        return (int)$query->count();
    }

    /**
     * 新增 Server
     * @param array $data Server 数据
     * @return int Server ID
     */
    public function insertServer(array $data): int
    {
        return (int)$this->insertGetId($data);
    }

    /**
     * 按主键更新 Server（返回影响行数，记录不存在时静默为 0）
     * @param int $id Server ID
     * @param array $data 更新数据
     * @return int
     */
    public function updateById(int $id, array $data): int
    {
        return (int)$this->search()->where('id', $id)->update($data);
    }

    /**
     * 按主键删除 Server
     * @param int $id Server ID
     * @return int
     */
    public function deleteById(int $id): int
    {
        return (int)$this->search()->where('id', $id)->delete();
    }
}
