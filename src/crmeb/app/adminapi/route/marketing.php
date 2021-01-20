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
 * 优惠卷，砍价，拼团，秒杀 路由
 */
Route::group('marketing', function () {
    //已发布优惠券列表
    Route::get('coupon/released', 'v1.marketing.StoreCouponIssue/index');
    //添加优惠券
    Route::post('coupon/save_coupon', 'v1.marketing.StoreCouponIssue/saveCoupon');
    //修改优惠券状态
    Route::get('coupon/status/:id/:status', 'v1.marketing.StoreCouponIssue/status');
    //一键复制优惠券
    Route::get('coupon/copy/:id', 'v1.marketing.StoreCouponIssue/copy');
    //发送优惠券列表
    Route::get('coupon/grant', 'v1.marketing.StoreCouponIssue/index');


    //优惠券相关 资源路由
    Route::get('coupon/list', 'v1.marketing.StoreCoupon/index');
    //优惠卷添加
    Route::get('coupon/create/:type', 'v1.marketing.StoreCoupon/create');
    //优惠卷数据添加添加
    Route::post('coupon/save', 'v1.marketing.StoreCoupon/save');
    //优惠卷修改
    Route::delete('coupon/del/:id', 'v1.marketing.StoreCoupon/delete');
    //修改状态
    Route::put('coupon/status/:id', 'v1.marketing.StoreCoupon/status');
    //发布优惠券表单
    Route::get('coupon/issue/:id', 'v1.marketing.StoreCoupon/issue');
    //发布优惠券
    Route::post('coupon/issue/:id', 'v1.marketing.StoreCoupon/update_issue');


    //已发布优惠券删除
    Route::delete('coupon/released/:id', 'v1.marketing.StoreCouponIssue/delete');
    //已发布优惠券修改状态表单
    Route::get('coupon/released/:id/status', 'v1.marketing.StoreCouponIssue/edit');
    //已发布优惠券修改状态
    Route::put('coupon/released/status/:id', 'v1.marketing.StoreCouponIssue/status');
    //已发布优惠券领取记录
    Route::get('coupon/released/issue_log/:id', 'v1.marketing.StoreCouponIssue/issue_log');
    //会员领取记录
    Route::get('coupon/user', 'v1.marketing.StoreCouponUser/index');
    //发送优惠券
    Route::post('coupon/user/grant', 'v1.marketing.StoreCouponUser/grant');


    //积分日志列表
    Route::get('integral', 'v1.marketing.UserPoint/index');
    //导出积分日志
    Route::get('integral/export', 'v1.marketing.UserPoint/export');
    //积分日志头部数据
    Route::get('integral/statistics', 'v1.marketing.UserPoint/integral_statistics');
    //积分配置编辑表单
    Route::get('integral_config/edit_basics', 'v1.setting.SystemConfig/edit_basics');
    //积分配置保存数据
    Route::post('integral_config/save_basics', 'v1.setting.SystemConfig/save_basics');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
