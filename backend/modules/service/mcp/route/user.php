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
use think\facade\Route;

/**
 * MCP 服务对外接口路由
 *
 * 由 api 应用加载，URL 前缀为 /api/
 * MCP 客户端连接地址：POST {site_url}/api/mcp
 */
Route::post('mcp', [\modules\service\mcp\controller\McpController::class, 'index'])
    ->option(['real_name' => 'MCP服务接口']);

// 图片直传端点：跨机场景把客户端本机图片字节带外写入暂存区（票据鉴权，见 crmeb_upload_ticket 工具）
Route::post('mcp/upload', [\modules\service\mcp\controller\McpUploadController::class, 'index'])
    ->option(['real_name' => 'MCP图片直传接口']);
