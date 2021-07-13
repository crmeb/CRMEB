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

use InvalidArgumentException;
use think\helper\Str;

abstract class Factory
{
    protected $app;

    /**
     * 驱动
     * @var array
     */
    protected $drivers = [];

    /**
     * 驱动的命名空间
     * @var string
     */
    protected $namespace = "";

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 获取驱动实例
     * @param null $driver
     * @return mixed
     */
    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (is_null($driver)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].', static::class
            ));
        }

        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * 获取驱动类
     * @param $driver
     * @return string
     */
    protected function resolveClass($driver)
    {
        if ($this->namespace || false !== strpos($driver, '\\')) {
            $class = false !== strpos($driver, '\\') ? $driver : $this->namespace . Str::studly($driver);

            if (class_exists($class)) {
                return $class;
            }
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * 创建驱动
     *
     * @param string $driver
     * @return mixed
     *
     */
    protected function createDriver($driver)
    {
        $method = 'create' . Str::studly($driver) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        $class = $this->resolveClass($driver);

        return $this->app->make($class);
    }

    /**
     * 默认驱动
     * @return string
     */
    abstract public function getDefaultDriver();

    /**
     * 动态调用
     * @param string $method
     * @param array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
