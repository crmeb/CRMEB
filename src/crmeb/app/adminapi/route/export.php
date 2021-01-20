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
    //用户积分
    Route::get('userPoint', 'v1.export.ExportExcel/userPoint');
    //微信用户
    Route::get('wechatUser', 'v1.export.ExportExcel/wechatUser');
    //商铺产品
    Route::get('storeProduct', 'v1.export.ExportExcel/storeProduct');
    //商铺订单
    Route::get('storeOrder', 'v1.export.ExportExcel/storeOrder');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
