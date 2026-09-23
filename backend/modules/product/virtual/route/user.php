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
 * 网盘商品用户端路由
 * 路由前缀：/api/virtual/
 */

use think\facade\Route;
use app\http\middleware\AllowOriginMiddleware;
use app\api\middleware\StationOpenMiddleware;
use app\api\middleware\AuthTokenMiddleware;
use app\api\middleware\BlockerMiddleware;
use modules\product\virtual\controller\user\ProductController;
use modules\product\virtual\controller\user\OrderController;
use app\api\controller\v1\order\CenterOrderController;

/** 无需授权接口 */
Route::group('virtual', function () {
    /** 商品 */
    Route::group(function () {
        Route::get('product/:id', [ProductController::class, 'read'])->option(['real_name' => '网盘商品详情']);
    })->option(['parent' => 'virtual', 'cate_name' => '网盘商品']);
})->middleware(AllowOriginMiddleware::class)
    ->middleware(StationOpenMiddleware::class)
    ->middleware(AuthTokenMiddleware::class, false)
    ->option(['mark' => 'virtual_anon', 'mark_name' => '网盘商品浏览']);

/** 需授权接口 */
Route::group('virtual', function () {
    /** 订单 */
    Route::group(function () {
        Route::get('order/:order_no', [OrderController::class, 'read'])->option(['real_name' => '网盘商品订单详情']);
    })->option(['parent' => 'virtual', 'cate_name' => '网盘商品订单']);

    /** 下单 */
    Route::group(function () {
        Route::post('checkout/confirm', [CenterOrderController::class, 'confirm'])->middleware(BlockerMiddleware::class)->option(['real_name' => '网盘商品确认订单']);
        Route::post('checkout/create', [CenterOrderController::class, 'create'])->middleware(BlockerMiddleware::class)->option(['real_name' => '网盘商品创建订单']);
    })->option(['parent' => 'virtual', 'cate_name' => '网盘商品下单']);
})->middleware(AllowOriginMiddleware::class)
    ->middleware(StationOpenMiddleware::class)
    ->middleware(AuthTokenMiddleware::class, true)
    ->option(['mark' => 'virtual_auth', 'mark_name' => '网盘商品交易']);
