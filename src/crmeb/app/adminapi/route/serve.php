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
 * 服务平台路由
 */
Route::group('serve', function () {
    //平台登录
    Route::post('login', 'v1.serve.Login/login');
    //验证码
    Route::post('captcha', 'v1.serve.Login/captcha');
    //注册
    Route::post('register', 'v1.serve.Login/register');
    //开通电子面单
    Route::post('opn_express', 'v1.serve.Serve/openExpress');
    //获取用户信息
    Route::get('info', 'v1.serve.Serve/getUserInfo');
    //获取列表模板
    Route::get('meal_list', 'v1.serve.Serve/mealList');
    //获取支付
    Route::post('pay_meal', 'v1.serve.Serve/payMeal');
    //开通短信服务
    Route::get('sms/open', 'v1.serve.Sms/openServe');
    //开通其他服务
    Route::get('open', 'v1.serve.Serve/openServe');
    //修改签名
    Route::put('sms/sign', 'v1.serve.Sms/editSign');
    //获取短信模板
    Route::get('sms/temps', 'v1.serve.Sms/temps');
    //申请模板
    Route::post('sms/apply', 'v1.serve.Sms/apply');
    //获取申请记录
    Route::get('sms/apply_record', 'v1.serve.Sms/applyRecord');
    //记录
    Route::get('record', 'v1.serve.Serve/getRecord');
    //获取全部物流公司
    Route::get('export_all', 'v1.serve.Export/getExportAll');
    //获取物流公司模板
    Route::get('export_temp', 'v1.serve.Export/getExportTemp');
    //修改密码
    Route::post('modify', 'v1.serve.Serve/modify');
    //修改手机号码
    Route::post('update_phone', 'v1.serve.Serve/updatePhone');
    //短信配置编辑表单
    Route::get('sms_config/edit_basics', 'v1.setting.SystemConfig/edit_basics');
    //短信配置保存数据
    Route::post('sms_config/save_basics', 'v1.setting.SystemConfig/save_basics');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
