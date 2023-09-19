<?php

use think\facade\Route;

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
