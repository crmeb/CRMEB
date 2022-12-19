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
 * 服务平台路由
 */
Route::group('serve', function () {
    //平台登录
    Route::post('login', 'v1.serve.Login/login')->option(['real_name' => '一号通平台登录']);
    //验证码
    Route::post('captcha', 'v1.serve.Login/captcha')->option(['real_name' => '一号通获取验证码']);
    //验证验证码
    Route::post('checkCode', 'v1.serve.Login/checkCode')->option(['real_name' => '一号通验证验证码']);
    //注册
    Route::post('register', 'v1.serve.Login/register')->option(['real_name' => '一号通注册']);
    //开通电子面单
    Route::post('opn_express', 'v1.serve.Serve/openExpress')->option(['real_name' => '一号通开通电子面单']);
    //获取用户信息
    Route::get('info', 'v1.serve.Serve/getUserInfo')->option(['real_name' => '一号通账户信息']);
    //获取列表模板
    Route::get('meal_list', 'v1.serve.Serve/mealList')->option(['real_name' => '一号通支付套餐列表']);
    //获取支付
    Route::post('pay_meal', 'v1.serve.Serve/payMeal')->option(['real_name' => '一号通支付二维码']);
    //开通短信服务
    Route::get('sms/open', 'v1.serve.Sms/openServe')->option(['real_name' => '一号通开通短信服务']);
    //开通其他服务
    Route::get('open', 'v1.serve.Serve/openServe')->option(['real_name' => '一号通开通其他服务']);
    //修改签名
    Route::put('sms/sign', 'v1.serve.Sms/editSign')->option(['real_name' => '一号通修改签名']);
    //获取短信模板
    Route::get('sms/temps', 'v1.serve.Sms/temps')->option(['real_name' => '一号通获取短信模板']);
    //申请模板
    Route::post('sms/apply', 'v1.serve.Sms/apply')->option(['real_name' => '一号通申请模板']);
    //获取申请记录
    Route::get('sms/apply_record', 'v1.serve.Sms/applyRecord')->option(['real_name' => '一号通获取申请记录']);
    //记录
    Route::get('record', 'v1.serve.Serve/getRecord')->option(['real_name' => '一号通消费记录']);
    //是否开启电子面单打印
    Route::get('dump_open', 'v1.serve.Export/dumpIsOpen')->name('dumpIsOpen')->option(['real_name' => '一号通是否开启电子面单打印']);
    //获取全部物流公司
    Route::get('export_all', 'v1.serve.Export/getExportAll')->option(['real_name' => '一号通获取全部物流公司']);
    //获取物流公司模板
    Route::get('export_temp', 'v1.serve.Export/getExportTemp')->option(['real_name' => '一号通获取物流公司模板']);
    //修改密码
    Route::post('modify', 'v1.serve.Serve/modify')->option(['real_name' => '一号通修改密码']);
    //修改手机号码
    Route::post('update_phone', 'v1.serve.Serve/updatePhone')->option(['real_name' => '一号通修改手机号码']);
    //短信配置编辑表单
    Route::get('sms_config/edit_basics', 'v1.setting.SystemConfig/edit_basics')->option(['real_name' => '一号通短信配置编辑表单']);
    //短信配置保存数据
    Route::post('sms_config/save_basics', 'v1.setting.SystemConfig/save_basics')->option(['real_name' => '一号通短信配置保存数据']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
