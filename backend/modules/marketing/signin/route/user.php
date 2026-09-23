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

// 每日签到用户端路由（自 app/api/route/v1.php 迁入，URL 保持不变）
use think\facade\Route;

// 需授权接口
Route::group(function () {
    //用户类 签到
    Route::get('sign/config', [\modules\marketing\signin\controller\user\v1\UserSignController::class, 'signConfig'])->name('signConfig')->option(['real_name' => '签到配置']);//签到配置
    Route::get('sign/list', [\modules\marketing\signin\controller\user\v1\UserSignController::class, 'signList'])->name('signList')->option(['real_name' => '签到列表']);//签到列表
    Route::get('sign/month', [\modules\marketing\signin\controller\user\v1\UserSignController::class, 'signMonth'])->name('signMonth')->option(['real_name' => '签到列表（年月）']);//签到列表（年月）
    Route::get('sign/remind/:status', [\modules\marketing\signin\controller\user\v1\UserSignController::class, 'signRemind'])->name('signRemind')->option(['real_name' => '签到提醒开关']);//签到提醒开关
    Route::post('sign/user', [\modules\marketing\signin\controller\user\v1\UserSignController::class, 'signUser'])->name('signUser')->option(['real_name' => '签到用户信息']);//签到用户信息
    Route::post('sign/integral', [\modules\marketing\signin\controller\user\v1\UserSignController::class, 'signIntegral'])->name('signIntegral')->middleware(\app\api\middleware\BlockerMiddleware::class)->option(['real_name' => '签到']);//签到
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, true)
    ->option(['mark' => 'sign', 'mark_name' => '签到']);
