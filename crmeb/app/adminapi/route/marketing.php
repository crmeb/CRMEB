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

    //砍价商品列表
    Route::get('bargain', 'v1.marketing.StoreBargain/index');
    //砍价详情
    Route::get('bargain/:id', 'v1.marketing.StoreBargain/read');
    //保存新增或编辑砍价
    Route::post('bargain/:id', 'v1.marketing.StoreBargain/save');
    //删除砍价
    Route::delete('bargain/:id', 'v1.marketing.StoreBargain/delete');
    //修改砍价状态
    Route::put('bargain/set_status/:id/:status', 'v1.marketing.StoreBargain/set_status');
    //砍价列表
    Route::get('bargain_list', 'v1.marketing.StoreBargain/bargainList');
    //砍价人列表
    Route::get('bargain_list_info/:id', 'v1.marketing.StoreBargain/bargainListInfo');

    //拼团商品列表
    Route::get('combination', 'v1.marketing.StoreCombination/index');
    //拼团统计
    Route::get('combination/statistics', 'v1.marketing.StoreCombination/statistics');
    //拼团导出
    Route::get('combination/export', 'v1.marketing.StoreCombination/save_excel');
    //拼团商品详情
    Route::get('combination/:id', 'v1.marketing.StoreCombination/read');
    //保存新疆或编辑
    Route::post('combination/:id', 'v1.marketing.StoreCombination/save');
    //删除
    Route::delete('combination/:id', 'v1.marketing.StoreCombination/delete');
    //修改拼团状态
    Route::put('combination/set_status/:id/:status', 'v1.marketing.StoreCombination/set_status');
    //拼团列表
    Route::get('combination/combine/list', 'v1.marketing.StoreCombination/combine_list');
    //拼团人列表
    Route::get('combination/order_pink/:id', 'v1.marketing.StoreCombination/order_pink');

    //秒杀列表
    Route::get('seckill', 'v1.marketing.StoreSeckill/index');
    //秒杀时间段列表
    Route::get('seckill/time_list', 'v1.marketing.StoreSeckill/time_list');
    //秒杀导出
    Route::get('seckill/export', 'v1.marketing.StoreSeckill/save_excel');
    //秒杀详情
    Route::get('seckill/:id', 'v1.marketing.StoreSeckill/read');
    //秒杀保存新增或编辑
    Route::post('seckill/:id', 'v1.marketing.StoreSeckill/save');
    //秒杀删除
    Route::delete('seckill/:id', 'v1.marketing.StoreSeckill/delete');
    //修改秒杀状态
    Route::put('seckill/set_status/:id/:status', 'v1.marketing.StoreSeckill/set_status');

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
