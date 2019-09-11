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

namespace think;

use think\filesystem\Driver;
use think\filesystem\driver\Local;

/**
 * Class Filesystem
 * @package think
 * @mixin Driver
 * @mixin Local
 */
class Filesystem
{
    protected $disks;

    /** @var App */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param null|string $name
     * @return Driver
     */
    public function disk(string $name = null): Driver
    {
        $name = $name ?: $this->app->config->get('filesystem.default');

        if (!isset($this->disks[$name])) {
            $config = $this->app->config->get("filesystem.disks.{$name}");

            $this->disks[$name] = App::factory($config['type'], '\\think\\filesystem\\driver\\', $config);
        }

        return $this->disks[$name];
    }

    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}
