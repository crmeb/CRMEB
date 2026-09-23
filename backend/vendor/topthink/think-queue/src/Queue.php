<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think;

use think\queue\Connector;
use think\queue\connector\Database;
use think\queue\connector\Redis;

/**
 * Class Queue
 * @package think\queue
 *
 * @mixin Database
 * @mixin Redis
 */
class Queue extends Manager
{
    protected $namespace = '\\think\\queue\\connector\\';

    protected function resolveType(string $name)
    {
        return $this->app->config->get("queue.connections.{$name}.type", 'sync');
    }

    protected function resolveConfig(string $name)
    {
        return $this->app->config->get("queue.connections.{$name}");
    }

    protected function createDriver(string $name)
    {
        /** @var Connector $driver */
        $driver = parent::createDriver($name);

        return $driver->setApp($this->app)
            ->setConnection($name);
    }

    /**
     * @param null|string $name
     * @return Connector
     */
    public function connection($name = null)
    {
        return $this->driver($name);
    }

    /**
     * 默认驱动
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app->config->get('queue.default');
    }
}
