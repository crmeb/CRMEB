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
 * 文件下载、导出相关路由
 */
Route::group(function () {

    //下载备份记录表
    Route::get('backup/download', 'v1.system.SystemDatabackup/downloadFile');
    //首页统计数据
    Route::get('home/header', 'Common/homeStatics');
    //首页订单图表
    Route::get('home/order', 'Common/orderChart');
    //首页用户图表
    Route::get('home/user', 'Common/userChart');
    //
    Route::get('home/rank', 'Common/purchaseRanking');
    // 消息提醒
    Route::get('jnotice', 'Common/jnotice');
    //获取左侧菜单
    Route::get('menus', 'v1.setting.SystemMenus/menus');
    //获取搜索菜单列表
    Route::get('menusList', 'Common/menusList');
    //获取logo
    Route::get('logo', 'Common/getLogo');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);

