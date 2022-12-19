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
 * 财务模块 相关路由
 */
Route::group('finance', function () {
    //筛选类型
    Route::get('finance/bill_type', 'v1.finance.Finance/bill_type')->option(['real_name' => '资金记录类型']);
    //资金记录
    Route::get('finance/list', 'v1.finance.Finance/list')->option(['real_name' => '资金记录列表']);
    //佣金记录
    Route::get('finance/commission_list', 'v1.finance.Finance/get_commission_list')->option(['real_name' => '佣金记录列表']);
    //佣金详情用户信息
    Route::get('finance/user_info/:id', 'v1.finance.Finance/user_info')->option(['real_name' => '佣金详情用户信息']);
    //佣金提现记录个人列表
    Route::get('finance/extract_list/:id', 'v1.finance.Finance/get_extract_list')->option(['real_name' => '佣金提现记录个人列表']);
    //申请列表
    Route::get('extract', 'v1.finance.UserExtract/index')->option(['real_name' => '提现申请列表']);
    //编辑表单
    Route::get('extract/:id/edit', 'v1.finance.UserExtract/edit')->option(['real_name' => '提现记录修改表单']);
    //保存修改
    Route::put('extract/:id', 'v1.finance.UserExtract/update')->option(['real_name' => '提现记录修改']);
    //拒绝申请
    Route::put('extract/refuse/:id', 'v1.finance.UserExtract/refuse')->option(['real_name' => '拒绝提现申请']);
    //通过申请
    Route::put('extract/adopt/:id', 'v1.finance.UserExtract/adopt')->option(['real_name' => '通过提现申请']);
    //充值记录列表
    Route::get('recharge', 'v1.finance.UserRecharge/index')->option(['real_name' => '充值记录列表']);
    //删除记录
    Route::delete('recharge/:id', 'v1.finance.UserRecharge/delete')->option(['real_name' => '删除充值记录']);
    //获取用户充值数据
    Route::get('recharge/user_recharge', 'v1.finance.UserRecharge/user_recharge')->option(['real_name' => '获取用户充值数据']);
    //退款表单
    Route::get('recharge/:id/refund_edit', 'v1.finance.UserRecharge/refund_edit')->option(['real_name' => '充值退款表单']);
    //退款
    Route::put('recharge/:id', 'v1.finance.UserRecharge/refund_update')->option(['real_name' => '充值退款']);

    /** 余额记录 */
    Route::get('balance/list', 'v1.finance.UserBalance/balanceList')->option(['real_name' => '余额记录列表']);
    Route::post('balance/set_mark/:id', 'v1.finance.UserBalance/balanceRecordRemark')->option(['real_name' => '余额记录备注']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
