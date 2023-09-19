<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use think\facade\Config;
use think\Response;
use app\http\middleware\AllowOriginMiddleware;

/**
 * 无需授权的接口
 */
Route::group(function () {
    //升级程序
    Route::get('upgrade', 'UpgradeController/index');
    Route::get('upgrade/run', 'UpgradeController/upgrade');
    //用户名密码登录
    Route::post('login', 'Login/login')->name('AdminLogin')->option(['real_name' => '下载表备份记录']);
    //后台登录页面数据
    Route::get('login/info', 'Login/info')->option(['real_name' => '登录信息']);
    //下载文件
    Route::get('download/:key', 'PublicController/download')->option(['real_name' => '下载文件']);
    //验证码
    Route::get('captcha_pro', 'Login/captcha')->name('')->option(['real_name' => '获取验证码']);
    //获取验证码
    Route::get('ajcaptcha', 'Login/ajcaptcha')->name('ajcaptcha')->option(['real_name' => '获取验证码']);
    //一次验证
    Route::post('ajcheck', 'Login/ajcheck')->name('ajcheck')->option(['real_name' => '一次验证']);
    //获取客服数据
    Route::get('get_workerman_url', 'PublicController/getWorkerManUrl')->option(['real_name' => '获取客服数据']);
    //测试
    Route::get('index', 'Test/index')->option(['real_name' => '测试地址']);

    //扫码上传图片
    Route::post('image/scan_upload', 'PublicController/scanUpload')->option(['real_name' => '扫码上传图片']);
    //路由导入
    Route::get('route/import_api', 'PublicController/import')->option(['real_name' => '路由导入']);

})->middleware(AllowOriginMiddleware::class)->option(['mark' => 'login', 'mark_name' => '登录相关']);

/**
 * miss 路由
 */
Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        $header['Access-Control-Allow-Origin'] = app()->request->header('origin');
        return Response::create('ok')->code(200)->header($header);
    } else
        return Response::create()->code(404);
});
