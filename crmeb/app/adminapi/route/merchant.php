<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
use think\facade\Route;

/**
 * 运费模板 相关路由
 */
Route::group('merchant', function () {
    //门店设置详情
    Route::get('store', 'v1.merchant.SystemStore/index');
    //门店列表数量
    Route::get('store/get_header', 'v1.merchant.SystemStore/get_header');
    //门店列表数量
    Route::put('store/set_show/:id/:is_show', 'v1.merchant.SystemStore/set_show');
    //门店列表数量
    Route::delete('store/del/:id', 'v1.merchant.SystemStore/delete');
    //位置选择
    Route::get('store/address', 'v1.merchant.SystemStore/select_address');
    //门店设置详情
    Route::get('store/get_info/:id', 'v1.merchant.SystemStore/get_info');
    //保存修改门店信息
    Route::post('store/:id', 'v1.merchant.SystemStore/save');
    //获取店员列表
    Route::get('store_staff', 'v1.merchant.SystemStoreStaff/index');
    //添加店员表单
    Route::get('store_staff/create', 'v1.merchant.SystemStoreStaff/create');
    //门店搜索列表
    Route::get('store_list', 'v1.merchant.SystemStoreStaff/store_list');
    //修改店员状态
    Route::put('store_staff/set_show/:id/:is_show', 'v1.merchant.SystemStoreStaff/set_show');
    //修改店员表单
    Route::get('store_staff/:id/edit', 'v1.merchant.SystemStoreStaff/edit');
    //保存店员
    Route::post('store_staff/save/:id', 'v1.merchant.SystemStoreStaff/save');
    //删除店员
    Route::delete('store_staff/del/:id', 'v1.merchant.SystemStoreStaff/delete');
    //获取核销订单列表
    Route::get('verify_order', 'v1.merchant.SystemVerifyOrder/list');
    //获取核销订单头部
    Route::get('verify_badge', 'v1.merchant.SystemVerifyOrder/getVerifyBadge');
    //获取核销订单头部
    Route::get('verify/spread_info/:uid', 'v1.merchant.SystemVerifyOrder/order_spread_user');
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
