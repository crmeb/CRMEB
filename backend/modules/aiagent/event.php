<?php
declare(strict_types=1);

/** AI Agent 模块事件监听。
 *
 * 本模块不再注册本地 MCP 工具：通用业务工具统一由 service_mcp 模块的
 * 内置注册表（modules/service/mcp/mcp/tools/）提供，本模块仅保留
 * 远程 MCP Server 转发与 crmeb_ask_user 虚拟问询工具（见 McpToolService）。
 */
return [
    'listen' => [],
];
