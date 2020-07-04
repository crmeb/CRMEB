<?php

namespace crmeb\basic;

use think\facade\Config;
use think\helper\Str;
use think\Container;

/**
 * Class BaseManager
 * @package crmeb\basic
 */
abstract class BaseManager
{
    /**
     * 驱动的命名空间
     * @var null
     */
    protected $namespace = null;

    /**
     * 配置
     * @var null
     */
    protected $configFile = null;

    /**
     * 配置
     * @var array
     */
    protected $config = [];

    /**
     * 驱动
     * @var array
     */
    protected $drivers = [];

    /**
     * 驱动类型
     * @var null
     */
    protected $name = null;

    /**
     * BaseManager constructor.
     * @param string|array|int $name驱动名称
     * @param array $config 配置
     */
    public function __construct($name = null, array $config = [])
    {
        $type = null;
        if (is_array($name)) {
            $config = $name;
            $name = null;
        }

        if (is_int($name)) {
            $type = $name;
            $name = null;
        }

        if ($name)
            $this->name = $name;
        if ($type && is_null($this->name)) {
            $this->setHandleType((int)$type - 1);
        }
        $this->config = $config;
    }

    /**
     * 提取配置文件名
     * @return $this
     */
    protected function getConfigFile()
    {
        if (is_null($this->configFile)) {
            $this->configFile = strtolower((new \ReflectionClass($this))->getShortName());
        }
        return $this;
    }

    /**
     * 设置文件句柄
     * @param int $type
     */
    protected function setHandleType(int $type)
    {
        $this->getConfigFile();
        $stores = array_keys(Config::get($this->configFile . '.stores', []));
        $name = $stores[$type] ?? null;
        if (!$name) {
            throw new \RuntimeException($this->configFile . ' type is not used');
        }
        $this->name = $name;
    }

    /**
     * 设置默认句柄
     * @return mixed
     */
    abstract protected function getDefaultDriver();

    /**
     * 动态调用
     * @param $method
     * @param $arguments
     */
    public function __call($method, $arguments)
    {
        return $this->driver()->{$method}(...$arguments);
    }

    /**
     * 获取驱动实例
     * @param null|string $name
     * @return mixed
     */
    protected function driver(string $name = null)
    {
        $name = $name ?: $this->name;
        $name = $name ?: $this->getDefaultDriver();

        if (is_null($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].', static::class
            ));
        }

        return $this->drivers[$name] = $this->getDriver($name);
    }

    /**
     * 获取驱动实例
     * @param string $name
     * @return mixed
     */
    protected function getDriver(string $name)
    {
        return $this->drivers[$name] ?? $this->createDriver($name);
    }

    /**
     * 获取驱动类型
     * @param string $name
     * @return mixed
     */
    protected function resolveType(string $name)
    {
        return $name;
    }

    /**
     * 创建驱动
     *
     * @param string $name
     * @return mixed
     *
     */
    protected function createDriver(string $name)
    {
        $type = $this->resolveType($name);

        $method = 'create' . Str::studly($type) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->$method($name);
        }

        $class = $this->resolveClass($type);
        $this->name = $type;
        return $this->invokeClass($class);
    }


    /**
     * 获取驱动类
     * @param string $type
     * @return string
     */
    protected function resolveClass(string $type): string
    {
        if ($this->namespace || false !== strpos($type, '\\')) {
            $class = false !== strpos($type, '\\') ? $type : $this->namespace . Str::studly($type);
            if (class_exists($class)) {
                return $class;
            }
        }

        throw new \InvalidArgumentException("Driver [$type] not supported.");
    }

    /**
     * 实例化类
     * @param $class
     * @return mixed
     */
    protected function invokeClass($class)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException('class not exists: ' . $class);
        }
        $this->getConfigFile();
        $handle = Container::getInstance()->invokeClass($class, [$this->name, $this->config, $this->configFile]);
        $this->config = [];
        return $handle;
    }

}