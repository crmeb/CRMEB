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
use app\adminapi\controller\plugin\PluginController;

/**
 * 插件管理路由
 */
Route::group('plugin', function () {
    // 获取所有插件列表及状态（支持 ?type=product 或 ?type=activity 筛选）
    Route::get('list', [PluginController::class, 'list']);

    // 安装插件（POST plugin_key=normal）
    Route::post('install', [PluginController::class, 'install']);

    // 卸载插件（POST plugin_key=normal）
    Route::post('uninstall', [PluginController::class, 'uninstall']);

    // 启用插件（POST plugin_key=normal）
    Route::post('enable', [PluginController::class, 'enable']);

    // 禁用插件（POST plugin_key=normal）
    Route::post('disable', [PluginController::class, 'disable']);

    // 上传插件（POST multipart/form-data, field: plugin_package）
    Route::post('upload', [PluginController::class, 'upload']);

    // 刷新插件消息模板（TEMPORARY-PLUGIN-NOTICE-REFRESH：临时功能，后续移除）
    // POST plugin_key=normal，以插件 Installer::getNoticeDefinitions 覆盖式更新通知/渠道模板
    Route::post('refresh_notices', [PluginController::class, 'refreshNotices']);
})->middleware([
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
])->option([
    'parent' => 'plugin',
    'cate_name' => '插件管理',
]);
