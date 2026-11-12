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
declare(strict_types=1);

use think\facade\Route;

/**
 * 小程序用户端路由
 */
Route::group(function () {
    Route::post('wechat/mp_auth', [\modules\channel\wechat_mini\controller\AuthController::class, 'mpAuth'])->name('mpAuth')->option(['real_name' => '小程序登陆']);
    Route::get('wechat/get_logo', [\modules\channel\wechat_mini\controller\AuthController::class, 'getLogo'])->name('getLogo')->option(['real_name' => '小程序登陆授权展示logo']);
    Route::get('wechat/temp_ids', [\modules\channel\wechat_mini\controller\AuthController::class, 'tempIds'])->name('wechatTempIds')->option(['real_name' => '小程序订阅消息']);
    Route::get('wechat/live', [\modules\channel\wechat_mini\controller\AuthController::class, 'live'])->name('wechatLive')->option(['real_name' => '小程序直播列表']);
    Route::get('wechat/livePlaybacks/:id', [\modules\channel\wechat_mini\controller\AuthController::class, 'livePlaybacks'])->name('livePlaybacks')->option(['real_name' => '小程序直播回放']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class)->option(['mark' => 'mini', 'mark_name' => '小程序']);

Route::group('v2', function () {
    Route::group(function () {
        Route::get('routine/auth_type', [\modules\channel\wechat_mini\controller\AuthController::class, 'authType'])->option(['real_name' => '小程序页面登录类型']);
        Route::get('routine/auth_login', [\modules\channel\wechat_mini\controller\AuthController::class, 'authLogin'])->option(['real_name' => '小程序授权登录']);
        Route::post('routine/auth_binding_phone', [\modules\channel\wechat_mini\controller\AuthController::class, 'authBindingPhone'])->option(['real_name' => '小程序授权绑定手机号']);
        Route::post('routine/phone_login', [\modules\channel\wechat_mini\controller\AuthController::class, 'phoneLogin'])->option(['real_name' => '手机号直接登录']);
        Route::post('routine/binding_phone', [\modules\channel\wechat_mini\controller\AuthController::class, 'bindingPhone'])->option(['real_name' => '小程序授权后绑定手机号']);
    })->option(['mark' => 'wechat_auto', 'mark_name' => '微信授权']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->middleware(\app\api\middleware\StationOpenMiddleware::class);

// 小程序外链跳转（自 app/api/route/v1.php 迁入，URL 保持不变）
Route::group(function () {
    Route::get('get_scheme_url/:id', [\modules\channel\wechat_mini\controller\SchemeController::class, 'redirect'])->option(['real_name' => '小程序跳转url接口']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->option(['mark' => 'mini_scheme', 'mark_name' => '小程序外链']);

// 用户推广小程序码（需登录）
Route::group(function () {
    Route::get('user/routine_code', [\modules\channel\wechat_mini\controller\RoutineCodeController::class, 'spreadCode'])->name('getRoutineCode')->option(['real_name' => '小程序二维码']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, true)
    ->option(['mark' => 'mini_code', 'mark_name' => '小程序码']);

// 直播状态同步的旧 HTTP 定时入口，路径与原先一致
Route::group(function () {
    Route::get('crontab/live_product_status', [\modules\channel\wechat_mini\controller\CrontabController::class, 'syncGoodStatus'])->name('syncGoodStatus')->option(['real_name' => '更新直播商品状态']);
    Route::get('crontab/live_room_status', [\modules\channel\wechat_mini\controller\CrontabController::class, 'syncRoomStatus'])->name('syncRoomStatus')->option(['real_name' => '更新直播间状态']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, false)
    ->middleware(\app\api\middleware\CrontabKeyMiddleware::class)
    ->option(['mark' => 'mini_crontab', 'mark_name' => '小程序定时任务']);
