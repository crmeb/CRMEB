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

use InvalidArgumentException;
use think\cache\driver\Redis;
use think\helper\Str;
use think\queue\Connector;
use think\queue\connector\Database;

/**
 * Class Queue
 * @package think\queue
 *
 * @method Connector driver($driver = null)
 * @mixin Database
 * @mixin Redis
 */
class Queue extends Factory
{
    protected $namespace = '\\think\\queue\\connector\\';

    /**
     * Get the queue connector configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app->config->get("queue.connections.{$name}", ['driver' => 'sync']);
    }

    protected function createDriver($name)
    {
        $driver = $this->getConfig($name)['driver'];

        $class = false !== strpos($driver, '\\') ? $driver : $this->namespace . Str::studly($driver);

        /** @var Connector $driver */
        if (class_exists($class)) {
            $driver = $this->app->invokeClass($class, [$this->getConfig($driver)]);

            return $driver->setApp($this->app)
                ->setConnection($name);
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
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
