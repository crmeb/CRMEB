<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\app;

use think\Service as BaseService;

class Service extends BaseService
{
    public function register()
    {
        $this->app->middleware->unshift(MultiApp::class);

        $this->commands([
            'build' => command\Build::class,
        ]);

        $this->app->bind([
            'think\route\Url' => Url::class,
        ]);
    }
}
