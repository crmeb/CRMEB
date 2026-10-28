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
 * 财务模块 相关路由
 */
Route::group('finance', function () {


    /** 资金记录 */
    Route::group(function () {
        //筛选类型
        Route::get('finance/bill_type', [\app\adminapi\controller\finance\FinanceController::class, 'billType'])->option(['real_name' => '资金记录类型']);
        //资金记录
        Route::get('finance/list', [\app\adminapi\controller\finance\FinanceController::class, 'list'])->option(['real_name' => '资金记录列表']);
        /** 余额记录 */
        Route::get('balance/list', [\app\adminapi\controller\finance\UserBalanceController::class, 'balanceList'])->option(['real_name' => '余额记录列表']);
        Route::post('balance/set_mark/:id', [\app\adminapi\controller\finance\UserBalanceController::class, 'balanceRecordRemark'])->option(['real_name' => '余额记录备注']);
    })->option(['parent' => 'finance', 'cate_name' => '资金记录']);

    /** 充值记录（系统自带，不依赖充值插件） */
    Route::group(function () {
        //充值记录列表
        Route::get('recharge', [\app\adminapi\controller\finance\UserRechargeController::class, 'index'])->option(['real_name' => '充值记录列表']);
        //删除记录
        Route::delete('recharge/:id', [\app\adminapi\controller\finance\UserRechargeController::class, 'delete'])->option(['real_name' => '删除充值记录']);
        //获取用户充值数据
        Route::get('recharge/user_recharge', [\app\adminapi\controller\finance\UserRechargeController::class, 'user_recharge'])->option(['real_name' => '获取用户充值数据']);
        //退款表单
        Route::get('recharge/:id/refund_edit', [\app\adminapi\controller\finance\UserRechargeController::class, 'refund_edit'])->option(['real_name' => '充值退款表单']);
        //退款
        Route::put('recharge/:id', [\app\adminapi\controller\finance\UserRechargeController::class, 'refund_update'])->option(['real_name' => '充值退款']);
    })->option(['parent' => 'finance', 'cate_name' => '充值']);



})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'finance', 'mark_name' => '财务管理']);
