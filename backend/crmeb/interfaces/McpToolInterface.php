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

namespace crmeb\interfaces;

/**
 * MCP 工具契约
 *
 * 所有 MCP 工具（含 MCP 服务模块内置工具与第三方插件通过 McpToolsRegister
 * 事件注册的工具）必须实现本接口，由 MCP 服务模块的工具注册中心统一管理与分发。
 * 本接口为系统级契约：插件实现它不构成对任何模块类的依赖。
 *
 * 可选约定：工具类可额外实现 `interaction(): string` 方法声明交互要求，
 * 返回 `execute` 表示工具会修改系统数据、需用户确认后才真实执行（客户端
 * 收到工具定义中的 interaction 字段后应先弹确认卡）；不实现或返回空串
 * 表示可直接执行。采用可选方法而非接口成员，保证第三方已注册工具无需改动。
 *
 * 可选约定：工具类可额外实现 `label(): string` 方法返回简短中文名称
 * （2~6 字，如「订单列表」），供管理后台工具列表与账号权限勾选展示；
 * 不实现或返回空串时后台回退展示 description()。同样采用可选方法而非
 * 接口成员，保证第三方已注册工具无需改动。
 *
 * 可选约定：工具类可额外实现 `category(): string` 方法返回分类名
 * （2~6 字中文，如「订单」「系统设置」），供管理后台按分类分组展示工具
 * 列表与账号权限勾选项；不实现或返回空串时后台归入「其他」分组。内置
 * 工具使用 McpToolRegistry::CATEGORY_ORDER 约定的分类名，第三方插件工具
 * 可返回自定义分类名，后台按出现顺序追加为新分组。同样采用可选方法而非
 * 接口成员，保证第三方已注册工具无需改动。
 */
interface McpToolInterface
{
    /**
     * 工具名称（MCP 协议 tools/call 的 name，建议 crmeb_ 前缀 + 下划线）
     * @return string
     */
    public function name(): string;

    /**
     * 工具功能描述（供 AI 模型理解工具用途）
     * @return string
     */
    public function description(): string;

    /**
     * 入参 JSON Schema（MCP 协议 inputSchema 结构）
     * @return array
     */
    public function inputSchema(): array;

    /**
     * 执行工具
     * @param array $args AI 客户端传入的工具参数
     * @return array 业务数据，将以 JSON 文本形式返回给客户端
     * @throws \Throwable 业务错误（实现方抛出的异常消息将透传给客户端）
     */
    public function execute(array $args): array;
}
