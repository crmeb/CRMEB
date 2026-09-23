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
declare(strict_types=1);

namespace modules\service\mcp\service;

use app\services\system\config\SystemConfigServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use modules\service\mcp\dao\McpAccountDao;
use modules\service\mcp\McpToolRegistry;

/**
 * MCP 服务账号管理服务
 *
 * 账号是 MCP 接口唯一的认证权威：每个账号持有独立访问密钥，一律按工具
 * 白名单访问（tools/list 过滤、tools/call 拦截未勾选工具）。系统账号
 * （is_system=1，默认保留一条供 AI Agent 内置工具源调用，安装时初始授予
 * 全部工具）同样受白名单约束、可编辑权限与停用（停用即下线 AI Agent
 * 内置工具源），但不支持删除；其密钥
 * 镜像到系统配置 mcp_appkey 供 AI Agent 读取（modules 层禁止互引）。
 */
class McpAccountServices extends BaseService
{
    /**
     * MCP 服务账号 DAO
     * @var McpAccountDao
     */
    protected $dao;

    /**
     * 构造函数
     * @param McpAccountDao $dao MCP 服务账号 DAO
     */
    public function __construct(McpAccountDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 新增账号（自动生成 32 位访问密钥，默认启用）
     *
     * @param string $remark 备注（账号用途说明）
     * @param array $tools 授权工具名列表
     * @return int 新账号主键
     */
    public function createAccount(string $remark, array $tools): int
    {
        $data = $this->buildAccountData($remark, $tools);
        $data['app_key'] = $this->generateKey();
        $data['status'] = 1;
        $data['add_time'] = time();
        return (int)$this->dao->insertGetId($data);
    }

    /**
     * 编辑账号（备注与授权工具，访问密钥保持不变；系统账号同样可编辑权限，
     * 收紧其白名单即收紧 AI Agent 内置工具源可见的工具范围）
     *
     * @param int $id 账号主键
     * @param string $remark 备注（账号用途说明）
     * @param array $tools 授权工具名列表
     * @return void
     */
    public function updateAccount(int $id, string $remark, array $tools): void
    {
        $this->getExistAccount($id);
        $this->dao->update($id, $this->buildAccountData($remark, $tools));
    }

    /**
     * 删除账号（软删除，删除后密钥立即失效；系统账号为 AI Agent 保留，不支持删除）
     *
     * @param int $id 账号主键
     * @return void
     */
    public function removeAccount(int $id): void
    {
        $account = $this->getExistAccount($id);
        if ($this->isSystem($account)) {
            throw new AdminException('系统账号为 AI Agent 内置工具源保留，不支持删除');
        }
        $this->dao->update($id, ['is_del' => 1]);
    }

    /**
     * 重置账号访问密钥（旧密钥立即失效）
     *
     * 系统账号重置后同步刷新系统配置 mcp_appkey 镜像，AI Agent 下次读取即生效
     * （密钥轮换零维护；配置行缺失时仅轮换账号密钥，重新启用模块可补齐镜像）。
     *
     * @param int $id 账号主键
     * @return void
     */
    public function resetKey(int $id): void
    {
        $account = $this->getExistAccount($id);
        $appKey = $this->generateKey();
        $this->dao->update($id, ['app_key' => $appKey]);
        if ($this->isSystem($account)) {
            app()->make(SystemConfigServices::class)->setConfig('mcp_appkey', $appKey);
        }
    }

    /**
     * 切换账号状态（停用后密钥立即失效，启用即时恢复，切换无缓存实时生效）
     *
     * 所有账号（含系统账号）均可停用：系统账号停用后 AI Agent 内置工具源
     * 的调用会因密钥失效不可用；如需整体关闭服务，请在
     * 「系统配置 → MCP服务」中切换 MCP 服务总开关。
     *
     * @param int $id 账号主键
     * @param int $status 目标状态：0停用 1启用
     * @return void
     */
    public function setStatus(int $id, int $status): void
    {
        if (!in_array($status, [0, 1], true)) {
            throw new AdminException('无效的状态值');
        }
        $this->getExistAccount($id);
        $this->dao->update($id, ['status' => $status]);
    }

    /**
     * 按访问密钥获取有效账号（MCP 接口认证用）
     *
     * @param string $appKey 访问密钥
     * @return array|null 账号数组，无效返回 null
     */
    public function getByKey(string $appKey): ?array
    {
        return $this->dao->getActiveByKey($appKey);
    }

    /**
     * 组装并校验账号数据（备注 + 白名单过滤到当前已注册工具）
     *
     * @param string $remark 备注
     * @param array $tools 授权工具名列表
     * @return array {remark: string, tools: array}
     */
    private function buildAccountData(string $remark, array $tools): array
    {
        $remark = trim($remark);
        if ($remark === '') {
            throw new AdminException('请输入备注');
        }
        if (mb_strlen($remark) > 100) {
            throw new AdminException('备注最长 100 字');
        }
        // 白名单只保留当前已注册的工具名，避免插件卸载后的残留名称继续入库
        $tools = array_values(array_intersect($this->registeredToolNames(), array_values($tools)));
        if (!$tools) {
            throw new AdminException('请至少勾选一个工具');
        }
        return ['remark' => $remark, 'tools' => $tools];
    }

    /**
     * 获取账号（不存在或已删除时抛出异常）
     *
     * @param int $id 账号主键
     * @return array 账号数组
     */
    private function getExistAccount(int $id): array
    {
        $account = $this->dao->get($id, ['id', 'remark', 'app_key', 'is_system', 'tools', 'status', 'is_del', 'add_time']);
        if (!$account || (int)$account['is_del'] === 1) {
            throw new AdminException('账号不存在或已删除');
        }
        return $account->toArray();
    }

    /**
     * 是否系统账号（AI Agent 工具源保留账号，密钥镜像至 mcp_appkey）
     *
     * @param array $account 账号数组
     * @return bool
     */
    private function isSystem(array $account): bool
    {
        return (int)($account['is_system'] ?? 0) === 1;
    }

    /**
     * 当前已注册的全部工具名（内置 + 插件经 McpToolsRegister 事件追加）
     *
     * @return array 工具名列表
     */
    private function registeredToolNames(): array
    {
        return array_column(McpToolRegistry::booted()->allDefinitions(), 'name');
    }

    /**
     * 生成 32 位随机密钥（与 mcp_appkey 镜像的生成规则一致）
     *
     * @return string
     */
    private function generateKey(): string
    {
        return bin2hex(random_bytes(16));
    }
}
