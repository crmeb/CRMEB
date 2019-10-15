<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\service;

use think\Paginator;
use think\paginator\driver\Bootstrap;
use think\Service;

/**
 * 分页服务类
 */
class PaginatorService extends Service
{
    public function register()
    {
        if (!$this->app->bound(Paginator::class)) {
            $this->app->bind(Paginator::class, Bootstrap::class);
        }
    }

    public function boot()
    {
        Paginator::maker(function (...$args) {
            return $this->app->make(Paginator::class, $args, true);
        });

        Paginator::currentPathResolver(function () {
            return $this->app->request->baseUrl();
        });

        Paginator::currentPageResolver(function ($varPage = 'page') {

            $page = $this->app->request->param($varPage);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });
    }
}
