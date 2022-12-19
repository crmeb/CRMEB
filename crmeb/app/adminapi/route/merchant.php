<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
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
    Route::get('store', 'v1.merchant.SystemStore/index')->option(['real_name' => '门店列表']);
    //门店列表数量
    Route::get('store/get_header', 'v1.merchant.SystemStore/get_header')->option(['real_name' => '门店列表头部数据']);
    //门店列表数量
    Route::put('store/set_show/:id/:is_show', 'v1.merchant.SystemStore/set_show')->option(['real_name' => '门店上下架']);
    //门店列表数量
    Route::delete('store/del/:id', 'v1.merchant.SystemStore/delete')->option(['real_name' => '门店删除']);
    //位置选择
    Route::get('store/address', 'v1.merchant.SystemStore/select_address')->option(['real_name' => '门店位置选择']);
    //门店设置详情
    Route::get('store/get_info/:id', 'v1.merchant.SystemStore/get_info')->option(['real_name' => '门店详情']);
    //保存修改门店信息
    Route::post('store/:id', 'v1.merchant.SystemStore/save')->option(['real_name' => '保存修改门店信息']);
    //获取店员列表
    Route::get('store_staff', 'v1.merchant.SystemStoreStaff/index')->option(['real_name' => '获取门店店员列表']);
    //添加店员表单
    Route::get('store_staff/create', 'v1.merchant.SystemStoreStaff/create')->option(['real_name' => '添加门店店员表单']);
    //门店搜索列表
    Route::get('store_list', 'v1.merchant.SystemStoreStaff/store_list')->option(['real_name' => '门店搜索列表']);
    //修改店员状态
    Route::put('store_staff/set_show/:id/:is_show', 'v1.merchant.SystemStoreStaff/set_show')->option(['real_name' => '修改店员状态']);
    //修改店员表单
    Route::get('store_staff/:id/edit', 'v1.merchant.SystemStoreStaff/edit')->option(['real_name' => '修改店员表单']);
    //保存店员
    Route::post('store_staff/save/:id', 'v1.merchant.SystemStoreStaff/save')->option(['real_name' => '保存店员']);
    //删除店员
    Route::delete('store_staff/del/:id', 'v1.merchant.SystemStoreStaff/delete')->option(['real_name' => '删除店员']);
    //获取核销订单列表
    Route::get('verify_order', 'v1.merchant.SystemVerifyOrder/list')->option(['real_name' => '获取核销订单列表']);
    //获取核销订单头部
    Route::get('verify_badge', 'v1.merchant.SystemVerifyOrder/getVerifyBadge')->option(['real_name' => '获取核销订单头部']);
    //获取核销订单头部
    Route::get('verify/spread_info/:uid', 'v1.merchant.SystemVerifyOrder/order_spread_user')->option(['real_name' => '核销订单推荐人信息']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
