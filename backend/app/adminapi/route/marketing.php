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
 * 优惠券，积分 路由
 */
Route::group('marketing', function () {

    /** 优惠券 */
    Route::group(function () {
        //已发布优惠券列表
        Route::get('coupon/released', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'index'])->option(['real_name' => '已发布优惠券列表']);
        //添加优惠券
        Route::post('coupon/save_coupon', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'saveCoupon'])->option(['real_name' => '添加优惠券']);
        //修改优惠券状态
        Route::get('coupon/status/:id/:status', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'status'])->option(['real_name' => '修改优惠券状态']);
        //一键复制优惠券
        Route::get('coupon/copy/:id', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'copy'])->option(['real_name' => '一键复制优惠券']);
        //发送优惠券列表
        Route::get('coupon/grant', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'index'])->option(['real_name' => '发送优惠券列表']);
        //已发布优惠券删除
        Route::delete('coupon/released/:id', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'delete'])->option(['real_name' => '已发布优惠券删除']);
        //已发布优惠券修改状态表单
        Route::get('coupon/released/:id/status', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'edit'])->option(['real_name' => '已发布优惠券修改状态表单']);
        //已发布优惠券修改状态
        Route::put('coupon/released/status/:id', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'status'])->option(['real_name' => '已发布优惠券修改状态']);
        //已发布优惠券领取记录
        Route::get('coupon/released/issue_log/:id', [\app\adminapi\controller\marketing\StoreCouponIssueController::class, 'issueLog'])->option(['real_name' => '已发布优惠券领取记录']);
        //会员领取记录
        Route::get('coupon/user', [\app\adminapi\controller\marketing\StoreCouponUserController::class, 'index'])->option(['real_name' => '会员领取记录']);
        //发送优惠券
        Route::post('coupon/user/grant', [\app\adminapi\controller\marketing\StoreCouponUserController::class, 'grant'])->option(['real_name' => '发送优惠券']);
    })->option(['parent' => 'marketing', 'cate_name' => '优惠券']);

    /** 积分活动 */
    Route::group(function () {
        //积分日志列表
        Route::get('integral', [\app\adminapi\controller\marketing\UserPointController::class, 'index'])->option(['real_name' => '积分日志列表']);
        //积分日志头部数据
        Route::get('integral/statistics', [\app\adminapi\controller\marketing\UserPointController::class, 'integralStatistics'])->option(['real_name' => '积分日志头部数据']);
        //积分订单列表获取配送员
        Route::get('integral/order/delivery/list', [\app\adminapi\controller\personnel\ServicePersonnelController::class, 'getDeliveryList'])->option(['real_name' => '积分订单列表获取配送员']);
        //积分记录列表
        Route::get('point_record', [\app\adminapi\controller\marketing\integral\StorePointRecordController::class, 'pointRecord'])->option(['real_name' => '积分记录列表']);
        //积分记录列表备注
        Route::post('point_record/remark/:id', [\app\adminapi\controller\marketing\integral\StorePointRecordController::class, 'pointRecordRemark'])->option(['real_name' => '积分记录列表备注']);
        //积分统计基本信息
        Route::get('point/get_basic', [\app\adminapi\controller\marketing\integral\StorePointRecordController::class, 'getBasic'])->option(['real_name' => '积分统计基本信息']);
        //积分统计趋势图
        Route::get('point/get_trend', [\app\adminapi\controller\marketing\integral\StorePointRecordController::class, 'getTrend'])->option(['real_name' => '积分统计趋势图']);
        //积分来源统计
        Route::get('point/get_channel', [\app\adminapi\controller\marketing\integral\StorePointRecordController::class, 'getChannel'])->option(['real_name' => '积分来源统计']);
        //积分消耗统计
        Route::get('point/get_type', [\app\adminapi\controller\marketing\integral\StorePointRecordController::class, 'getType'])->option(['real_name' => '积分消耗统计']);
    })->option(['parent' => 'marketing', 'cate_name' => '积分活动']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'marketing', 'mark_name' => '营销活动']);
