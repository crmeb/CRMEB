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
 * 消息通知管理、模板消息（列表，通知，添加，编辑）相关路由
 *
 * 注意：旧版短信接口（公共短信模板/剩余条数/购买套餐/支付码）已随 v8 驱动化架构下线，
 * 短信管理统一走 driver 短信驱动中心（/adminapi/sms_driver/*）。
 */
Route::group('notify', function () {

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'notify', 'mark_name' => '消息通知']);
