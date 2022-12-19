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
 * 文件下载、导出相关路由
 */
Route::group(function () {

    //下载备份记录表
    Route::get('backup/download', 'v1.system.SystemDatabackup/downloadFile')->option(['real_name' => '下载表备份记录']);
    //首页统计数据
    Route::get('home/header', 'Common/homeStatics')->option(['real_name' => '首页统计数据']);
    //首页订单图表
    Route::get('home/order', 'Common/orderChart')->option(['real_name' => '首页订单图表']);
    //首页用户图表
    Route::get('home/user', 'Common/userChart')->option(['real_name' => '首页用户图表']);
    //
    Route::get('home/rank', 'Common/purchaseRanking')->option(['real_name' => '首页交易额排行']);
    // 消息提醒
    Route::get('jnotice', 'Common/jnotice')->option(['real_name' => '消息提醒']);
    //验证授权
    Route::get('check_auth', 'Common/auth')->option(['real_name' => '验证授权']);
    //申请授权
    Route::post('auth_apply', 'Common/auth_apply')->option(['real_name' => '申请授权']);
    //授权
    Route::get('auth', 'Common/auth')->option(['real_name' => '授权信息']);
    //获取左侧菜单
    Route::get('menus', 'v1.setting.SystemMenus/menus')->option(['real_name' => '左侧菜单']);
    //获取搜索菜单列表
    Route::get('menusList', 'Common/menusList')->option(['real_name' => '搜索菜单列表']);
    //获取logo
    Route::get('logo', 'Common/getLogo')->option(['real_name' => '获取logo']);
    //查询版权
    Route::get('copyright', 'Common/copyright')->option(['real_name' => '申请版权']);
    //保存版权
    Route::post('copyright', 'Common/saveCopyright')->option(['real_name' => '保存版权']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);

