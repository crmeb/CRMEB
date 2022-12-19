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
 * 消息通知管理、模版消息（列表，通知，添加，编辑）、短信 相关路由
 */
Route::group('notify', function () {
    //保存配置 登录
    Route::post('sms/config', 'v1.notification.sms.SmsConfig/save_basics')->option(['real_name' => '保存短信配置']);
    //短信发送记录
    Route::get('sms/record', 'v1.notification.sms.SmsConfig/record')->option(['real_name' => '短信发送记录']);
    //短信账号数据
    Route::get('sms/data', 'v1.notification.sms.SmsConfig/data')->option(['real_name' => '短信账号数据']);
    //查看是否登录
    Route::get('sms/is_login', 'v1.notification.sms.SmsConfig/is_login')->option(['real_name' => '查看短信账号是否登录']);
    //查看是否登录
    Route::get('sms/logout', 'v1.notification.sms.SmsConfig/logout')->option(['real_name' => '短信账号退出登录']);
    //发送短信验证码
    Route::post('sms/captcha', 'v1.notification.sms.SmsAdmin/captcha')->option(['real_name' => '发送短信验证码']);
    //修改/注册短信平台账号
    Route::post('sms/register', 'v1.notification.sms.SmsAdmin/save')->option(['real_name' => '修改或注册短信平台账号']);
    //短信模板列表
    Route::get('sms/temp', 'v1.notification.sms.SmsTemplateApply/index')->option(['real_name' => '短信模板列表']);
    //短信模板申请表单
    Route::get('sms/temp/create', 'v1.notification.sms.SmsTemplateApply/create')->option(['real_name' => '短信模板申请表单']);
    //短信模板申请
    Route::post('sms/temp', 'v1.notification.sms.SmsTemplateApply/save')->option(['real_name' => '短信模板申请']);
    //公共短信模板列表
    Route::get('sms/public_temp', 'v1.notification.sms.SmsPublicTemp/index')->option(['real_name' => '公共短信模板列表']);
    //剩余条数
    Route::get('sms/number', 'v1.notification.sms.SmsPay/number')->option(['real_name' => '短信剩余条数']);
    //获取支付套餐
    Route::get('sms/price', 'v1.notification.sms.SmsPay/price')->option(['real_name' => '获取短信购买套餐']);
    //获取支付码
    Route::post('sms/pay_code', 'v1.notification.sms.SmsPay/pay')->option(['real_name' => '获取短信购买支付码']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
