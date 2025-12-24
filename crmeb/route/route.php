<?php

use think\facade\Route;

// 生产环境健康检查（给 LB / k8s 使用）
Route::get('healthz', function () {
    $payload = [
        'status' => 'ok',
        'time' => date('c'),
    ];

    // deep=1 时做依赖探活（DB/缓存），失败返回 503
    $deep = (int)request()->get('deep', 0) === 1;
    if ($deep) {
        try {
            \think\facade\Db::query('SELECT 1');
            $payload['db'] = 'ok';
        } catch (\Throwable $e) {
            $payload['db'] = 'fail';
            return \think\Response::create($payload, 'json')->code(503);
        }

        try {
            // 以 Cache 抽象为准（可能是 file/redis）
            \think\facade\Cache::set('__healthz__', 1, 2);
            $payload['cache'] = 'ok';
        } catch (\Throwable $e) {
            $payload['cache'] = 'fail';
            return \think\Response::create($payload, 'json')->code(503);
        }
    }

    return \think\Response::create($payload, 'json');
});

Route::get('surl/:id', function(\app\Request $request){
    return app()->make(\app\api\controller\v1\PublicController::class)->getSchemeUrl($request->param('id'));
});

Route::miss(function () {
    $appRequest = request()->pathinfo();
    if ($appRequest === null) {
        $appName = '';
    } else {
        $appRequest = str_replace('//', '/', $appRequest);
        $appName = explode('/', $appRequest)[0] ?? '';
    }

    switch (strtolower($appName)) {
        case config('app.admin_prefix', 'admin'):
        case 'kefu':
        case 'app':
            return view(app()->getRootPath() . 'public' . DS . config('app.admin_prefix', 'admin') . DS . 'index.html');
        case 'home':
            if (request()->isMobile()) {
                return redirect(app()->route->buildUrl('/'));
            } else {
                return view(app()->getRootPath() . 'public' . DS . 'home' . DS . 'index.html');
            }
        case 'pages':
            return view(app()->getRootPath() . 'public' . DS . 'index.html');
        default:
            if (!request()->isMobile()) {
                if (is_dir(app()->getRootPath() . 'public' . DS . 'home') && !request()->get('mdType')) {
                    return view(app()->getRootPath() . 'public' . DS . 'home' . DS . 'index.html');
                } else {
                    if (request()->get('type')) {
                        return view(app()->getRootPath() . 'public' . DS . 'index.html');
                    } else {
                        return view(app()->getRootPath() . 'public' . DS . 'mobile.html', ['siteName' => sys_config('site_name'), 'siteUrl' => sys_config('site_url') . '/pages/index/index']);
                    }
                }
            } else {
                return view(app()->getRootPath() . 'public' . DS . 'index.html');
            }
    }
});
