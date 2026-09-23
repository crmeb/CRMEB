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

use app\adminapi\controller\schema\SchemaPageController;
use think\facade\Route;

/**
 * 通用页面网关路由
 *
 * 插件管理页面的统一数据入口，前端通用页面容器（SchemaPage）经此读写页面配置与数据。
 * 路由变量：pluginKey=插件目录名（如 mcp），pageKey=页面标识（如 list）。
 */
Route::group('schema', function () {
    Route::get('plugin/:pluginKey/:pageKey/config', [SchemaPageController::class, 'config'])
        ->option(['real_name' => '通用页面-获取页面配置']);
    Route::get('plugin/:pluginKey/:pageKey/list', [SchemaPageController::class, 'list'])
        ->option(['real_name' => '通用页面-获取列表数据']);
    Route::get('plugin/:pluginKey/:pageKey/form', [SchemaPageController::class, 'form'])
        ->option(['real_name' => '通用页面-获取表单规则']);
    Route::get('plugin/:pluginKey/:pageKey/detail', [SchemaPageController::class, 'detail'])
        ->option(['real_name' => '通用页面-获取详情数据']);
    Route::post('plugin/:pluginKey/:pageKey/save', [SchemaPageController::class, 'save'])
        ->option(['real_name' => '通用页面-保存表单']);
    Route::post('plugin/:pluginKey/:pageKey/action', [SchemaPageController::class, 'action'])
        ->option(['real_name' => '通用页面-执行动作']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class,
])->option(['mark' => 'schema', 'mark_name' => '通用页面']);
