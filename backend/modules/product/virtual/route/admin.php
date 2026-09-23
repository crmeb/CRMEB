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
 * 网盘商品后台管理路由
 * 路由前缀：/adminapi/virtual/
 */

use think\facade\Route;
use app\http\middleware\AllowOriginMiddleware;
use app\adminapi\middleware\AdminAuthTokenMiddleware;
use modules\product\virtual\controller\admin\ProductController;
use modules\product\virtual\controller\admin\OrderController;

Route::group('virtual', function () {
    /** 商品管理 */
    Route::group(function () {
        Route::get('product/list', [ProductController::class, 'list'])->option(['real_name' => '网盘商品列表']);
        Route::post('product', [ProductController::class, 'save'])->option(['real_name' => '创建网盘商品']);
        Route::put('product/:id', [ProductController::class, 'update'])->option(['real_name' => '更新网盘商品']);
        Route::delete('product/:id', [ProductController::class, 'delete'])->option(['real_name' => '删除网盘商品']);
        Route::get('product/:id', [ProductController::class, 'read'])->option(['real_name' => '网盘商品详情']);
        Route::put('product/status/:id/:status', [ProductController::class, 'setStatus'])->option(['real_name' => '上下架网盘商品']);
    })->option(['parent' => 'virtual', 'cate_name' => '网盘商品管理']);

    /** 网盘订单 */
    Route::group(function () {
        Route::get('order/list', [OrderController::class, 'list'])->option(['real_name' => '网盘商品订单列表']);
        Route::get('order/:order_no', [OrderController::class, 'read'])->option(['real_name' => '网盘商品订单详情']);
    })->option(['parent' => 'virtual', 'cate_name' => '网盘订单']);
})->middleware([
    AllowOriginMiddleware::class,
    AdminAuthTokenMiddleware::class,
])->option(['mark' => 'virtual', 'mark_name' => '网盘商品管理']);
