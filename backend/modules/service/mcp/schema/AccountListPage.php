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

namespace modules\service\mcp\schema;

use crmeb\exceptions\AdminException;
use crmeb\services\schema\BaseSchemaPageProvider;
use crmeb\utils\FormBuilder as Form;
use modules\service\mcp\dao\McpAccountDao;
use modules\service\mcp\McpToolRegistry;
use modules\service\mcp\service\McpAccountServices;

/**
 * MCP 账号管理列表页（通用页面系统）
 *
 * 纯 PHP 声明后台页面：访问 /admin/plugin/mcp/config 管理 MCP 服务访问账号，
 * 账号体系是 MCP 接口的唯一认证权威。
 *
 * 账号模型：每个账号持有独立访问密钥 + 工具白名单（勾选内置/插件注册的
 * MCP 工具），AI 客户端使用账号密钥接入时仅能看到、调用勾选的工具；
 * 系统账号（is_system=1，默认保留一条）供 AI Agent 模块内置工具源调用，
 * 同样受白名单约束、可「编辑权限」收紧（安装时初始授予全部工具），但不
 * 支持删除——其密钥镜像到系统配置 mcp_appkey（modules 层禁止互引，
 * 配置键是唯一合法通道），「重置Key」时自动同步镜像。
 *
 * 页面能力：新增账号（备注 + 勾选工具）、编辑权限、状态开关（所有账号
 * 均可停用，停用后密钥立即失效；系统账号停用即下线 AI Agent 内置工具源）、
 * 重置 Key、删除，行「接入信息」抽屉展示密钥与按账号密钥实时生成的
 * mcpServers JSON。
 */
class AccountListPage extends BaseSchemaPageProvider
{
    /**
     * MCP 服务账号 DAO
     * @var McpAccountDao
     */
    protected McpAccountDao $dao;

    /**
     * MCP 服务账号业务服务
     * @var McpAccountServices
     */
    protected McpAccountServices $services;

    /**
     * 构造注入（注册中心经容器实例化，自动解析依赖）
     * @param McpAccountDao $dao MCP 服务账号 DAO
     * @param McpAccountServices $services MCP 服务账号业务服务
     */
    public function __construct(McpAccountDao $dao, McpAccountServices $services)
    {
        $this->dao = $dao;
        $this->services = $services;
    }

    /**
     * 页面标识（沿用 config，菜单路径 /plugin/mcp/config 不变）
     * @return string
     */
    public static function pageKey(): string
    {
        return 'config';
    }

    /**
     * 页面结构配置（统计卡片 + 顶部动作 + 搜索 + 列定义 + 行操作）
     * @return array
     */
    public function config(): array
    {
        return [
            'type'    => 'list',
            'title'   => 'MCP 账号',
            'cards'   => [
                ['field' => 'status',        'label' => '服务状态', 'type' => 'text'],
                ['field' => 'account_count', 'label' => '账号总数', 'type' => 'number'],
                ['field' => 'tool_count',    'label' => '可用工具', 'type' => 'number'],
                ['field' => 'endpoint',      'label' => '服务端点', 'type' => 'text'],
            ],
            'actions' => [
                ['action' => 'create', 'label' => '新增账号', 'theme' => 'primary'],
            ],
            'search' => [
                ['field' => 'keyword', 'label' => '备注', 'type' => 'input', 'placeholder' => '按备注搜索账号'],
            ],
            'columns' => [
                ['prop' => 'id',      'label' => 'ID',     'width' => 70],
                ['prop' => 'remark',  'label' => '备注',   'minWidth' => 200],
                ['prop' => 'is_system', 'label' => '类型', 'type' => 'tag', 'width' => 90,
                 'map' => [
                     '1' => ['label' => '系统', 'theme' => 'warning'],
                     '0' => ['label' => '普通', 'theme' => 'info'],
                 ]],
                ['prop' => 'tools_count', 'label' => '已授权工具', 'width' => 100],
                // 状态开关：switch 列未声明 action 时统一走通用 status 动作（payload {id,value}）；
                // 所有账号均可停用（含系统账号，停用后 AI Agent 内置工具源不可用）
                ['prop' => 'status', 'label' => '状态', 'type' => 'switch', 'width' => 90],
                ['prop' => 'add_time', 'label' => '生成时间', 'type' => 'datetime', 'width' => 170, 'sortable' => true],
            ],
            'row_actions' => [
                ['action' => 'detail', 'label' => '接入信息', 'mode' => 'drawer_detail'],
                // 所有账号（含系统账号）均可编辑权限；系统账号供 AI Agent 依赖，不可删除
                ['action' => 'edit', 'label' => '编辑权限', 'mode' => 'drawer_form'],
                ['action' => 'reset_key', 'label' => '重置Key', 'mode' => 'confirm_action',
                 'confirm' => '重置后该账号原密钥立即失效，使用它的 AI 客户端需更新配置，确定继续？'],
                ['action' => 'delete', 'label' => '删除', 'mode' => 'confirm_action',
                 'confirm' => '删除后该账号立即无法访问 MCP 服务，确定删除？',
                 'visible' => [['field' => 'is_system', 'op' => 'eq', 'value' => 0]]],
            ],
        ];
    }

    /**
     * 账号列表数据（关键词搜索备注 + 统计卡片 summary）
     *
     * @param array $where 搜索条件（keyword 备注关键词）
     * @param int $page 页码
     * @param int $limit 每页条数
     * @param array $order 排序（支持 id/add_time，默认 id 倒序）
     * @return array {list: array, count: int, summary: array}
     */
    public function list(array $where, int $page, int $limit, array $order = []): array
    {
        $where = ['keyword' => trim((string)($where['keyword'] ?? '')), 'is_del' => 0];
        $order = $this->sanitizeOrder($order);
        $list = $this->dao->selectList($where, 'id,remark,app_key,is_system,tools,status,add_time', $page, $limit, $order, [], true);
        $rows = [];
        foreach ($list as $item) {
            $item = is_array($item) ? $item : $item->toArray();
            $item['status'] = (int)($item['status'] ?? 1);
            // 工具数（系统账号与普通账号一致，均按白名单授权）
            $item['tools_count'] = (string)count((array)($item['tools'] ?? []));
            $rows[] = $item;
        }
        $count = $this->dao->count($where, true);

        // 统计卡片（随列表刷新，服务总开关状态实时反映；开关在「系统配置 → MCP服务」）
        $summary = [
            'status'        => (int)sys_config('mcp_enabled', 1) === 1 ? '已开启' : '已关闭',
            'account_count' => $count,
            'tool_count'    => count(McpToolRegistry::booted()->allDefinitions()),
            'endpoint'      => $this->endpoint(),
        ];
        return ['list' => $rows, 'count' => $count, 'summary' => $summary];
    }

    /**
     * 表单规则（新增/编辑抽屉：备注 + 工具白名单勾选）
     *
     * @param int $id 数据主键，0 表示新增
     * @return array
     */
    public function form(int $id = 0): array
    {
        $info = $id ? $this->dao->get($id, ['id', 'remark', 'is_system', 'tools', 'is_del']) : null;
        if ($info && (int)$info['is_del'] === 1) {
            throw new AdminException('账号不存在或已删除');
        }
        $isSystem = $info && (int)$info['is_system'] === 1;
        $checked = $info ? array_values((array)($info['tools'] ?? [])) : [];

        $field[] = Form::input('remark', '备注', (string)($info['remark'] ?? ''))
            ->placeholder($isSystem ? '系统账号，建议保留说明用途' : '账号用途说明，如：数据分析助手、客服机器人')
            ->required('请输入备注');
        // 多列勾选组：两列展示 + 全选联动 + 英文名/描述悬停提示（option.title）
        $field[] = Form::checkboxGroup('tools', '授权工具', $checked, $this->toolOptions())
            ->info($isSystem
                ? '系统账号供 AI Agent 内置工具源调用，收紧白名单即收紧 AI Agent 可见范围（调整后需在 AI Agent 的 MCP 管理页重新「同步工具」）；标注（写）的为写入/发送类工具，标注（删）的为删除类工具'
                : '账号仅能调用勾选的工具；标注（写）的为写入/发送类工具，标注（删）的为删除类工具')
            ->validate([
                ['required' => true, 'message' => '请至少勾选一个工具', 'type' => 'array', 'trigger' => 'change'],
            ]);
        foreach ($field as &$rule) {
            $rule->col(24);
        }
        unset($rule);
        return create_form($id ? '编辑 MCP 账号' : '新增 MCP 账号', $field, '', $id ? 'PUT' : 'POST');
    }

    /**
     * 保存表单（新增/编辑统一入口，密钥与生成时间由服务层维护）
     *
     * @param int $id 数据主键，0 表示新增
     * @param array $data 表单数据（remark + tools）
     * @return void
     */
    public function save(int $id, array $data): void
    {
        $remark = (string)($data['remark'] ?? '');
        $tools = array_values((array)($data['tools'] ?? []));
        if ($id) {
            $this->services->updateAccount($id, $remark, $tools);
        } else {
            $this->services->createAccount($remark, $tools);
        }
    }

    /**
     * 账号详情（接入信息抽屉：密钥 + 授权工具 + 实时生成的 mcpServers JSON）
     *
     * @param int $id 账号主键
     * @return array {fields: array}
     */
    public function detail(int $id): array
    {
        $account = $this->dao->get($id, ['id', 'remark', 'app_key', 'is_system', 'tools', 'status', 'is_del', 'add_time']);
        if (!$account || (int)$account['is_del'] === 1) {
            throw new AdminException('账号不存在或已删除');
        }
        $account = $account->toArray();
        $isSystem = (int)($account['is_system'] ?? 0) === 1;
        $tools = array_values((array)($account['tools'] ?? []));
        // 展示为 英文名 => 中文名 映射；工具定义已不存在（插件停用/卸载）时回退英文名
        $labelMap = $this->toolLabelMap();
        $toolNames = array_map('strval', $tools);
        sort($toolNames);
        $toolDetail = [];
        foreach ($toolNames as $name) {
            $toolDetail[$name] = $labelMap[$name] ?? $name;
        }
        $fields = [
            ['label' => '备注', 'value' => (string)$account['remark']],
            ['label' => '账号类型', 'value' => $isSystem ? '1' : '0', 'type' => 'tag',
             'map' => [
                 '1' => ['label' => '系统', 'theme' => 'warning'],
                 '0' => ['label' => '普通', 'theme' => 'info'],
             ]],
            ['label' => '状态', 'value' => (int)($account['status'] ?? 1) === 1 ? '启用' : '停用'],
            ['label' => '访问密钥', 'value' => (string)$account['app_key']],
            ['label' => '已授权工具（' . count($tools) . ' 项）', 'value' => (string)json_encode($toolDetail, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 'type' => 'json'],
            ['label' => '生成时间', 'value' => (int)$account['add_time'], 'type' => 'datetime'],
            ['label' => '接入代码（mcpServers JSON）', 'value' => $this->buildServerJson((string)$account['app_key']), 'type' => 'json'],
        ];
        if ($isSystem) {
            $fields[] = ['label' => '接入说明', 'value' => '系统账号默认保留，供 AI Agent 内置工具源调用（密钥自动镜像到系统配置，重置后 AI Agent 自动生效）；'
                . '与其他账号一致，仅能调用「已授权工具」内的工具——收紧白名单即收紧 AI Agent 可见范围，调整后需在 AI Agent 的 MCP 管理页重新「同步工具」；'
                . '新增插件工具不会自动进入白名单，需在「编辑权限」中勾选。'];
        } else {
            $fields[] = ['label' => '接入说明', 'value' => '该账号仅能调用「已授权工具」列表内的工具，未勾选的工具无权限访问；'
                . '复制接入代码到 AI 客户端（WorkBuddy/豆包办公/TraeWork/千问办公 等）的 MCP 配置即可接入；'
                . '密钥泄露时可返回列表「重置Key」或删除账号。'];
        }
        return ['fields' => $fields];
    }

    /**
     * 通用动作：账号状态开关 / 账号重置Key / 删除
     *
     * 系统账号「重置Key」由服务层同步刷新 mcp_appkey 镜像（AI Agent 自动生效）。
     *
     * @param string $action 动作标识
     * @param array $payload 动作参数（行操作含 id，状态开关含 value 目标值）
     * @return array 响应指令（msg 提示，前端默认刷新列表）
     */
    public function action(string $action, array $payload): array
    {
        switch ($action) {
            case 'status':
                $status = (int)($payload['value'] ?? 1) === 1 ? 1 : 0;
                $id = (int)($payload['id'] ?? 0);
                $this->services->setStatus($id, $status);
                // 系统账号停用即下线 AI Agent 内置工具源，提示语单独点明影响
                $account = $this->dao->get($id, ['is_system']);
                $isSystem = $account && (int)$account['is_system'] === 1;
                return ['msg' => $status === 1
                    ? '账号已启用'
                    : ($isSystem ? '系统账号已停用，AI Agent 内置工具源将不可用' : '账号已停用，密钥立即失效')];
            case 'reset_key':
                $this->services->resetKey((int)($payload['id'] ?? 0));
                return ['msg' => '账号密钥已重置，可在「接入信息」中复制新密钥'];
            case 'delete':
                $this->services->removeAccount((int)($payload['id'] ?? 0));
                return ['msg' => '删除成功'];
            default:
                throw new AdminException('不支持的动作: ' . $action);
        }
    }

    /**
     * 工具名 → 展示文案映射（当前已注册的全部工具：内置 + 插件经事件追加）
     *
     * 文案优先取工具中文名（label()），未提供时回退功能描述，最后回退
     * 工具名；写工具在文案后标注（写）、删除类工具标注（删），便于管理员
     * 识别写入/发送与删除类工具。
     *
     * @return array 工具名 => 展示文案
     */
    private function toolLabelMap(): array
    {
        $map = [];
        foreach (McpToolRegistry::booted()->allDefinitions() as $definition) {
            $label = (string)($definition['label'] ?? '');
            if ($label === '') {
                $label = (string)($definition['description'] ?? '');
            }
            if ($label === '') {
                $label = (string)$definition['name'];
            }
            if ((string)($definition['interaction'] ?? '') === 'delete') {
                $label .= '（删）';
            } elseif ((string)($definition['interaction'] ?? '') === 'execute') {
                $label .= '（写）';
            }
            $map[(string)$definition['name']] = $label;
        }
        return $map;
    }

    /**
     * 工具勾选选项（文案取自 toolLabelMap()，按分类分组排序）
     *
     * 选项按注册中心约定分类顺序排列（插件自定义分类按出现顺序追加，
     * 未声明分类的归入「其他」），group 字段供前端渲染分组标题与分组全选；
     * title 携带英文名 + 描述作为悬停提示：form-create 渲染 checkbox 时会把
     * 选项的额外字段透传给 el-checkbox 根节点，原生 title 随之生效。
     *
     * @return array {label, value, group, title} 选项列表
     */
    private function toolOptions(): array
    {
        $labelMap = $this->toolLabelMap();
        $definitions = McpToolRegistry::booted()->allDefinitions();
        // 分组排序：约定分类在前，插件自定义分类次之，「其他」兜底；组内按工具名排序
        $order = array_merge(McpToolRegistry::orderedCategories($definitions), ['其他']);
        usort($definitions, fn(array $a, array $b) => array_search($this->categoryOf($a), $order, true)
            <=> array_search($this->categoryOf($b), $order, true)
            ?: strcmp((string)$a['name'], (string)$b['name']));
        $options = [];
        foreach ($definitions as $definition) {
            $name = (string)$definition['name'];
            $title = $name;
            $description = trim((string)($definition['description'] ?? ''));
            if ($description !== '') {
                $title .= "\n" . $description;
            }
            $options[] = [
                'label' => $labelMap[$name],
                'value' => $name,
                'group' => $this->categoryOf($definition),
                'title' => $title,
            ];
        }
        return $options;
    }

    /**
     * 工具所属分类（未声明分类的工具归入「其他」）
     *
     * @param array $definition 工具定义（allDefinitions() 返回结构）
     * @return string 分类名
     */
    private function categoryOf(array $definition): string
    {
        $category = trim((string)($definition['category'] ?? ''));
        return $category !== '' ? $category : '其他';
    }

    /**
     * 排序白名单化（支持 id/add_time 升降序，默认 id 倒序）
     *
     * @param array $order 前端提交的排序
     * @return string 排序串（如 "id desc"）
     */
    private function sanitizeOrder(array $order): string
    {
        foreach ($order as $field => $direction) {
            if (in_array($field, ['id', 'add_time'], true) && in_array(strtolower((string)$direction), ['asc', 'desc'], true)) {
                return $field . ' ' . strtolower((string)$direction);
            }
        }
        return 'id desc';
    }

    /**
     * 服务端点地址（站点域名 + 统一路径，站点配置缺失时回退当前请求域名）
     * @return string
     */
    private function endpoint(): string
    {
        $siteUrl = rtrim(trim((string)sys_config('site_url', '')), '/');
        if ($siteUrl === '') {
            $siteUrl = rtrim(request()->domain(), '/');
        }
        return $siteUrl . '/api/mcp';
    }

    /**
     * 生成 mcpServers JSON 接入代码（按账号密钥实时生成，避免静态演示代码过期）
     *
     * 除 url/headers 外补充 name/timeout/description/disabled 增强字段：
     * 标准客户端忽略未知键不受影响，识别的客户端获得站点名展示与超时控制。
     *
     * @param string $appKey 账号访问密钥
     * @return string
     */
    private function buildServerJson(string $appKey): string
    {
        $siteName = trim((string)sys_config('site_name', ''));
        $demo = [
            'mcpServers' => [
                'crmeb' => [
                    'name'        => $siteName !== '' ? $siteName : 'CRMEB 商城',
                    'url'         => $this->endpoint(),
                    'headers'     => ['X-Mcp-Key' => $appKey],
                    'timeout'     => 15,
                    'description' => 'CRMEB 商城管理工具集：查询/管理商城数据（商品、订单、售后、配置等，以账号已授权工具为准）。',
                    'disabled'    => false,
                ],
            ],
        ];
        return (string)json_encode($demo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
