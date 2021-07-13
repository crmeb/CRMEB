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
 * 财务模块 相关路由
 */
Route::group('finance', function () {
    //筛选类型
    Route::get('finance/bill_type', 'v1.finance.Finance/bill_type');
    //资金记录
    Route::get('finance/list', 'v1.finance.Finance/list');
    //保存资金监控的excel表格
    Route::get('finance/export', 'v1.finance.Finance/save_bell_export');
    //佣金记录
    Route::get('finance/commission_list', 'v1.finance.Finance/get_commission_list');
    //佣金详情用户信息
    Route::get('finance/user_info/:id', 'v1.finance.Finance/user_info');
    //佣金提现记录个人列表
    Route::get('finance/extract_list/:id', 'v1.finance.Finance/get_extract_list');
    //申请列表
    Route::get('extract', 'v1.finance.UserExtract/index');
    //编辑表单
    Route::get('extract/:id/edit', 'v1.finance.UserExtract/edit');
    //保存修改
    Route::put('extract/:id', 'v1.finance.UserExtract/update');
    //拒绝申请
    Route::put('extract/refuse/:id', 'v1.finance.UserExtract/refuse');
    //通过申请
    Route::put('extract/adopt/:id', 'v1.finance.UserExtract/adopt');
    //充值记录列表
    Route::get('recharge', 'v1.finance.UserRecharge/index');
    //删除记录
    Route::delete('recharge/:id', 'v1.finance.UserRecharge/delete');
    //获取用户充值数据
    Route::get('recharge/user_recharge', 'v1.finance.UserRecharge/user_recharge');
    //退款表单
    Route::get('recharge/:id/refund_edit', 'v1.finance.UserRecharge/refund_edit');
    //退款
    Route::put('recharge/:id', 'v1.finance.UserRecharge/refund_update');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
