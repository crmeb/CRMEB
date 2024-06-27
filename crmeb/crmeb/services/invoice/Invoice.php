<?php

namespace crmeb\services\invoice;

use crmeb\basic\BaseManager;
use crmeb\services\AccessTokenServeService;
use think\Container;
use think\facade\Config;

class Invoice extends BaseManager
{
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\crmeb\\services\\invoice\\storage\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
//        return Config::get('invoice.default');
        return 'yihaotong';
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
        $handleAccessToken = new AccessTokenServeService($this->config['account'] ?? '', $this->config['secret'] ?? '');
        $handle = Container::getInstance()->invokeClass($class, [$this->name, $handleAccessToken, $this->configFile, $this->config]);
        $this->config = [];
        return $handle;
    }
}