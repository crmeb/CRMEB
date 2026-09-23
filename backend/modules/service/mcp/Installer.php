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

namespace modules\service\mcp;

use crmeb\module\BaseModuleInstaller;
use think\facade\Db;

/**
 * MCP 服务安装器
 *
 * 独立数据表：eb_service_mcp_account（MCP 访问账号，每账号独立密钥 + 工具白名单）。
 * 菜单：后台「设置」下注册目录「MCP服务」（纯容器，无页面），其下级挂「MCP配置」
 * （/plugin/mcp/config，MCP 账号管理列表页）与「MCP 工具」（/plugin/mcp/list）两个
 * 菜单，均由 schema/ 目录的通用页面提供者渲染，本插件不开发任何前端页面。
 * 配置：系统配置分组 eng_title='mcp'（MCP服务），仅含服务总开关 mcp_enabled
 * （安装默认开启，关闭后 /api/mcp 拒绝所有调用）；另有一条隐藏分组
 * （config_tab_id=0）的 mcp_appkey：系统账号密钥的自动镜像，
 * 供 AI Agent 模块内置工具源读取（modules 层禁止互引，配置键是唯一合法通道），
 * 由 seed 与系统账号「重置Key」动作维护，禁止手动修改。
 * 账号体系：认证只认 eb_service_mcp_account（单一权威），所有账号（含默认保留
 * 一条的系统账号 is_system=1，初始授予全部工具、可编辑权限）一律按工具白名单
 * 授权；接入代码由 schema/AccountListPage.php 实时生成，不落库。
 */
class Installer extends BaseModuleInstaller
{
    /**
     * 配置分组英文标识
     */
    private const CONFIG_TAB = 'mcp';

    /**
     * 本插件写入的系统配置项 key
     *
     * mcp_write_key 已随写入密钥功能移除退役（1.0.1），保留在清理清单中用于
     * 卸载旧版本站点的残留配置行。
     *
     * @var array<int, string>
     */
    private const CONFIG_KEYS = ['mcp_enabled', 'mcp_appkey', 'mcp_write_key', 'mcp_demo'];

    /**
     * 插件唯一标识
     * @return string
     */
    public function getKey(): string
    {
        return 'service_mcp';
    }

    /**
     * 插件展示名称
     * @return string
     */
    public function getName(): string
    {
        return 'MCP 服务';
    }

    /**
     * 插件类型
     * @return string
     */
    public function getType(): string
    {
        return 'service';
    }

    /**
     * 插件版本
     * @return string
     */
    protected function getVersion(): string
    {
        return '1.0.1';
    }

    /**
     * 安装建表（MCP 访问账号表，已存在时幂等跳过）
     */
    protected function runMigrate(): void
    {
        if (!$this->hasTable('service_mcp_account')) {
            $this->exec("CREATE TABLE IF NOT EXISTS `{$this->table('service_mcp_account')}` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
                `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注(账号用途说明)',
                `app_key` varchar(64) NOT NULL DEFAULT '' COMMENT '访问密钥(32位随机hex,普通账号仅可调用tools白名单内工具)',
                `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统账号: 0普通 1系统(默认保留供AI Agent使用,同样受tools白名单约束,密钥镜像至mcp_appkey)',
                `tools` json DEFAULT NULL COMMENT '授权工具名列表(JSON数组,所有账号仅可调用白名单内工具)',
                `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态: 0停用 1启用(停用后密钥立即失效)',
                `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除: 0正常 1已删除',
                `add_time` int NOT NULL DEFAULT '0' COMMENT '生成时间',
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_app_key` (`app_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset} COLLATE={$this->collation} COMMENT='MCP服务账号表(service_mcp)';");
        }
    }

    /**
     * 卸载删表（MCP 访问账号表）
     */
    protected function rollbackMigrate(): void
    {
        $this->exec("DROP TABLE IF EXISTS `{$this->table('service_mcp_account')}`;");
    }

    /**
     * 模块自有表白名单（cleanTable 数据清理准入，卸载时表由 rollbackMigrate 整体删除）
     * @return array
     */
    protected function getOwnedTables(): array
    {
        return ['service_mcp_account'];
    }

    /**
     * 后台菜单配置
     *
     * 菜单树：
     *
     * 设置 (pid=0, 系统已有, menu_name=设置, controller=setting.system_config)
     * └── MCP服务 (auth_type=1, 目录容器, menu_path 为空, 不指向任何页面)
     *     ├── MCP配置 (auth_type=1, menu_path=/plugin/mcp/config, 通用页面容器渲染的账号管理列表页)
     *     └── MCP 工具 (auth_type=1, menu_path=/plugin/mcp/list, 通用页面容器渲染的工具列表)
     *
     * 子菜单 pid 以 @MCP服务 引用同批次插入的父级目录（基类 insertMenus 解析），
     * 避免硬编码父级 id。两个页面均由通用页面系统提供（schema/ 目录纯 PHP 声明，
     * 前端 SchemaPage 容器按 /admin/plugin/{pluginKey}/{pageKey} 动态路由渲染），
     * 本插件不开发任何专属前端页面。本插件为插件自有菜单（非系统原生），
     * 卸载时由基类 removeMenus() 物理删除即可。
     *
     * @return array
     */
    protected function getMenuConfig(): array
    {
        return [
            // 1. 父级目录容器（无 menu_path，仅聚合下级菜单）
            [
                'parent_key'  => 'core:admin-setting',
                'menu_name'   => 'MCP服务',
                'module'      => 'admin',
                'controller'  => 'mcp',
                'menu_path'   => '',
                'icon'        => 'CRMEB-284-tongyongzujian-xianxing',
                'sort'        => 110,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'unique_auth' => '',
                'mark'        => 'MCP服务目录',
            ],
            // 2. MCP配置页
            [
                'pid'         => '@MCP服务',
                'menu_name'   => 'MCP配置',
                'module'      => 'admin',
                'controller'  => 'mcp',
                'menu_path'   => '/plugin/mcp/config',
                'icon'        => '',
                'sort'        => 10,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'unique_auth' => 'setting-mcp',
                'mark'        => 'MCP服务配置',
            ],
            // 3. MCP 工具列表页
            [
                'pid'         => '@MCP服务',
                'menu_name'   => 'MCP工具',
                'module'      => 'admin',
                'controller'  => 'mcp',
                'menu_path'   => '/plugin/mcp/list',
                'icon'        => '',
                'sort'        => 2,
                'is_show'     => 1,
                'access'      => 1,
                'auth_type'   => 1,
                'unique_auth' => 'setting-mcp-tools',
                'mark'        => 'MCP工具列表',
            ],
        ];
    }

    /**
     * 种子数据：创建 MCP 配置分组与总开关，预置系统账号（幂等）
     *
     * 服务总开关 mcp_enabled 安装默认「开启」，站点开箱即可通过 /api/mcp 接入；
     * 如需整体关闭，在「系统配置 → MCP服务」中切换。另预置系统账号并把其密钥
     * 镜像到隐藏配置 mcp_appkey 供 AI Agent 使用（见 seedSystemAccount）。
     *
     * @return int 新增记录数
     */
    protected function seed(): int
    {
        $count = 0;

        // 1. 配置分组（eng_title 幂等）
        $tab = $this->ensureConfigTab(self::CONFIG_TAB, [
            'pid'       => 0,
            'title'     => 'MCP服务',
            'eng_title' => self::CONFIG_TAB,
            'status'    => 1,
            'info'      => 0,
            'icon'      => '',
            'type'      => 3,
            'sort'      => 0,
            'menus_id'  => 0,
        ]);
        if ($tab['created']) {
            $count++;
        }

        // 2. 服务总开关（menu_name 幂等）：安装默认开启
        $exists = Db::table($this->table('system_config'))
            ->where('menu_name', 'mcp_enabled')
            ->find();
        if (!$exists) {
            Db::table($this->table('system_config'))->insert([
                'menu_name'     => 'mcp_enabled',
                'config_tab_id' => $tab['id'],
                'type'          => 'radio',
                'input_type'    => 'radio',
                'parameter'     => "1=>开启\n0=>关闭",
                'value'         => '1',
                'info'          => '是否开启MCP服务',
                'desc'          => '开启后 AI 客户端可通过 MCP 协议访问本系统',
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'status'        => 1,
                'sort'          => 0,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ]);
            $count++;
        }

        // 3. 系统账号 + mcp_appkey 凭证镜像（幂等）
        $count += $this->seedSystemAccount();

        return $count;
    }

    /**
     * 预置系统账号并维护 mcp_appkey 凭证镜像（幂等）
     *
     * 系统账号（is_system=1）默认保留一条，是 AI Agent 模块内置工具源的调用
     * 身份，与普通账号同样受工具白名单约束：创建时初始授予全部已注册工具，
     * 之后管理员可在「MCP配置」页编辑其权限（收紧白名单即收紧 AI Agent
     * 可见范围）。modules 层禁止互引，AI Agent 无法读取本模块账号表，故其
     * 密钥镜像至系统配置 mcp_appkey（config_tab_id=0 不入配置分组），AI Agent
     * 侧零改动：读取该配置作为调用 /api/mcp 的凭证。
     *
     * 幂等策略：系统账号存在则不重建（白名单为空视为旧版种子残留，自动补齐
     * 为全部工具；管理员主动收紧后不为空则不覆盖）；mcp_appkey 配置行缺失则
     * 插入（隐藏分组），存在则强制同步为系统账号当前密钥并修正为隐藏分组
     * （兼容旧版挂在分组的残留）。
     *
     * @return int 实际写入/修复的记录数
     */
    private function seedSystemAccount(): int
    {
        $count = 0;
        $accountTable = $this->table('service_mcp_account');

        // 1. 系统账号：不存在则创建（初始授予全部已注册工具）
        $system = Db::table($accountTable)
            ->where(['is_system' => 1, 'is_del' => 0])
            ->field('id,app_key,tools')
            ->find();
        if (!$system) {
            Db::table($accountTable)->insert([
                'remark'    => '系统账号（AI Agent 工具源）',
                'app_key'   => bin2hex(random_bytes(16)),
                'is_system' => 1,
                'tools'     => json_encode($this->registeredToolNames()),
                'status'    => 1,
                'is_del'    => 0,
                'add_time'  => time(),
            ]);
            $system = Db::table($accountTable)
                ->where(['is_system' => 1, 'is_del' => 0])
                ->field('id,app_key,tools')
                ->find();
            $count++;
        } elseif (empty(json_decode((string)$system['tools'], true))) {
            // 旧版种子残留（空占位白名单）：补齐为全部工具，避免系统账号零权限
            Db::table($accountTable)
                ->where('id', (int)$system['id'])
                ->update(['tools' => json_encode($this->registeredToolNames())]);
            $count++;
        }

        // 2. mcp_appkey 凭证镜像：插入或同步为系统账号当前密钥，并保持隐藏分组形态
        $config = Db::table($this->table('system_config'))
            ->where('menu_name', 'mcp_appkey')
            ->find();
        if (!$config) {
            Db::table($this->table('system_config'))->insert([
                'menu_name'  => 'mcp_appkey',
                'type'       => 'text',
                'input_type' => 'input',
                'parameter'  => '',
                'value'      => json_encode((string)$system['app_key']),
                'info'       => '系统账号密钥（自动同步）',
                'desc'       => 'MCP 系统账号密钥的自动镜像，供 AI Agent 内置工具源调用使用；请在「MCP配置」页通过系统账号「重置Key」维护，勿手动修改',
                'config_tab_id' => 0,
                'upload_type'   => 1,
                'required'      => '',
                'width'         => 0,
                'high'          => 0,
                'status'        => 1,
                'sort'          => 0,
                'level'         => 0,
                'link_id'       => 0,
                'link_value'    => 0,
            ]);
            $count++;
            return $count;
        }

        // 已存在：同步密钥值并修正为隐藏分组（兼容旧版挂在「MCP服务」分组并带联动的行）
        $current = (string)(json_decode((string)$config['value'], true) ?: '');
        $update = [];
        if ($current !== (string)$system['app_key']) {
            $update['value'] = json_encode((string)$system['app_key']);
        }
        if ((int)$config['config_tab_id'] !== 0) {
            $update['config_tab_id'] = 0;
        }
        if ((int)$config['level'] !== 0) {
            $update['level'] = 0;
            $update['link_id'] = 0;
            $update['link_value'] = 0;
        }
        if ((string)$config['info'] !== '系统账号密钥（自动同步）') {
            $update['info'] = '系统账号密钥（自动同步）';
            $update['desc'] = 'MCP 系统账号密钥的自动镜像，供 AI Agent 内置工具源调用使用；请在「MCP配置」页通过系统账号「重置Key」维护，勿手动修改';
        }
        if ($update) {
            Db::table($this->table('system_config'))
                ->where('menu_name', 'mcp_appkey')
                ->update($update);
            $count++;
        }
        return $count;
    }

    /**
     * 当前已注册的全部工具名（内置 + 插件经 McpToolsRegister 事件追加）
     *
     * 安装/启用流程中事件监听器可能尚未加载，此时退化为内置工具清单，
     * 属可接受的初始白名单；插件工具启用后可在「编辑权限」中补勾选。
     *
     * @return array 工具名列表
     */
    private function registeredToolNames(): array
    {
        return array_column(McpToolRegistry::booted()->allDefinitions(), 'name');
    }

    /**
     * 卸载清理：删除本插件写入的配置项与配置分组
     *
     * CONFIG_KEYS 含已退役的 mcp_write_key（及 1.2.x 历史版本的 mcp_demo），
     * 用于清理旧版本站点的残留配置行。
     *
     * @return int 清理记录数
     */
    protected function cleanData(): int
    {
        $count = Db::table($this->table('system_config'))
            ->whereIn('menu_name', self::CONFIG_KEYS)
            ->delete();

        $count += Db::table($this->table('system_config_tab'))
            ->where('eng_title', self::CONFIG_TAB)
            ->delete();

        return (int)$count;
    }

    /**
     * 确保配置分组 tab 存在（按 eng_title 幂等）
     *
     * @param string $engTitle 分组英文标识
     * @param array  $data     插入数据（仅在不存在时使用）
     * @return array{ id: int, created: bool }
     */
    private function ensureConfigTab(string $engTitle, array $data): array
    {
        $existing = Db::table($this->table('system_config_tab'))
            ->where('eng_title', $engTitle)
            ->find();
        if ($existing) {
            return ['id' => (int)$existing['id'], 'created' => false];
        }
        $id = Db::table($this->table('system_config_tab'))->insertGetId($data);
        return ['id' => (int)$id, 'created' => true];
    }
}
