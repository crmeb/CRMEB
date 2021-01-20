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
        case 'admin':
        case 'kefu':
            return view(app()->getRootPath() . 'public' . DS . 'admin' . DS . 'index.html');
        default:
            if (!request()->isMobile() && is_dir(app()->getRootPath() . 'public' . DS . 'admin') && !request()->get('type')) {
                return view(app()->getRootPath() . 'public' . DS . 'admin' . DS . 'index.html');
            } else {
                return view(app()->getRootPath() . 'public' . DS . 'index.html');
            }
    }
});
