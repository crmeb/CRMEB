<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\logistics;


use crmeb\basic\BaseManager;
use think\Container;
use think\facade\Config;
use think\helper\Str;

/**
 * Class Express
 * @package crmeb\services\logistics
 * @mixin \driver\logistics\Yihaotong\Driver
 */
class Express extends BaseManager
{
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\driver\\logistics\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('express.default', 'yihaotong');
    }

    /**
     * 获取类的实例
     * @param $class
     * @return mixed|void
     */
    protected function invokeClass($class)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException('class not exists: ' . $class);
        }
        $this->getConfigFile();

        if (!$this->config) {
            $this->config = Config::get($this->configFile . '.stores.' . $this->name, []);
        }
        // 将配置直接传入驱动构造函数，由各驱动的 initialize() 自行处理
        $handle = Container::getInstance()->invokeClass($class, [$this->name, $this->config, $this->configFile]);
        $this->config = [];
        return $handle;
    }

    protected function resolveClass(string $type): string
    {
        $studly = Str::studly($type);

        $class = $this->namespace . $studly . '\\Driver';
        if (class_exists($class)) {
            return $class;
        }

        $class = $this->namespace . $studly;
        if (class_exists($class)) {
            return $class;
        }

        throw new \InvalidArgumentException("Driver [{$type}] not supported.");
    }
}