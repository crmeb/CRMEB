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
 * 运费模板 相关路由
 */
Route::group('merchant', function () {

    /** 门店 */
    Route::group(function () {
        //门店设置详情
        Route::get('store', [\app\adminapi\controller\merchant\SystemStoreController::class, 'index'])->option(['real_name' => '门店列表']);
        //门店列表数量
        Route::get('store/get_header', [\app\adminapi\controller\merchant\SystemStoreController::class, 'getHeader'])->option(['real_name' => '门店列表头部数据']);
        //门店列表数量
        Route::put('store/set_show/:id/:is_show', [\app\adminapi\controller\merchant\SystemStoreController::class, 'setShow'])->option(['real_name' => '门店上下架']);
        //门店列表数量
        Route::delete('store/del/:id', [\app\adminapi\controller\merchant\SystemStoreController::class, 'delete'])->option(['real_name' => '门店删除']);
        //位置选择
        Route::get('store/address', [\app\adminapi\controller\merchant\SystemStoreController::class, 'selectAddress'])->option(['real_name' => '门店位置选择']);
        //门店设置详情
        Route::get('store/get_info/:id', [\app\adminapi\controller\merchant\SystemStoreController::class, 'getInfo'])->option(['real_name' => '门店详情']);
        //保存修改门店信息
        Route::post('store/:id', [\app\adminapi\controller\merchant\SystemStoreController::class, 'save'])->option(['real_name' => '保存修改门店信息']);
    })->option(['parent' => 'merchant', 'cate_name' => '门店']);

    /** 店员 */
    Route::group(function () {
        //获取店员列表
        Route::get('store_staff', [\app\adminapi\controller\merchant\SystemStoreStaffController::class, 'index'])->option(['real_name' => '获取门店店员列表']);
        //添加店员表单
        Route::get('store_staff/create', [\app\adminapi\controller\merchant\SystemStoreStaffController::class, 'create'])->option(['real_name' => '添加门店店员表单']);
        //门店搜索列表
        Route::get('store_list', [\app\adminapi\controller\merchant\SystemStoreStaffController::class, 'storeList'])->option(['real_name' => '门店搜索列表']);
        //修改店员状态
        Route::put('store_staff/set_show/:id/:is_show', [\app\adminapi\controller\merchant\SystemStoreStaffController::class, 'setShow'])->option(['real_name' => '修改店员状态']);
        //修改店员表单
        Route::get('store_staff/:id/edit', [\app\adminapi\controller\merchant\SystemStoreStaffController::class, 'edit'])->option(['real_name' => '修改店员表单']);
        //保存店员
        Route::post('store_staff/save/:id', [\app\adminapi\controller\merchant\SystemStoreStaffController::class, 'save'])->option(['real_name' => '保存店员']);
        //删除店员
        Route::delete('store_staff/del/:id', [\app\adminapi\controller\merchant\SystemStoreStaffController::class, 'delete'])->option(['real_name' => '删除店员']);
    })->option(['parent' => 'merchant', 'cate_name' => '店员']);

    /** 核销订单 */
    Route::group(function () {
        //核销订单推荐人信息
        Route::get('verify/spread_info/:uid', [\app\adminapi\controller\merchant\SystemVerifyOrderController::class, 'orderSpreadUser'])->option(['real_name' => '核销订单推荐人信息']);
    })->option(['parent' => 'merchant', 'cate_name' => '核销订单']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'merchant', 'mark_name' => '门店核销']);
