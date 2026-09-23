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

/**
 * 中台统一管理路由 - 用户端
 * 与 app/adminapi/route/center.php 的后台部分对应
 */

use think\facade\Route;
use app\http\middleware\AllowOriginMiddleware;
use app\api\middleware\StationOpenMiddleware;
use app\api\middleware\AuthTokenMiddleware;

/** 无需授权接口 */
Route::group(function () {
    /** 中台商品 */
    Route::group('center/product', function () {
        Route::get('type_list', [\app\api\controller\v1\product\CenterProductController::class, 'typeList'])->option(['real_name' => '产品类型列表']);
        Route::get('list', [\app\api\controller\v1\product\CenterProductController::class, 'list'])->option(['real_name' => '统一商品列表']);
        Route::get('search', [\app\api\controller\v1\product\CenterProductController::class, 'search'])->option(['real_name' => '商品搜索']);
    })->option(['parent' => 'center', 'cate_name' => '中台商品浏览']);
})->middleware(AllowOriginMiddleware::class)
    ->middleware(StationOpenMiddleware::class)
    ->middleware(AuthTokenMiddleware::class, false)
    ->option(['mark' => 'center_anon', 'mark_name' => '中台商品浏览']);

/** 需授权接口 */
Route::group(function () {
    /** 中台订单 */
    Route::group('center/order', function () {
        Route::get('list', [\app\api\controller\v1\order\CenterOrderController::class, 'list'])->option(['real_name' => '我的订单列表']);
        Route::get('stat', [\app\api\controller\v1\order\CenterOrderController::class, 'stat'])->option(['real_name' => '我的订单统计']);
        Route::get('detail/:orderNo', [\app\api\controller\v1\order\CenterOrderController::class, 'detail'])->option(['real_name' => '订单详情']);
        Route::get('confirm', [\app\api\controller\v1\order\CenterOrderController::class, 'confirm'])->option(['real_name' => '订单确认']);
        Route::post('computed', [\app\api\controller\v1\order\CenterOrderController::class, 'computed'])->option(['real_name' => '计算订单金额']);
        Route::post('create', [\app\api\controller\v1\order\CenterOrderController::class, 'create'])->option(['real_name' => '统一下单']);
    })->option(['parent' => 'center', 'cate_name' => '中台订单']);
})->middleware(AllowOriginMiddleware::class)
    ->middleware(StationOpenMiddleware::class)
    ->middleware(AuthTokenMiddleware::class, true)
    ->option(['mark' => 'center_auth', 'mark_name' => '中台订单交易']);
