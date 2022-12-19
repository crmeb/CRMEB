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
 * 优惠卷，砍价，拼团，秒杀 路由
 */
Route::group('marketing', function () {
    //已发布优惠券列表
    Route::get('coupon/released', 'v1.marketing.StoreCouponIssue/index')->option(['real_name' => '已发布优惠券列表']);
    //添加优惠券
    Route::post('coupon/save_coupon', 'v1.marketing.StoreCouponIssue/saveCoupon')->option(['real_name' => '添加优惠券']);
    //修改优惠券状态
    Route::get('coupon/status/:id/:status', 'v1.marketing.StoreCouponIssue/status')->option(['real_name' => '修改优惠券状态']);
    //一键复制优惠券
    Route::get('coupon/copy/:id', 'v1.marketing.StoreCouponIssue/copy')->option(['real_name' => '一键复制优惠券']);
    //发送优惠券列表
    Route::get('coupon/grant', 'v1.marketing.StoreCouponIssue/index')->option(['real_name' => '发送优惠券列表']);
    //已发布优惠券删除
    Route::delete('coupon/released/:id', 'v1.marketing.StoreCouponIssue/delete')->option(['real_name' => '已发布优惠券删除']);
    //已发布优惠券修改状态表单
    Route::get('coupon/released/:id/status', 'v1.marketing.StoreCouponIssue/edit')->option(['real_name' => '已发布优惠券修改状态表单']);
    //已发布优惠券修改状态
    Route::put('coupon/released/status/:id', 'v1.marketing.StoreCouponIssue/status')->option(['real_name' => '已发布优惠券修改状态']);
    //已发布优惠券领取记录
    Route::get('coupon/released/issue_log/:id', 'v1.marketing.StoreCouponIssue/issue_log')->option(['real_name' => '已发布优惠券领取记录']);
    //会员领取记录
    Route::get('coupon/user', 'v1.marketing.StoreCouponUser/index')->option(['real_name' => '会员领取记录']);
    //发送优惠券
    Route::post('coupon/user/grant', 'v1.marketing.StoreCouponUser/grant')->option(['real_name' => '发送优惠券']);

    //砍价商品列表
    Route::get('bargain', 'v1.marketing.StoreBargain/index')->option(['real_name' => '砍价商品列表']);
    //砍价详情
    Route::get('bargain/:id', 'v1.marketing.StoreBargain/read')->option(['real_name' => '砍价商品详情']);
    //保存新增或编辑砍价
    Route::post('bargain/:id', 'v1.marketing.StoreBargain/save')->option(['real_name' => '新增或编辑砍价商品']);
    //删除砍价
    Route::delete('bargain/:id', 'v1.marketing.StoreBargain/delete')->option(['real_name' => '删除砍价商品']);
    //修改砍价状态
    Route::put('bargain/set_status/:id/:status', 'v1.marketing.StoreBargain/set_status')->option(['real_name' => '修改砍价商品状态']);
    //砍价列表
    Route::get('bargain_list', 'v1.marketing.StoreBargain/bargainList')->option(['real_name' => '参与砍价列表']);
    //砍价人列表
    Route::get('bargain_list_info/:id', 'v1.marketing.StoreBargain/bargainListInfo')->option(['real_name' => '砍价人列表']);
    //砍价统计
    Route::get('bargain/statistics/head/:id', 'v1.marketing.StoreBargain/bargainStatistics')->option(['real_name' => '砍价统计']);
    //砍价列表
    Route::get('bargain/statistics/list/:id', 'v1.marketing.StoreBargain/bargainStatisticsList')->option(['real_name' => '砍价统计列表']);
    //砍价订单
    Route::get('bargain/statistics/order/:id', 'v1.marketing.StoreBargain/bargainStatisticsOrder')->option(['real_name' => '砍价统计订单']);

    //拼团商品列表
    Route::get('combination', 'v1.marketing.StoreCombination/index')->option(['real_name' => '拼团商品列表']);
    //拼团统计
    Route::get('combination/statistics', 'v1.marketing.StoreCombination/statistics')->option(['real_name' => '拼团商品统计']);
    //拼团商品详情
    Route::get('combination/:id', 'v1.marketing.StoreCombination/read')->option(['real_name' => '拼团商品详情']);
    //保存新疆或编辑
    Route::post('combination/:id', 'v1.marketing.StoreCombination/save')->option(['real_name' => '新增或编辑拼团商品']);
    //删除
    Route::delete('combination/:id', 'v1.marketing.StoreCombination/delete')->option(['real_name' => '删除拼团商品']);
    //修改拼团状态
    Route::put('combination/set_status/:id/:status', 'v1.marketing.StoreCombination/set_status')->option(['real_name' => '修改拼团商品状态']);
    //拼团列表
    Route::get('combination/combine/list', 'v1.marketing.StoreCombination/combine_list')->option(['real_name' => '参与拼团列表']);
    //拼团人列表
    Route::get('combination/order_pink/:id', 'v1.marketing.StoreCombination/order_pink')->option(['real_name' => '拼团人列表']);
    //拼团统计
    Route::get('combination/statistics/head/:id', 'v1.marketing.StoreCombination/combinationStatistics')->option(['real_name' => '拼团统计']);
    //拼团列表
    Route::get('combination/statistics/list/:id', 'v1.marketing.StoreCombination/combinationStatisticsList')->option(['real_name' => '拼团统计列表']);
    //拼团订单
    Route::get('combination/statistics/order/:id', 'v1.marketing.StoreCombination/combinationStatisticsOrder')->option(['real_name' => '拼团统计订单']);


    //秒杀列表
    Route::get('seckill', 'v1.marketing.StoreSeckill/index')->option(['real_name' => '秒杀商品列表']);
    //秒杀时间段列表
    Route::get('seckill/time_list', 'v1.marketing.StoreSeckill/time_list')->option(['real_name' => '秒杀时间段列表']);
    //秒杀详情
    Route::get('seckill/:id', 'v1.marketing.StoreSeckill/read')->option(['real_name' => '秒杀商品详情']);
    //秒杀保存新增或编辑
    Route::post('seckill/:id', 'v1.marketing.StoreSeckill/save')->option(['real_name' => '新增或编辑秒杀商品']);
    //秒杀删除
    Route::delete('seckill/:id', 'v1.marketing.StoreSeckill/delete')->option(['real_name' => '删除秒杀商品']);
    //修改秒杀状态
    Route::put('seckill/set_status/:id/:status', 'v1.marketing.StoreSeckill/set_status')->option(['real_name' => '修改秒杀商品状态']);
    //秒杀统计
    Route::get('seckill/statistics/head/:id', 'v1.marketing.StoreSeckill/seckillStatistics')->option(['real_name' => '秒杀统计']);
    //参与活动人员
    Route::get('seckill/statistics/people/:id','v1.marketing.StoreSeckill/seckillPeople')->option(['real_name' => '秒杀参与人']);
    //秒杀订单
    Route::get('seckill/statistics/order/:id','v1.marketing.StoreSeckill/seckillOrder')->option(['real_name' => '秒杀参与人']);

    //积分日志列表
    Route::get('integral', 'v1.marketing.UserPoint/index')->option(['real_name' => '积分日志列表']);
    //积分日志头部数据
    Route::get('integral/statistics', 'v1.marketing.UserPoint/integral_statistics')->option(['real_name' => '积分日志头部数据']);
    //积分配置编辑表单
    Route::get('integral_config/edit_basics', 'v1.setting.SystemConfig/edit_basics')->option(['real_name' => '积分配置编辑表单']);
    //积分配置保存数据
    Route::post('integral_config/save_basics', 'v1.setting.SystemConfig/save_basics')->option(['real_name' => '积分配置保存数据']);

    //预售列表
    Route::get('advance/index', 'v1.marketing.StoreAdvance/index')->option(['real_name' => '预售商品列表']);
    Route::get('advance/info/:id', 'v1.marketing.StoreAdvance/info')->option(['real_name' => '预售商品详情']);
    Route::post('advance/save/:id', 'v1.marketing.StoreAdvance/save')->option(['real_name' => '保存预售商品']);
    Route::delete('advance/:id', 'v1.marketing.StoreAdvance/del')->option(['real_name' => '删除预售商品']);
    Route::put('advance/set_status/:id/:status', 'v1.marketing.StoreAdvance/setStatus')->option(['real_name' => '上下架预售商品']);

    //积分商城
    //积分商品列表
    Route::get('integral_product', 'v1.marketing.integral.StoreIntegral/index')->option(['real_name' => '积分商品列表']);
    //积分商品新增或编辑
    Route::post('integral/:id', 'v1.marketing.integral.StoreIntegral/save')->option(['real_name' => '积分商品新增或编辑']);
    //积分商品详情
    Route::get('integral/:id', 'v1.marketing.integral.StoreIntegral/read')->option(['real_name' => '积分商品详情']);
    //积分商品删除
    Route::delete('integral/:id', 'v1.marketing.integral.StoreIntegral/delete')->option(['real_name' => '积分商品删除']);
    //修改积分商品状态
    Route::put('integral/set_show/:id/:is_show', 'v1.marketing.integral.StoreIntegral/set_show')->option(['real_name' => '修改积分商品状态']);
    //积分商城订单列表
    Route::get('integral/order/list', 'v1.marketing.integral.StoreIntegralOrder/lst')->option(['real_name' => '积分商城订单列表']);
    //积分商城订单数据
    Route::get('integral/order/chart', 'v1.marketing.integral.StoreIntegralOrder/chart')->option(['real_name' => '积分商城订单数据']);
    //积分商城订单详情数据
    Route::get('integral/order/info/:id', 'v1.marketing.integral.StoreIntegralOrder/order_info')->option(['real_name' => '积分商城订单详情数据']);
    //修改积分商品订单备注信息
    Route::put('integral/order/remark/:id', 'v1.marketing.integral.StoreIntegralOrder/remark')->option(['real_name' => '修改积分商品订单备注信息']);
    //获取积分订单状态
    Route::get('integral/order/status/:id', 'v1.marketing.integral.StoreIntegralOrder/status')->option(['real_name' => '获取积分订单状态']);
    //删除积分订单
    Route::delete('integral/order/del/:id', 'v1.marketing.integral.StoreIntegralOrder/del')->option(['real_name' => '删除积分订单']);
    //积分订单发送货
    Route::put('integral/order/delivery/:id', 'v1.marketing.integral.StoreIntegralOrder/update_delivery')->option(['real_name' => '积分订单发送货']);
    //获取积分订单配送信息表单
    Route::get('integral/order/distribution/:id', 'v1.marketing.integral.StoreIntegralOrder/distribution')->option(['real_name' => '获取积分订单配送信息表单']);
    //修改积分订单配送信息
    Route::put('integral/order/distribution/:id', 'v1.marketing.integral.StoreIntegralOrder/update_distribution')->option(['real_name' => '修改积分订单配送信息']);
    //积分订单确认收货
    Route::put('integral/order/take/:id', 'v1.marketing.integral.StoreIntegralOrder/take_delivery')->option(['real_name' => '积分订单确认收货']);
    //积分订单获取物流公司
    Route::get('integral/order/express_list', 'v1.marketing.integral.StoreIntegralOrder/express')->option(['real_name' => '积分订单获取物流公司']);
    //积分订单快递公司电子面单模版
    Route::get('integral/order/express/temp', 'v1.marketing.integral.StoreIntegralOrder/express_temp')->option(['real_name' => '积分订单快递公司电子面单模版']);
    //积分订单获取物流信息
    Route::get('integral/order/express/:id', 'v1.marketing.integral.StoreIntegralOrder/get_express')->option(['real_name' => '积分订单获取物流信息']);
    //打印积分订单
    Route::get('integral/order/print/:id', 'v1.marketing.integral.StoreIntegralOrder/order_print')->option(['real_name' => '打印积分订单']);
    //积分订单列表获取配送员
    Route::get('integral/order/delivery/list', 'v1.order.DeliveryService/get_delivery_list')->option(['real_name' => '积分订单列表获取配送员']);
    //积分订单获取面单默认配置信息
    Route::get('integral/order/sheet_info', 'v1.marketing.integral.StoreIntegralOrder/getDeliveryInfo')->option(['real_name' => '积分订单获取面单默认配置信息']);

    //九宫格抽奖
    //抽奖活动列表
    Route::get('lottery/list', 'v1.marketing.lottery.LuckLottery/index')->option(['real_name' => '抽奖活动列表']);
    //抽奖活动详情
    Route::get('lottery/detail/:id', 'v1.marketing.lottery.LuckLottery/detail')->option(['real_name' => '抽奖活动详情']);
    //抽奖活动详情
    Route::get('lottery/factor_info/:factor', 'v1.marketing.lottery.LuckLottery/factorInfo')->option(['real_name' => '抽奖活动详情']);
    //添加抽奖活动
    Route::post('lottery/add', 'v1.marketing.lottery.LuckLottery/add')->option(['real_name' => '添加抽奖活动']);
    //修改抽奖活动数据
    Route::put('lottery/edit/:id', 'v1.marketing.lottery.LuckLottery/edit')->option(['real_name' => '修改抽奖活动数据']);
    //删除抽奖活动
    Route::delete('lottery/del/:id', 'v1.marketing.lottery.LuckLottery/delete')->option(['real_name' => '删除抽奖活动']);
    //设置抽奖活动是否显示
    Route::post('lottery/set_status/:id/:status', 'v1.marketing.lottery.LuckLottery/setStatus')->option(['real_name' => '设置抽奖活动是否显示']);
    //抽奖记录列表
    Route::get('lottery/record/list', 'v1.marketing.lottery.LuckLotteryRecord/index')->option(['real_name' => '抽奖记录列表']);
    //抽奖中奖发货、备注处理
    Route::post('lottery/record/deliver', 'v1.marketing.lottery.LuckLotteryRecord/deliver')->option(['real_name' => '抽奖中奖发货、备注处理']);

    //积分记录
    Route::get('point_record', 'v1.marketing.integral.StorePointRecord/pointRecord')->option(['read_name' => '积分记录列表']);
    Route::post('point_record/remark/:id', 'v1.marketing.integral.StorePointRecord/pointRecordRemark')->option(['read_name' => '积分记录列表备注']);
    Route::get('point/get_basic', 'v1.marketing.integral.StorePointRecord/getBasic')->option(['read_name' => '积分统计基本信息']);
    Route::get('point/get_trend', 'v1.marketing.integral.StorePointRecord/getTrend')->option(['read_name' => '积分统计趋势图']);
    Route::get('point/get_channel', 'v1.marketing.integral.StorePointRecord/getChannel')->option(['read_name' => '积分来源统计']);
    Route::get('point/get_type', 'v1.marketing.integral.StorePointRecord/getType')->option(['read_name' => '积分消耗统计']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
