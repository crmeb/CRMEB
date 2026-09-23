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
 * 服务平台路由
 */
Route::group('serve', function () {
    //平台登录
    Route::post('login', [\app\adminapi\controller\serve\LoginController::class, 'login'])->option(['real_name' => '一号通平台登录']);
    //验证码
    Route::post('captcha', [\app\adminapi\controller\serve\LoginController::class, 'captcha'])->option(['real_name' => '一号通获取验证码']);
    //验证验证码
    Route::post('checkCode', [\app\adminapi\controller\serve\LoginController::class, 'checkCode'])->option(['real_name' => '一号通验证验证码']);
    //注册
    Route::post('register', [\app\adminapi\controller\serve\LoginController::class, 'register'])->option(['real_name' => '一号通注册']);
    //开通电子面单
    Route::post('opn_express', [\app\adminapi\controller\serve\ServeController::class, 'openExpress'])->option(['real_name' => '一号通开通电子面单']);
    //获取用户信息
    Route::get('info', [\app\adminapi\controller\serve\ServeController::class, 'getUserInfo'])->option(['real_name' => '一号通账户信息']);
    //获取列表模板
    Route::get('meal_list', [\app\adminapi\controller\serve\ServeController::class, 'mealList'])->option(['real_name' => '一号通支付套餐列表']);
    //获取支付
    Route::post('pay_meal', [\app\adminapi\controller\serve\ServeController::class, 'payMeal'])->option(['real_name' => '一号通支付二维码']);
    //开通短信服务
    Route::get('sms/open', [\app\adminapi\controller\serve\SmsController::class, 'openServe'])->option(['real_name' => '一号通开通短信服务']);
    //开通其他服务
    Route::get('open', [\app\adminapi\controller\serve\ServeController::class, 'openServe'])->option(['real_name' => '一号通开通其他服务']);
    //修改签名
    Route::put('sms/sign', [\app\adminapi\controller\serve\SmsController::class, 'editSign'])->option(['real_name' => '一号通修改签名']);
    //获取短信模板
    Route::get('sms/temps', [\app\adminapi\controller\serve\SmsController::class, 'temps'])->option(['real_name' => '一号通获取短信模板']);
    //申请模板
    Route::post('sms/apply', [\app\adminapi\controller\serve\SmsController::class, 'apply'])->option(['real_name' => '一号通申请模板']);
    //获取申请记录
    Route::get('sms/apply_record', [\app\adminapi\controller\serve\SmsController::class, 'applyRecord'])->option(['real_name' => '一号通获取申请记录']);
    //记录
    Route::get('record', [\app\adminapi\controller\serve\ServeController::class, 'getRecord'])->option(['real_name' => '一号通消费记录']);
    //是否开启电子面单打印
    Route::get('dump_open', [\app\adminapi\controller\serve\ExportController::class, 'dumpIsOpen'])->name('dumpIsOpen')->option(['real_name' => '一号通是否开启电子面单打印']);
    //获取全部物流公司
    Route::get('export_all', [\app\adminapi\controller\serve\ExportController::class, 'getExportAll'])->option(['real_name' => '一号通获取全部物流公司']);
    //获取物流公司模板
    Route::get('export_temp', [\app\adminapi\controller\serve\ExportController::class, 'getExportTemp'])->option(['real_name' => '一号通获取物流公司模板']);
    //修改密码
    Route::post('modify', [\app\adminapi\controller\serve\ServeController::class, 'modify'])->option(['real_name' => '一号通修改密码']);
    //修改手机号码
    Route::post('update_phone', [\app\adminapi\controller\serve\ServeController::class, 'updatePhone'])->option(['real_name' => '一号通修改手机号码']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'serve', 'mark_name' => '一号通']);
