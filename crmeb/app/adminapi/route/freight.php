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
 * 商户管理 相关路由
 */
Route::group('freight', function () {
    //物流公司资源路由
    Route::resource('express', 'v1.freight.Express')->name('ExpressResource')->option(['real_name' => '物流公司']);
    //修改状态
    Route::put('express/set_status/:id/:status', 'v1.freight.Express/set_status')->option(['real_name' => '修改物流公司状态']);
    //同步物流快递公司
    Route::get('express/sync_express', 'v1.freight.Express/syncExpress')->option(['real_name' => '同步物流公司']);
    //物流配置编辑表单
    Route::get('config/edit_basics', 'v1.setting.SystemConfig/edit_basics')->option(['real_name' => '物流配置编辑表单']);
    //物流配置保存数据
    Route::post('config/save_basics', 'v1.setting.SystemConfig/save_basics')->option(['real_name' => '物流配置保存数据']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
