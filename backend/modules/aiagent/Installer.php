<?php
declare(strict_types=1);

namespace modules\aiagent;

use crmeb\module\BaseModuleInstaller;
use think\facade\Db;

/** AI Agent 扩展安装器 */
class Installer extends BaseModuleInstaller
{
    /**
     * 一级分组菜单名称（子菜单 pid 以 '@AI' 引用该菜单）
     */
    private const ROOT_MENU = 'AI';

    /** 模块唯一标识（覆写父类） */
    public function getKey(): string
    {
        return 'aiagent';
    }

    /** 模块显示名称（覆写父类） */
    public function getName(): string
    {
        return 'AI Agent';
    }

    /** 模块类型（覆写父类） */
    public function getType(): string
    {
        return 'service';
    }

    /** 模块版本（覆写父类默认值，与 plugin.json 双源保持一致） */
    protected function getVersion(): string
    {
        return '1.0.1';
    }

    /** 安装/升级迁移：创建会话、消息与 MCP Server 注册表（幂等），并为存量安装补 ai_agent_conversation.is_pin 列 */
    protected function runMigrate(): void
    {
        $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('ai_agent_conversation')}` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '会话 ID',
            `agent_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'Agent ID',
            `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员 ID',
            `title` varchar(120) NOT NULL DEFAULT '' COMMENT '会话标题',
            `is_pin` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶：0否 1是',
            `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `update_time` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
            PRIMARY KEY (`id`),
            KEY `idx_agent_admin` (`agent_id`,`admin_id`),
            KEY `idx_update_time` (`update_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='AI Agent 会话表';");

        // 存量安装升级：会话表已存在但尚未包含置顶字段时补列；新安装已由上面的建表语句直接创建。
        if (!$this->hasColumn('ai_agent_conversation', 'is_pin')) {
            $this->exec("ALTER TABLE `{$this->table('ai_agent_conversation')}` ADD COLUMN `is_pin` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶：0否 1是' AFTER `title`");
        }

        $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('ai_agent_message')}` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '消息 ID',
            `conversation_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '会话 ID',
            `role` varchar(20) NOT NULL DEFAULT '' COMMENT '角色：user/assistant/tool',
            `content` longtext NULL COMMENT '消息内容',
            `tool_name` varchar(100) NOT NULL DEFAULT '' COMMENT '工具名称',
            `tool_payload` longtext NULL COMMENT '工具参数或结果 JSON',
            `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            PRIMARY KEY (`id`),
            KEY `idx_conversation` (`conversation_id`,`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='AI Agent 消息表';");

        $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('ai_agent_task')}` (
            `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '任务 ID',
            `name` varchar(100) NOT NULL DEFAULT '' COMMENT '任务名称',
            `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '创建管理员 ID',
            `instruction` text NULL COMMENT '任务指令（无人值守时发给模型的完整执行指令）',
            `model_id` int unsigned NOT NULL DEFAULT '0' COMMENT '模型 ID（0=系统默认）',
            `server_ids` varchar(500) NOT NULL DEFAULT '' COMMENT '连接器 ID 列表 JSON（空=全部可用）',
            `skill_keys` varchar(500) NOT NULL DEFAULT '' COMMENT '挂载 Skill 标识 JSON',
            `autonomy` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '自主级别：1=只读 2=需审批（暂按只读执行） 3=全自动',
            `schedule_type` tinyint unsigned NOT NULL DEFAULT '2' COMMENT '调度类型：1=每隔N分钟 2=每天HH:MM 3=每周周几|HH:MM 4=每月几日|HH:MM',
            `schedule_value` varchar(100) NOT NULL DEFAULT '' COMMENT '调度值（分钟数 / HH:MM / 周几|HH:MM / 几日|HH:MM）',
            `max_steps` int unsigned NOT NULL DEFAULT '10' COMMENT '单次运行最大工具调用轮数',
            `timeout` int unsigned NOT NULL DEFAULT '300' COMMENT '单次运行软超时（秒）',
            `notify_channels` varchar(200) NOT NULL DEFAULT '' COMMENT '完成通知渠道 JSON：is_email/is_ent_wechat',
            `notify_email` varchar(200) NOT NULL DEFAULT '' COMMENT '通知收件邮箱（is_email 渠道必填）',
            `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态：0停用 1启用',
            `last_run_time` int unsigned NOT NULL DEFAULT '0' COMMENT '上次运行时间',
            `next_run_time` int unsigned NOT NULL DEFAULT '0' COMMENT '下次运行时间',
            `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `update_time` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
            PRIMARY KEY (`id`),
            KEY `idx_status_next` (`status`,`next_run_time`),
            KEY `idx_admin` (`admin_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='AI Agent 自动化任务表';");

        $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('ai_agent_task_run')}` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '运行 ID',
            `task_id` int unsigned NOT NULL DEFAULT '0' COMMENT '任务 ID',
            `conversation_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '关联会话 ID（全过程审计回放）',
            `status` varchar(20) NOT NULL DEFAULT 'running' COMMENT '状态：running/success/failed/timeout',
            `trigger_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '触发方式：1=调度 2=手动立即执行',
            `trigger_time` int unsigned NOT NULL DEFAULT '0' COMMENT '调度触发时间点',
            `confirm_message_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '关联确认卡消息 ID（对话创建任务场景）',
            `summary` text NULL COMMENT '运行结果摘要（最终答复）',
            `error` varchar(1000) NOT NULL DEFAULT '' COMMENT '失败原因或兜底说明',
            `tool_calls` int unsigned NOT NULL DEFAULT '0' COMMENT '工具调用次数',
            `usage` varchar(1000) NOT NULL DEFAULT '' COMMENT 'token 用量 JSON',
            `started_at` int unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
            `finished_at` int unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
            PRIMARY KEY (`id`),
            KEY `idx_task` (`task_id`,`id`),
            KEY `idx_status` (`status`),
            UNIQUE KEY `uk_task_trigger` (`task_id`,`trigger_type`,`trigger_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='AI Agent 任务运行记录表';");

        $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('ai_mcp_server')}` (
            `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'MCP Server ID',
            `name` varchar(100) NOT NULL DEFAULT '' COMMENT '服务显示名称',
            `server_key` varchar(64) NOT NULL DEFAULT '' COMMENT '服务标识（工具命名空间前缀）',
            `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'MCP Server 地址（Streamable HTTP）',
            `api_key` varchar(255) NOT NULL DEFAULT '' COMMENT '访问密钥（X-Mcp-Key 请求头）',
            `headers` text NULL COMMENT '附加请求头 JSON',
            `timeout` int unsigned NOT NULL DEFAULT '15' COMMENT '请求超时（秒）',
            `tools` text NULL COMMENT '最近一次同步的工具清单 JSON',
            `tools_sync_time` int unsigned NOT NULL DEFAULT '0' COMMENT '工具同步时间',
            `description` varchar(500) NOT NULL DEFAULT '' COMMENT '备注说明',
            `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态：0停用，1启用',
            `create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `update_time` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_server_key` (`server_key`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='远程 MCP Server 注册表';");
    }

    /** 卸载回滚：删除本模块创建的数据表 */
    protected function rollbackMigrate(): void
    {
        $this->exec("DROP TABLE IF EXISTS `{$this->table('ai_agent_task_run')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('ai_agent_task')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('ai_mcp_server')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('ai_agent_message')}`;");
        $this->exec("DROP TABLE IF EXISTS `{$this->table('ai_agent_conversation')}`;");
    }

    /** 菜单配置：一级「AI」对话入口 + 对话/MCP Server/Skill 的权限项（AI 模型管理已内置为系统级菜单，见安装 SQL） */
    protected function getMenuConfig(): array
    {
        return [
            // 一级菜单：点击「AI」直接进入 AI 对话页（模型/MCP/Skill 管理在对话页内以遮盖层打开）
            [
                'pid'         => 0,
                'menu_name'   => self::ROOT_MENU,
                'module'      => 'admin',
                'controller'  => 'aiagent',
                'action'      => 'index',
                'icon'        => 'CRMEB-663-AI-mianxing',
                'sort'        => 5,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'unique_auth' => 'aiagent-manage',
                'menu_path'   => '/aiagent/index',
                'mark'        => 'AI 对话入口',
            ],
            // ===== 二级分组容器：仅在菜单管理中归类接口权限节点（auth_type=1 且 is_show_path=0，不进侧边栏与授权树） =====
            [
                'pid'          => '@AI',
                'key'          => 'group-chat',
                'menu_name'    => '对话管理',
                'icon'         => 'CRMEB-244-xiaoxi-xianxing',
                'auth_type'    => 1,
                'is_show'      => 1,
                'is_show_path' => 0,
                'sort'         => 6,
            ],
            [
                'pid'          => '@AI',
                'key'          => 'group-mcp',
                'menu_name'    => 'MCP Server 管理',
                'icon'         => 'CRMEB-284-tongyongzujian-xianxing',
                'auth_type'    => 1,
                'is_show'      => 1,
                'is_show_path' => 0,
                'sort'         => 4,
            ],
            [
                'pid'          => '@AI',
                'key'          => 'group-skill',
                'menu_name'    => 'Skill 管理',
                'icon'         => 'CRMEB-220-kaifashezhi-xianxing',
                'auth_type'    => 1,
                'is_show'      => 1,
                'is_show_path' => 0,
                'sort'         => 3,
            ],
            [
                'pid'          => '@AI',
                'key'          => 'group-task',
                'menu_name'    => '自动化任务',
                'icon'         => 'CRMEB-254-zidonghuayunying-xianxing',
                'auth_type'    => 1,
                'is_show'      => 1,
                'is_show_path' => 0,
                'sort'         => 2,
            ],
            [
                'pid'        => '@对话管理',
                'menu_name'  => 'AI 对话配置选项',
                'controller' => 'aiagent',
                'action'     => 'options',
                'api_url'    => 'aiagent/options',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@对话管理',
                'menu_name'  => 'AI 对话会话列表',
                'controller' => 'aiagent',
                'action'     => 'conversations',
                'api_url'    => 'aiagent/conversations',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@对话管理',
                'menu_name'  => 'AI 对话消息记录',
                'controller' => 'aiagent',
                'action'     => 'messages',
                'api_url'    => 'aiagent/messages/<conversation_id>',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@对话管理',
                'menu_name'  => '删除 AI 对话会话',
                'controller' => 'aiagent',
                'action'     => 'deleteConversation',
                'api_url'    => 'aiagent/conversation/<id>',
                'methods'    => 'DELETE',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@对话管理',
                'menu_name'  => '修改 AI 对话会话',
                'controller' => 'aiagent',
                'action'     => 'updateConversation',
                'api_url'    => 'aiagent/conversation/<id>',
                'methods'    => 'PUT',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@对话管理',
                'menu_name'  => 'AI 对话发送消息',
                'controller' => 'aiagent',
                'action'     => 'chat',
                'api_url'    => 'aiagent/chat',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@对话管理',
                'menu_name'  => 'AI 对话流式输出',
                'controller' => 'aiagent',
                'action'     => 'chatStream',
                'api_url'    => 'aiagent/chat_stream',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@对话管理',
                'menu_name'  => 'AI 对话确认操作处理',
                'controller' => 'aiagent',
                'action'     => 'confirmStream',
                'api_url'    => 'aiagent/confirm_stream',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            // ===== 远程 MCP Server =====
            [
                'pid'        => '@MCP Server 管理',
                'menu_name'  => 'MCP Server 列表',
                'controller' => 'aiagent',
                'action'     => 'mcp_servers',
                'api_url'    => 'aiagent/mcp_servers',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@MCP Server 管理',
                'menu_name'  => '创建 MCP Server',
                'controller' => 'aiagent',
                'action'     => 'mcp_server_save',
                'api_url'    => 'aiagent/mcp_server',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@MCP Server 管理',
                'menu_name'  => 'MCP Server 详情',
                'controller' => 'aiagent',
                'action'     => 'mcp_server_read',
                'api_url'    => 'aiagent/mcp_server/<id>',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@MCP Server 管理',
                'menu_name'  => '编辑 MCP Server',
                'controller' => 'aiagent',
                'action'     => 'mcp_server_update',
                'api_url'    => 'aiagent/mcp_server/<id>',
                'methods'    => 'PUT',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@MCP Server 管理',
                'menu_name'  => '删除 MCP Server',
                'controller' => 'aiagent',
                'action'     => 'mcp_server_delete',
                'api_url'    => 'aiagent/mcp_server/<id>',
                'methods'    => 'DELETE',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@MCP Server 管理',
                'menu_name'  => '修改 MCP Server 状态',
                'controller' => 'aiagent',
                'action'     => 'mcp_server_status',
                'api_url'    => 'aiagent/mcp_server/<id>/status',
                'methods'    => 'PUT',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@MCP Server 管理',
                'menu_name'  => '同步 MCP Server 工具',
                'controller' => 'aiagent',
                'action'     => 'mcp_server_sync',
                'api_url'    => 'aiagent/mcp_server/<id>/sync',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@MCP Server 管理',
                'menu_name'  => '测试 MCP Server 连接',
                'controller' => 'aiagent',
                'action'     => 'mcp_server_test',
                'api_url'    => 'aiagent/mcp_server/<id>/test',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            // ===== Skill 管理 =====
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => 'Skill 列表',
                'controller' => 'aiagent',
                'action'     => 'skills',
                'api_url'    => 'aiagent/skills',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => '创建 Skill',
                'controller' => 'aiagent',
                'action'     => 'skill_save',
                'api_url'    => 'aiagent/skill',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => 'Skill 详情',
                'controller' => 'aiagent',
                'action'     => 'skill_read',
                'api_url'    => 'aiagent/skill/<key>',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => '编辑 Skill',
                'controller' => 'aiagent',
                'action'     => 'skill_update',
                'api_url'    => 'aiagent/skill/<key>',
                'methods'    => 'PUT',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => '删除 Skill',
                'controller' => 'aiagent',
                'action'     => 'skill_delete',
                'api_url'    => 'aiagent/skill/<key>',
                'methods'    => 'DELETE',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => '修改 Skill 状态',
                'controller' => 'aiagent',
                'action'     => 'skill_status',
                'api_url'    => 'aiagent/skill/<key>/status',
                'methods'    => 'PUT',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            // ===== Skill 附属资料管理（技能目录下随 SKILL.md 存储的 .md 文件，Agent 按需加载） =====
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => 'Skill 附属资料列表',
                'controller' => 'aiagent',
                'action'     => 'skill_attachment_list',
                'api_url'    => 'aiagent/skill/<key>/attachments',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => 'Skill 附属资料详情',
                'controller' => 'aiagent',
                'action'     => 'skill_attachment_read',
                'api_url'    => 'aiagent/skill/<key>/attachment',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => '保存 Skill 附属资料',
                'controller' => 'aiagent',
                'action'     => 'skill_attachment_save',
                'api_url'    => 'aiagent/skill/<key>/attachment',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@Skill 管理',
                'menu_name'  => '删除 Skill 附属资料',
                'controller' => 'aiagent',
                'action'     => 'skill_attachment_delete',
                'api_url'    => 'aiagent/skill/<key>/attachment',
                'methods'    => 'DELETE',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            // ===== AI 自动化任务 =====
            [
                'pid'        => '@自动化任务',
                'menu_name'  => 'AI 自动化任务列表',
                'controller' => 'aiagent',
                'action'     => 'tasks',
                'api_url'    => 'aiagent/tasks',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => '创建 AI 自动化任务',
                'controller' => 'aiagent',
                'action'     => 'task_save',
                'api_url'    => 'aiagent/task',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => 'AI 自动化任务详情',
                'controller' => 'aiagent',
                'action'     => 'task_read',
                'api_url'    => 'aiagent/task/<id>',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => '编辑 AI 自动化任务',
                'controller' => 'aiagent',
                'action'     => 'task_update',
                'api_url'    => 'aiagent/task/<id>',
                'methods'    => 'PUT',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => '删除 AI 自动化任务',
                'controller' => 'aiagent',
                'action'     => 'task_delete',
                'api_url'    => 'aiagent/task/<id>',
                'methods'    => 'DELETE',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => '修改 AI 自动化任务状态',
                'controller' => 'aiagent',
                'action'     => 'task_status',
                'api_url'    => 'aiagent/task/<id>/status',
                'methods'    => 'PUT',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => '立即执行 AI 自动化任务',
                'controller' => 'aiagent',
                'action'     => 'task_trigger',
                'api_url'    => 'aiagent/task/<id>/run',
                'methods'    => 'POST',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => 'AI 自动化任务运行列表',
                'controller' => 'aiagent',
                'action'     => 'task_runs',
                'api_url'    => 'aiagent/task/<id>/runs',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => 'AI 自动化任务全量运行记录',
                'controller' => 'aiagent',
                'action'     => 'task_run_index',
                'api_url'    => 'aiagent/task_runs',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
            [
                'pid'        => '@自动化任务',
                'menu_name'  => 'AI 自动化任务运行详情',
                'controller' => 'aiagent',
                'action'     => 'task_run_detail',
                'api_url'    => 'aiagent/task_run/<id>',
                'methods'    => 'GET',
                'auth_type'  => 2,
                'is_show'    => 1,
                'access'     => 1,
            ],
        ];
    }

    /**
     * 插入菜单前先做存量结构修复（根菜单分组化 + 页面菜单改名「AI 对话」+ 接口权限类型迁移 + 模型管理菜单移交系统级）
     * @return int
     */
    protected function insertMenus(): int
    {
        $this->migrateRootMenu();
        $this->migrateChatMenu();
        $this->migrateModelMenu();
        $this->migrateLegacyAuthType();
        return parent::insertMenus();
    }

    /**
     * 存量安装升级：「AI 模型管理」菜单移交系统级，删除本模块此前注册的节点
     *
     * 模型管理（eb_ai_model 表、ai/model/* 路由与页面）为系统内置能力，
     * 页面菜单与接口权限节点已由核心安装 SQL 提供（「设置 → AI 模型」），
     * 历史版本由本模块注册的「模型管理」分组与 ai/model/* 权限节点必须清理，
     * 避免与系统级节点重复导致权限与菜单双份维护。
     *
     * @return void
     */
    protected function migrateModelMenu(): void
    {
        $query = Db::table($this->table('system_menus'))
            ->where(function ($q) {
                // 本模块注册的模型管理接口权限节点
                $q->where('api_url', 'like', 'ai/model/%')
                    // 「模型管理」分组容器（无 api_url，按名称与模块归属匹配）
                    ->whereOr(function ($group) {
                        $group->where('menu_name', '模型管理')->where('api_url', '');
                    });
            });
        if ($this->supportsOwnershipColumn('system_menus')) {
            $query->where('owner_plugin_id', $this->getOwnerPluginId());
        }
        $query->delete();
    }

    /**
     * 存量安装升级：接口权限节点 auth_type 由 3 迁移为 2
     *
     * 历史版本按「auth_type=3=接口」的错误语义写入菜单，而核心权限校验
     * （SystemRoleServices::verifyAuth）仅识别 auth_type=2 且 api_url 非空的
     * 接口节点，导致本模块接口从未纳入角色权限管控（未注册即放行）。
     * 现按新配置逐条将同名同接口的 auth_type=3 记录迁移为 auth_type=2，
     * 并按新身份重算 menu_key（auth_type 参与稳定标识哈希，改类型后必须同步更新），
     * 确保后续 insertMenus 按 menu_key 幂等匹配，不产生重复菜单。
     *
     * @return void
     */
    protected function migrateLegacyAuthType(): void
    {
        foreach ($this->getMenuConfig() as $menu) {
            if ((int) ($menu['auth_type'] ?? 0) !== 2 || empty($menu['api_url'])) {
                continue;
            }
            $query = Db::table($this->table('system_menus'))
                ->where('menu_name', $menu['menu_name'])
                ->where('api_url', $menu['api_url'])
                ->where('auth_type', 3);
            if ($this->supportsOwnershipColumn('system_menus')) {
                $query->whereIn('owner_plugin_id', ['', $this->getOwnerPluginId()]);
            }
            // 同名同接口的旧记录至多迁移一条，避免多条记录写同一 menu_key 撞唯一索引
            $legacy = $query->find();
            if (!$legacy) {
                continue;
            }
            $updates = ['auth_type' => 2];
            if ($this->supportsMenuKeyColumn()) {
                $updates['menu_key'] = $this->normalizeMenu($menu)['menu_key'];
            }
            Db::table($this->table('system_menus'))
                ->where('id', $legacy['id'])
                ->update($updates);
        }
    }

    /**
     * 存量安装升级：一级「AI Agent」菜单迁移为「AI」对话入口
     *
     * 历史版本根菜单 menu_path 直接指向 /aiagent/index 且为分组形态，
     * 这里统一迁移为「AI」菜单（menu_name=AI、menu_path=/aiagent/index、
     * unique_auth=aiagent-manage），点击直接进入 AI 对话页。
     * 迁移必须先于插入执行，避免父级菜单被重新插入造成两条顶级记录。
     *
     * @return void
     */
    protected function migrateRootMenu(): void
    {
        Db::table($this->table('system_menus'))
            ->where('menu_name', 'AI Agent')
            ->where('menu_path', '/aiagent/index')
            ->update([
                'menu_name'   => self::ROOT_MENU,
                'menu_path'   => '/aiagent/index',
                'unique_auth' => 'aiagent-manage',
                'mark'        => 'AI 对话入口',
            ]);
    }

    /**
     * 存量安装升级：AI 菜单改为直接进入对话页，移除模型/MCP/Skill 管理子菜单
     *
     * 历史版本「AI」为分组菜单（menu_path=/aiagent，前端无对应路由导致空白），
     * 且「智能体管理」页面菜单、会话列表接口含 <agent_id> 占位符，均需迁移；
     * 模型/MCP/Skill 管理改为对话页内遮盖层，页面入口子菜单删除。
     * 迁移必须先于插入执行，避免按 menu_name 查重时重复插入新名称记录。
     *
     * @return void
     */
    protected function migrateChatMenu(): void
    {
        // 分组形态的「AI」菜单改为直接进入对话页
        Db::table($this->table('system_menus'))
            ->where('menu_name', self::ROOT_MENU)
            ->where('menu_path', '/aiagent')
            ->update([
                'menu_path'   => '/aiagent/index',
                'unique_auth' => 'aiagent-manage',
                'mark'        => 'AI 对话入口',
            ]);
        // 「智能体管理」页面菜单改名「AI 对话」（更早版本遗留）
        Db::table($this->table('system_menus'))
            ->where('menu_path', '/aiagent/index')
            ->where('menu_name', '智能体管理')
            ->update([
                'menu_name' => 'AI 对话',
                'mark'      => 'AI 对话工作台',
            ]);
        // 会话列表接口去掉 agent_id 参数
        Db::table($this->table('system_menus'))
            ->where('api_url', 'aiagent/conversations/<agent_id>')
            ->update([
                'menu_name' => 'AI 对话会话列表',
                'api_url'   => 'aiagent/conversations',
            ]);
        // 删除模型/MCP/Skill 管理页面入口子菜单（管理功能移入对话页遮盖层）
        Db::table($this->table('system_menus'))
            ->whereIn('menu_path', ['/aiagent/model', '/aiagent/mcp', '/aiagent/skill'])
            ->delete();
    }

    /**
     * 模块自有表：Agent 任务/会话/MCP 相关表
     * @return array 表名列表（不含前缀）
     */
    protected function getOwnedTables(): array
    {
        return ['ai_agent_task_run', 'ai_agent_task', 'ai_mcp_server', 'ai_agent_message', 'ai_agent_conversation'];
    }

    /** 卸载清理：清空本模块业务表数据并返回清理记录数 */
    protected function cleanData(): int
    {
        $count = 0;
        $count += $this->cleanTable('ai_agent_task_run');
        $count += $this->cleanTable('ai_agent_task');
        $count += $this->cleanTable('ai_mcp_server');
        $count += $this->cleanTable('ai_agent_message');
        $count += $this->cleanTable('ai_agent_conversation');
        // 预置的调度记录与系统配置项随卸载移除（配置分类若无其他配置项一并删除）
        $count += Db::name('system_crontab')->where('mark', 'aiAgentTask')->delete();
        $tabId = (int)Db::name('system_config_tab')->where('eng_title', 'aiagent')->value('id');
        if ($tabId > 0) {
            $count += Db::name('system_config')->where('config_tab_id', $tabId)->whereIn('menu_name', ['aiagent_qywechat_webhook', 'ai_allow_private_endpoint', 'aiagent_email_recipients'])->delete();
            $count += Db::name('system_config_tab')->where('id', $tabId)->delete();
        }
        return $count;
    }

    /**
     * 预置数据：系统内置 MCP Server 与默认接入的第三方 MCP Server（幂等，按 server_key 查重）
     *
     * - builtin：系统内置 MCP（service_mcp 模块的 /api/mcp 端点），为 Agent 提供
     *   商品、订单、用户等业务查询工具；密钥留空表示运行时自动跟随系统配置
     *   mcp_appkey，无需手动维护。默认地址优先取 site_url；在官方 Docker 部署下
     *   （以容器名环境变量 MYSQL_HOST_IP 为标志）PHP 进程经 site_url 回环自调用
     *   不可达，自动改走 nginx 容器名地址，如实际不符可在 MCP 管理页修改。
     * - tavily：联网搜索（官方 keyless 免费模式，通过自定义请求头声明免密访问，开箱即用，有限速；
     *   如需更高额度可到 tavily.com 免费申请 API Key，把 URL 改为
     *   https://mcp.tavily.com/mcp/?tavilyApiKey=你的key 并清空附加请求头）
     * - amap：高德地图（地理/POI/路线/天气，默认停用，需用户到高德开放平台申请
     *   「Web 服务」Key 替换 URL 占位后启用）
     * - memcp：MemCP 记忆库（个人记忆与凭证管理，默认停用，需用户到 memcp.cn
     *   申请 API Key，替换附加请求头 Authorization 中的 Bearer 占位符后启用）
     *
 * 工具清单（tools 字段）不在此预置：同步工具需发起 HTTP 请求，属运行时动作。
 * 内置服务读取工具时若发现缓存为空会自动补同步一次（见
 * McpToolService::autoSyncBuiltinTools()），安装后开箱即用；也可在 MCP 管理
 * 页手动点击「同步工具」拉取。
     *
     * @return int 新增记录数
     */
    protected function seed(): int
    {
        // 内置 MCP 访问地址：官方 Docker 部署走 nginx 容器名，其余环境走站点地址
        $dockerDeploy = getenv('MYSQL_HOST_IP') !== false && getenv('MYSQL_HOST_IP') !== '';
        $builtinUrl = ($dockerDeploy ? 'http://crmeb_nginx' : rtrim((string) sys_config('site_url', ''), '/')) . '/api/mcp';
        $rows = [
            [
                'name'        => '系统内置 MCP',
                'server_key'  => 'builtin',
                'url'         => $builtinUrl,
                'api_key'     => '',
                'headers'     => '',
                'timeout'     => 15,
                'description' => 'CRMEB 系统内置的商品、订单、用户等业务查询工具（由 service_mcp 模块提供）。密钥留空时自动使用系统配置的 MCP 访问密钥（mcp_appkey）；如连接失败请检查本地址是否为 PHP 进程可达的 MCP 接口地址（Docker 部署可改为 http://crmeb_nginx/api/mcp），修改后点击「同步工具」拉取工具清单',
                'status'      => 1,
            ],
            [
                'name'        => 'Tavily 联网搜索',
                'server_key'  => 'tavily',
                'url'         => 'https://mcp.tavily.com/mcp/',
                'api_key'     => '',
                'headers'     => '{"X-Tavily-Access-Mode":"keyless"}',
                'timeout'     => 15,
                'description' => '联网搜索与网页内容提取（官方 keyless 免费模式，有限速；如需更高额度可到 tavily.com 免费申请 API Key，把 URL 改为 https://mcp.tavily.com/mcp/?tavilyApiKey=你的key 并清空附加请求头）',
                'status'      => 1,
            ],
            [
                'name'        => '高德地图',
                'server_key'  => 'amap',
                'url'         => 'https://mcp.amap.com/mcp?key=请替换为你的高德Key',
                'api_key'     => '',
                'headers'     => '',
                'timeout'     => 15,
                'description' => '地理编码/POI搜索/路线规划/天气查询。需到高德开放平台 lbs.amap.com 免费注册并创建「Web 服务」Key，替换 URL 中的 key 后启用',
                'status'      => 0,
            ],
            [
                'name'        => 'MemCP 记忆库',
                'server_key'  => 'memcp',
                'url'         => 'https://www.memcp.cn/mcp',
                'api_key'     => '',
                'headers'     => '{"Authorization":"Bearer 请替换为你的MemCP密钥"}',
                'timeout'     => 15,
                'description' => 'MemCP 个人记忆与凭证管理（www.memcp.cn）。需注册并创建 API Key，替换附加请求头 Authorization 中的 Bearer 占位符后启用',
                'status'      => 0,
            ],
        ];

        $count = 0;
        foreach ($rows as $row) {
            if (Db::name('ai_mcp_server')->where('server_key', $row['server_key'])->count() > 0) {
                continue;
            }
            $row['create_time'] = time();
            $row['update_time'] = time();
            Db::name('ai_mcp_server')->insert($row);
            $count++;
        }
        $count += $this->seedTaskScheduler();
        $count += $this->seedTaskConfig();
        return $count;
    }

    /**
     * 预置自动化任务调度器：eb_system_crontab 插入 mark=aiAgentTask 的每分钟触发记录（幂等）
     *
     * 触发器仅做「到期扫描 + 入队」，timer 单进程内毫秒级完成；
     * timer 进程内任务清单 30 秒自动刷新，安装后无需额外清缓存。
     *
     * @return int 新增记录数
     */
    private function seedTaskScheduler(): int
    {
        if (Db::name('system_crontab')->where('mark', 'aiAgentTask')->where('is_del', 0)->count() > 0) {
            return 0;
        }
        Db::name('system_crontab')->insert([
            'name'                => 'AI 自动化任务调度',
            'mark'                => 'aiAgentTask',
            'content'             => '扫描到期的 AI 自动化任务并入队执行（aiagent 模块）',
            'type'                => 2,
            'minute'              => 1,
            'timeStr'             => '0 */1 * * * *',
            'next_execution_time' => time(),
            'is_open'             => 1,
            'add_time'            => time(),
            'update_time'         => time(),
        ]);
        return 1;
    }

    /**
     * 预置自动化任务系统配置：分类「AI 自动化任务」+ 企微机器人 Webhook（幂等）
     *
     * - aiagent_qywechat_webhook：企业微信群机器人推送地址（服务端注入，模型不可指定推送目标）
     *
     * @return int 新增记录数
     */
    private function seedTaskConfig(): int
    {
        $count = 0;
        $tabId = (int)Db::name('system_config_tab')->where('eng_title', 'aiagent')->value('id');
        if ($tabId === 0) {
            $tabId = (int)Db::name('system_config_tab')->insertGetId([
                'pid'       => 0,
                'title'     => 'AI 自动化任务',
                'eng_title' => 'aiagent',
                'status'    => 1,
                'info'      => 0,
                'icon'      => 'md-alarm',
                'type'      => 0,
                'sort'      => 0,
                'menus_id'  => 0,
            ]);
            $count++;
        }
        $rows = [
            // type 存控件类型（createForm 按 type 分发：text/radio/textarea...），info 才是表单标签
            [
                'menu_name'     => 'aiagent_qywechat_webhook',
                'type'          => 'text',
                'input_type'    => 'input',
                'config_tab_id' => $tabId,
                'value'         => '',
                'info'          => '企业微信机器人 Webhook',
                'desc'          => '企业微信机器人推送地址（qyapi.weixin.qq.com 的 webhook 地址），用于 AI 自动化任务与对话消息推送',
                'sort'          => 1,
                'status'        => 1,
            ],
            [
                'menu_name'     => 'ai_allow_private_endpoint',
                'type'          => 'radio',
                'input_type'    => 'radio',
                'parameter'     => "0=>关闭\n1=>开启",
                'config_tab_id' => $tabId,
                'value'         => '0',
                'info'          => '允许内网 AI 服务地址',
                'desc'          => '开启后 AI 模型接口地址与自定义 MCP Server 允许填写内网地址（如本机自建 Ollama、内网 MCP 网关）；云服务器元数据地址始终禁止。默认关闭以保证出站安全',
                'sort'          => 2,
                'status'        => 1,
            ],
            [
                'menu_name'     => 'aiagent_email_recipients',
                'type'          => 'textarea',
                'input_type'    => 'textarea',
                'config_tab_id' => $tabId,
                'value'         => '',
                'info'          => '无人值守邮件收件白名单',
                'desc'          => '自动化任务发送邮件的额外允许收件地址，多个用逗号分隔（任务登记的通知邮箱始终允许）；未配置且任务未登记邮箱时，无人值守邮件将被拒绝发送',
                'sort'          => 3,
                'status'        => 1,
            ],
        ];
        foreach ($rows as $row) {
            if (Db::name('system_config')->where('menu_name', $row['menu_name'])->count() > 0) {
                continue;
            }
            Db::name('system_config')->insert($row);
            $count++;
        }
        return $count;
    }

    /**
     * 判断模块表是否存在指定列（用于增量迁移幂等）
     * @param string $table 不含前缀的表名
     * @param string $column 列名
     * @return bool
     */
    protected function hasColumn(string $table, string $column): bool
    {
        $database = config('database.connections.mysql.database');
        $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1";
        $result = \think\facade\Db::query($sql, [$database, $this->table($table), $column]);
        return !empty($result);
    }
}
