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

namespace crmeb\services\sms;

use crmeb\basic\BaseManager;
use crmeb\services\yihaotong\AccessTokenServeService;
use think\Container;
use think\facade\Config;
use think\helper\Str;


/**
 * Class Sms
 * @package crmeb\services\sms
 * @mixin \extend\sms\Yihaotong\Driver
 */
class Sms extends BaseManager
{

    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\driver\\sms\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('sms.default');
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

        $handleAccessToken = new AccessTokenServeService($this->config['yihaotong_appid'] ?? '', $this->config['yihaotong_appsecret'] ?? '');
        $handle = Container::getInstance()->invokeClass($class, [$this->name, $handleAccessToken, $this->configFile, $this->config]);
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