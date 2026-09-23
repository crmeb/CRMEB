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
use app\adminapi\controller\setting\ModuleController;

/**
 * 模块安装管理路由
 */
Route::group('module', function () {
    // 获取所有模块列表及安装状态（支持 ?type=product_type 或 ?type=activity 筛选）
    Route::get('list', [ModuleController::class, 'list']);

    // 获取产品类型模块列表
    Route::get('productTypes', [ModuleController::class, 'productTypes']);

    // 获取营销活动模块列表
    Route::get('activities', [ModuleController::class, 'activities']);

    // 获取独立业务模块列表（如 agent 分销）
    Route::get('businessModules', [ModuleController::class, 'businessModules']);

    // 安装模块（POST module_key=product_type/hotel、activity/seckill、agent）
    Route::post('install', [ModuleController::class, 'install']);

    // 卸载模块（POST module_key=product_type/hotel、activity/seckill、agent）
    Route::post('uninstall', [ModuleController::class, 'uninstall']);

    // 启用模块（POST module_key=agent）
    Route::post('enable', [ModuleController::class, 'enable']);

    // 禁用模块（POST module_key=agent）
    Route::post('disable', [ModuleController::class, 'disable']);
})->middleware([
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
]);
