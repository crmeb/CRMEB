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
 * 分销管理 相关路由
 */
Route::group('statistic', function () {

    /** 交易统计 */
    Route::group(function () {
        //今日营业额统计
        Route::get('trade/top_trade', [\app\adminapi\controller\statistic\TradeStatisticController::class, 'topTrade'])->option(['real_name' => '今日营业额统计']);
        Route::get('trade/bottom_trade', [\app\adminapi\controller\statistic\TradeStatisticController::class, 'bottomTrade'])->option(['real_name' => '交易统计底部数据']);
    })->option(['parent' => 'statistic', 'cate_name' => '交易统计']);

    /** 订单统计 */
    Route::group(function () {
        //订单基础
        Route::get('order/get_basic', [\app\adminapi\controller\statistic\OrderStatisticController::class, 'getBasic'])->option(['real_name' => '订单基础统计']);
        //订单趋势
        Route::get('order/get_trend', [\app\adminapi\controller\statistic\OrderStatisticController::class, 'getTrend'])->option(['real_name' => '订单趋势']);
        //订单来源
        Route::get('order/get_channel', [\app\adminapi\controller\statistic\OrderStatisticController::class, 'getChannel'])->option(['real_name' => '订单来源']);
        //订单类型
        Route::get('order/get_type', [\app\adminapi\controller\statistic\OrderStatisticController::class, 'getType'])->option(['real_name' => '订单类型']);
    })->option(['parent' => 'statistic', 'cate_name' => '订单统计']);

    /** 资金流水 */
    Route::group(function () {
        Route::get('flow/get_list', [\app\adminapi\controller\statistic\FlowStatisticController::class, 'getFlowList'])->option(['real_name' => '资金流水']);
        Route::post('flow/set_mark/:id', [\app\adminapi\controller\statistic\FlowStatisticController::class, 'setMark'])->option(['real_name' => '设置备注']);
        Route::get('flow/get_record', [\app\adminapi\controller\statistic\FlowStatisticController::class, 'getFlowRecord'])->option(['real_name' => '账单记录']);
    })->option(['parent' => 'statistic', 'cate_name' => '资金流水']);

    /** 余额统计 */
    Route::group(function () {
        //余额基础统计
        Route::get('balance/get_basic', [\app\adminapi\controller\statistic\BalanceStatisticController::class, 'getBasic'])->option(['real_name' => '余额基础统计']);
        //余额趋势
        Route::get('balance/get_trend', [\app\adminapi\controller\statistic\BalanceStatisticController::class, 'getTrend'])->option(['real_name' => '余额趋势']);
        //余额来源
        Route::get('balance/get_channel', [\app\adminapi\controller\statistic\BalanceStatisticController::class, 'getChannel'])->option(['real_name' => '余额来源']);
        //余额消耗
        Route::get('balance/get_type', [\app\adminapi\controller\statistic\BalanceStatisticController::class, 'getType'])->option(['real_name' => '余额消耗']);
    })->option(['parent' => 'statistic', 'cate_name' => '余额统计']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'statistic', 'mark_name' => '程序统计']);
