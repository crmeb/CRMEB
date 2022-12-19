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
 * 导出excel相关路由
 */
Route::group('export', function () {
    //用户资金监控
    Route::get('userFinance', 'v1.export.ExportExcel/userFinance')->option(['real_name' => '用户资金导出']);
    //用户佣金
    Route::get('userCommission', 'v1.export.ExportExcel/userCommission')->option(['real_name' => '用户佣金导出']);
    //用户积分
    Route::get('userPoint', 'v1.export.ExportExcel/userPoint')->option(['real_name' => '用户积分导出']);
    //用户充值
    Route::get('userRecharge', 'v1.export.ExportExcel/userRecharge')->option(['real_name' => '用户充值导出']);
    //分销用户推广列表
    Route::get('userAgent', 'v1.export.ExportExcel/userAgent')->option(['real_name' => '分销员推广列表导出']);
    //微信用户
    Route::get('wechatUser', 'v1.export.ExportExcel/wechatUser')->option(['real_name' => '微信用户导出']);
    //商铺砍价活动
    Route::get('storeBargain', 'v1.export.ExportExcel/storeBargain')->option(['real_name' => '砍价商品导出']);
    //商铺拼团
    Route::get('storeCombination', 'v1.export.ExportExcel/storeCombination')->option(['real_name' => '拼团商品导出']);
    //商铺秒杀
    Route::get('storeSeckill', 'v1.export.ExportExcel/storeSeckill')->option(['real_name' => '秒杀商品导出']);
    //商铺产品
    Route::get('storeProduct', 'v1.export.ExportExcel/storeProduct')->option(['real_name' => '商品导出']);
    //商铺订单
    Route::get('storeOrder', 'v1.export.ExportExcel/storeOrder')->option(['real_name' => '订单导出']);
    //商铺提货点
    Route::get('storeMerchant', 'v1.export.ExportExcel/storeMerchant')->option(['real_name' => '提货点导出']);
    //导出会员卡
    Route::get('memberCard/:id', 'v1.export.ExportExcel/memberCard')->option(['real_name' => '会员卡导出']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
