<?php

use think\facade\Route;

Route::get('surl/:id', function (\app\Request $request) {
    $results = event('ChannelServiceProxy', [
        'endpoint' => 'wechat_mini',
        'service' => 'RoutineSchemeServices',
        'action' => 'redirectHtml',
        'args' => [$request->param('id')],
    ]);
    return response((string)proxy_event_result($results, '<h1>未找到跳转路径</h1>'));
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
        case 'pages':
            return view(app()->getRootPath() . 'public' . DS . 'index.html');
        default:
            if (!request()->isMobile()) {
                if (request()->get('type')) {
                    return view(app()->getRootPath() . 'public' . DS . 'index.html');
                } else {
                    return view(app()->getRootPath() . 'public' . DS . 'mobile.html', ['siteName' => sys_config('site_name'), 'siteUrl' => sys_config('site_url') . '/pages/index/index']);
                }
            } else {
                return view(app()->getRootPath() . 'public' . DS . 'index.html');
            }
    }
});
