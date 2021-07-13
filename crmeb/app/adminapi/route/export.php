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
 * 导出excel相关路由
 */
Route::group('export', function () {
    //用户资金监控
    Route::get('userFinance', 'v1.export.ExportExcel/userFinance');
    //用户佣金
    Route::get('userCommission', 'v1.export.ExportExcel/userCommission');
    //用户积分
    Route::get('userPoint', 'v1.export.ExportExcel/userPoint');
    //用户充值
    Route::get('userRecharge', 'v1.export.ExportExcel/userRecharge');
    //分销用户推广列表
    Route::get('userAgent', 'v1.export.ExportExcel/userAgent');
    //微信用户
    Route::get('wechatUser', 'v1.export.ExportExcel/wechatUser');
    //商铺砍价活动
    Route::get('storeBargain', 'v1.export.ExportExcel/storeBargain');
    //商铺拼团
    Route::get('storeCombination', 'v1.export.ExportExcel/storeCombination');
    //商铺秒杀
    Route::get('storeSeckill', 'v1.export.ExportExcel/storeSeckill');
    //商铺产品
    Route::get('storeProduct', 'v1.export.ExportExcel/storeProduct');
    //商铺订单
    Route::get('storeOrder', 'v1.export.ExportExcel/storeOrder');
    //商铺提货点
    Route::get('storeMerchant', 'v1.export.ExportExcel/storeMerchant');
    //导出会员卡
    Route::get('memberCard/:id', 'v1.export.ExportExcel/memberCard');
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
