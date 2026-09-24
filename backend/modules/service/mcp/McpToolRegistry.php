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

use crmeb\interfaces\McpToolInterface;
use think\App;

/**
 * MCP 工具注册中心
 *
 * 管理所有可被 AI 客户端调用的工具：
 * - 内置工具按"约定优于配置"自动发现：tools/ 目录下以 Tool.php 结尾且实现
 *   McpToolInterface 的类自动注册，新增工具仅需创建类文件、无需修改任何清单
 * - 第三方插件通过监听 McpToolsRegister 事件调用 register() 追加/覆盖同名工具
 *
 * 工具支持传入类名（延迟实例化，由容器解析并注入依赖）或现成实例；
 * 容器 make 默认单例，重复调用开销可忽略。
 */
class McpToolRegistry
{
    /**
     * 内置工具目录约定后缀（文件名须以 Tool.php 结尾才会被自动发现）
     */
    private const TOOL_FILE_SUFFIX = 'Tool.php';

    /**
     * 内置工具命名空间（与 tools/ 目录一一对应）
     */
    private const TOOL_NAMESPACE = 'modules\\service\\mcp\\tools\\';

    /**
     * 内置工具分类展示顺序（与各工具 category() 返回值对应）
     *
     * 仅约束展示顺序：未知分类（第三方插件工具自定义）按出现顺序追加在后，
     * 未声明分类的工具由后台归入「其他」分组排在最后。
     */
    public const CATEGORY_ORDER = ['用户', '商品', '订单', '售后退款', '财务', '营销', '系统设置', '消息通知', '开发支持'];

    /**
     * 自动发现内置工具（扫描 tools/ 目录）
     *
     * 发现约定：文件名以 Tool.php 结尾 + 类实现 McpToolInterface，
     * 抽象类、辅助类等不符合约定的文件自动排除。
     *
     * @return array<int, class-string<McpToolInterface>> 工具类名列表（按文件名字典序）
     */
    public static function builtinTools(): array
    {
        $files = glob(__DIR__ . '/tools/*' . self::TOOL_FILE_SUFFIX) ?: [];
        $tools = [];
        foreach ($files as $file) {
            $class = self::TOOL_NAMESPACE . basename($file, '.php');
            if (is_subclass_of($class, McpToolInterface::class)) {
                $tools[] = $class;
            }
        }
        return $tools;
    }

    /**
     * 已完成初始化的注册中心（每请求缓存）
     *
     * 统一「注册自动发现的内置工具 + 触发 McpToolsRegister 扩展事件」的
     * 初始化流程，控制器与 schema 页面共用；缓存避免同请求内重复派发事件。
     *
     * @return self
     */
    public static function booted(): self
    {
        if (self::$booted === null) {
            $registry = app()->make(self::class);
            $registry->registerAll(self::builtinTools());
            // 派发工具注册事件：其他插件可通过监听 McpToolsRegister 追加/覆盖工具
            event('McpToolsRegister', [$registry]);
            self::$booted = $registry;
        }
        return self::$booted;
    }

    /**
     * 已完成初始化的注册中心缓存（booted 使用）
     * @var self|null
     */
    private static ?McpToolRegistry $booted = null;

    /**
     * 已注册工具映射（工具名 => McpToolInterface 实例或类名）
     * @var array<string, McpToolInterface|string>
     */
    private array $tools = [];

    /**
     * 应用实例（用于容器解析工具类）
     * @var App
     */
    private App $app;

    /**
     * 构造函数
     * @param App $app 应用实例
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 注册工具（同名工具后注册者覆盖，允许插件定制内置工具行为）
     *
     * @param McpToolInterface|class-string<McpToolInterface> $tool 工具实例或类名
     * @return void
     */
    public function register(McpToolInterface|string $tool): void
    {
        if (is_string($tool)) {
            // 类名方式：先借容器实例化取一次名称，保证注册表按名称可检索
            $instance = $this->app->make($tool);
            $this->tools[$instance->name()] = $instance;
            return;
        }
        $this->tools[$tool->name()] = $tool;
    }

    /**
     * 批量注册工具
     *
     * @param array $tools 工具实例/类名列表
     * @return void
     */
    public function registerAll(array $tools): void
    {
        foreach ($tools as $tool) {
            $this->register($tool);
        }
    }

    /**
     * 是否存在指定工具
     *
     * @param string $name 工具名
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    /**
     * 获取全部工具的 MCP 定义（tools/list 响应数据）
     *
     * 工具类若实现了可选的 interaction() 方法且返回非空，定义中额外携带
     * interaction 字段，供 AI 客户端识别需确认后执行的写操作工具；
     * 若实现了可选的 label() 方法且返回非空，定义中额外携带 label 字段
     *（简短中文名称），供后台工具列表与账号权限勾选展示
     *
     * @return array 工具定义数组（name/description/inputSchema[，interaction][，label]）
     */
    public function allDefinitions(): array
    {
        $definitions = [];
        foreach ($this->tools as $tool) {
            $tool = $this->resolve($tool);
            $definition = [
                'name'        => $tool->name(),
                'description' => $tool->description(),
                'inputSchema' => $tool->inputSchema(),
            ];
            // 可选交互声明：仅声明了交互要求的工具携带该字段，其余保持标准结构
            if (method_exists($tool, 'interaction')) {
                $interaction = (string)$tool->interaction();
                if ($interaction !== '') {
                    $definition['interaction'] = $interaction;
                }
            }
            // 可选中文名：仅实现了 label() 且非空的工具携带该字段，供后台展示
            if (method_exists($tool, 'label')) {
                $label = trim((string)$tool->label());
                if ($label !== '') {
                    $definition['label'] = $label;
                }
            }
            // 可选中文分类：仅实现了 category() 且非空的工具携带该字段，供后台分组展示
            if (method_exists($tool, 'category')) {
                $category = trim((string)$tool->category());
                if ($category !== '') {
                    $definition['category'] = $category;
                }
            }
            $definitions[] = $definition;
        }
        return $definitions;
    }

    /**
     * 按约定顺序整理分类清单（去重）
     *
     * CATEGORY_ORDER 中的已知分类按约定顺序在前，其余分类（第三方插件
     * 自定义）按出现顺序追加在后；未声明分类（空串）不返回，由调用方
     * 归入「其他」分组。
     *
     * @param array $definitions 工具定义列表（allDefinitions() 返回结构）
     * @return array 分类名列表
     */
    public static function orderedCategories(array $definitions): array
    {
        $seen = [];
        foreach ($definitions as $definition) {
            $category = trim((string)($definition['category'] ?? ''));
            if ($category !== '' && !in_array($category, $seen, true)) {
                $seen[] = $category;
            }
        }
        return array_merge(
            array_values(array_intersect(self::CATEGORY_ORDER, $seen)),
            array_values(array_diff($seen, self::CATEGORY_ORDER))
        );
    }

    /**
     * 调用工具（tools/call）
     *
     * @param string $name 工具名
     * @param array  $args 工具参数
     * @return array 业务数据
     * @throws McpException 工具不存在或业务错误
     */
    public function call(string $name, array $args = []): array
    {
        if (!$this->has($name)) {
            throw new McpException("未知工具: {$name}");
        }
        return $this->resolve($this->tools[$name])->execute($args);
    }

    /**
     * 解析工具（类名 → 实例）
     *
     * @param McpToolInterface|string $tool 工具实例或类名
     * @return McpToolInterface
     */
    private function resolve(McpToolInterface|string $tool): McpToolInterface
    {
        return is_string($tool) ? $this->app->make($tool) : $tool;
    }
}
