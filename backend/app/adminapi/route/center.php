<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

/**
 * 中台统一管理路由 - 后台端
 * 用户端路由见 app/api/route/center.php
 */

use think\facade\Route;
use app\http\middleware\AllowOriginMiddleware;
use app\adminapi\middleware\AdminAuthTokenMiddleware;
use app\adminapi\controller\product\CenterProductController;
use app\adminapi\controller\order\OrderCenterController;

// ===== 后台路由（需管理员授权）=====
Route::group(function () {
    /** 中台商品 */
    Route::group('center/product', function () {
        Route::get('type_list', [CenterProductController::class, 'typeList'])->option(['real_name' => '产品类型列表']);
        Route::get('list', [CenterProductController::class, 'list'])->option(['real_name' => '统一商品列表']);
        Route::get('search', [CenterProductController::class, 'search'])->option(['real_name' => '商品搜索']);
        Route::get('detail/:id', [CenterProductController::class, 'detail'])->option(['real_name' => '商品详情'])->pattern(['id' => '\d+']);
    })->option(['parent' => 'center', 'cate_name' => '中台商品管理']);

    /** 中台订单 */
    Route::group('center/order', function () {
        Route::get('list', [OrderCenterController::class, 'index'])->option(['real_name' => '统一订单列表']);
    })->option(['parent' => 'center', 'cate_name' => '中台订单管理']);
})->middleware([
    AllowOriginMiddleware::class,
    AdminAuthTokenMiddleware::class,
])->option(['mark' => 'center_admin', 'mark_name' => '中台后台管理']);
