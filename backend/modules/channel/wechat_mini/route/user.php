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
