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

namespace modules\product\normal\listener;

use crmeb\interfaces\ListenerInterface;
use modules\product\normal\mcp\NormalProductCreateTool;
use modules\product\normal\mcp\NormalProductReadTool;
use modules\product\normal\mcp\NormalProductUpdateTool;

/**
 * 向 MCP 服务注册本插件工具
 *
 * 仅做工具注册，不写业务逻辑：McpToolsRegister 属关键级事件，
 * 监听器抛异常会导致整个 MCP 接口 500。
 */
class McpToolsRegisterListener implements ListenerInterface
{
    /**
     * 向注册中心注册插件工具（传类名，容器延迟实例化并注入依赖）
     *
     * @param array $event 事件参数按项目规范打包为 [McpToolRegistry 实例]，
     *                     解构后使用（modules\service\mcp 层，不做类型标注避免跨模块 use 依赖）
     */
    public function handle($event): void
    {
        [$registry] = $event;
        $registry->register(NormalProductReadTool::class);
        $registry->register(NormalProductCreateTool::class);
        $registry->register(NormalProductUpdateTool::class);
    }
}
