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
use think\Response;
use app\http\middleware\AllowOriginMiddleware;

$routeDir = __DIR__;
$files = scandir($routeDir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && $file !== 'route.php' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        require $routeDir . '/' . $file;
    }
}

/**
 * 无需授权的接口
 */
Route::group(function () {
    //升级程序
    Route::get('upgrade', [\app\adminapi\controller\UpgradeController::class, 'index']);
    Route::get('upgrade/run', [\app\adminapi\controller\UpgradeController::class, 'upgrade']);
    //用户名密码登录
    Route::post('login', [\app\adminapi\controller\LoginController::class, 'login'])->name('AdminLogin')->option(['real_name' => '下载表备份记录']);
    //后台登录页面数据
    Route::get('login/info', [\app\adminapi\controller\LoginController::class, 'info'])->option(['real_name' => '登录信息']);
    //验证码
    Route::get('captcha_pro', [\app\adminapi\controller\LoginController::class, 'captcha'])->name('')->option(['real_name' => '获取验证码']);
    //获取验证码
    Route::get('ajcaptcha', [\app\adminapi\controller\LoginController::class, 'ajcaptcha'])->name('ajcaptcha')->option(['real_name' => '获取验证码']);
    //一次验证
    Route::post('ajcheck', [\app\adminapi\controller\LoginController::class, 'ajcheck'])->name('ajcheck')->option(['real_name' => '一次验证']);
    //获取客服数据
    Route::get('get_workerman_url', [\app\adminapi\controller\PublicController::class, 'getWorkerManUrl'])->option(['real_name' => '获取客服数据']);
    //测试
    Route::get('index', [\app\adminapi\controller\TestController::class, 'index'])->option(['real_name' => '测试地址']);
    //扫码上传图片
    Route::post('image/scan_upload', [\app\adminapi\controller\PublicController::class, 'scanUpload'])->option(['real_name' => '扫码上传图片']);
    Route::get('custom_admin_js', [\app\adminapi\controller\PublicController::class, 'customAdminJs'])->option(['real_name' => '测试地址']);

})->middleware(AllowOriginMiddleware::class)->option(['mark' => 'login', 'mark_name' => '登录相关']);


/**
 * 需授权的接口
 */
Route::group(function () {
    //服务器信息
    Route::get('system/info', [\app\adminapi\controller\PublicController::class, 'getSystemInfo'])->option(['real_name' => '服务器信息']);
    //路由导入
    Route::get('route/import_api', [\app\adminapi\controller\PublicController::class, 'import'])->option(['real_name' => '路由导入']);
    //下载文件
    Route::get('download/[:key]', [\app\adminapi\controller\PublicController::class, 'download'])->option(['real_name' => '下载文件']);
})->middleware([
    AllowOriginMiddleware::class,
   \app\adminapi\middleware\AdminAuthTokenMiddleware::class
])->option(['mark' => 'system', 'mark_name' => '系统相关']);

// 自动加载模块后台管理端路由（一/二级目录扫描，含模块启用状态校验，禁用模块不加载路由）
\crmeb\plugin\ModuleRouteLoader::load('admin');

// 加载消息通知后台管理端路由
$noticeAdminRoute = root_path() . 'app/adminapi/route/message.php';
if (is_file($noticeAdminRoute)) {
    require $noticeAdminRoute;
}

/**
 * miss 路由
 *
 * 插件未安装/未启用时给出友好提示，避免前端拿到裸 404 无法区分"接口不存在"与"插件未安装"。
 * 模块判定基于目录扫描 + 安装状态校验（ModuleRouteLoader::inactiveModuleHint），不硬编码模块清单。
 */
Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = AllowOriginMiddleware::corsHeaders(app()->request);
        return Response::create('ok')->code(200)->header($header);
    }
    $inactiveModule = \crmeb\plugin\ModuleRouteLoader::inactiveModuleHint('admin');
    if ($inactiveModule !== null) {
        return app('json')->fail('插件 [' . $inactiveModule['name'] . '] 未安装或未启用，请先安装/启用后再访问');
    }
    return Response::create()->code(404);
});
