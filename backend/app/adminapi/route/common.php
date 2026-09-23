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
 * 文件下载、导出相关路由
 */
Route::group(function () {
    //下载备份记录表
    Route::get('backup/download', [\app\adminapi\controller\system\SystemDatabackupController::class, 'downloadFile'])->option(['real_name' => '下载表备份记录']);
    //首页统计数据
    Route::get('home/header', [\app\adminapi\controller\CommonController::class, 'homeStatics'])->option(['real_name' => '首页统计数据']);
    //首页订单图表
    Route::get('home/order', [\app\adminapi\controller\CommonController::class, 'orderChart'])->option(['real_name' => '首页订单图表']);
    //首页用户图表
    Route::get('home/user', [\app\adminapi\controller\CommonController::class, 'userChart'])->option(['real_name' => '首页用户图表']);
    //首页交易额排行
    Route::get('home/rank', [\app\adminapi\controller\CommonController::class, 'purchaseRanking'])->option(['real_name' => '首页交易额排行']);
    //消息提醒
    Route::get('jnotice', [\app\adminapi\controller\CommonController::class, 'jnotice'])->option(['real_name' => '消息提醒']);
    //验证授权
    Route::get('check_auth', [\app\adminapi\controller\CommonController::class, 'auth'])->option(['real_name' => '验证授权']);
        Route::post('auth_apply', [\app\adminapi\controller\CommonController::class, 'authApply'])->option(['real_name' => '申请授权']);
    //授权
    Route::get('auth', [\app\adminapi\controller\CommonController::class, 'auth'])->option(['real_name' => '授权信息']);
    //获取左侧菜单
    Route::get('menus', [\app\adminapi\controller\setting\SystemMenusController::class, 'menus'])->option(['real_name' => '左侧菜单']);
    //获取搜索菜单列表
    Route::get('menusList', [\app\adminapi\controller\CommonController::class, 'menusList'])->option(['real_name' => '搜索菜单列表']);
    //获取logo
    Route::get('logo', [\app\adminapi\controller\CommonController::class, 'getLogo'])->option(['real_name' => '获取logo']);
    //查询版权
    Route::get('copyright', [\app\adminapi\controller\CommonController::class, 'copyright'])->option(['real_name' => '申请版权']);
    //保存版权
    Route::post('copyright', [\app\adminapi\controller\CommonController::class, 'saveCopyright'])->option(['real_name' => '保存版权']);
    //后台菜单搜索
    Route::post('menusSearch', [\app\adminapi\controller\CommonController::class, 'menusSearch'])->option(['real_name' => '后台菜单搜索']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'common', 'mark_name' => '系统数据']);

