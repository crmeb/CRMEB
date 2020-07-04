<?php
/**
 * Created by PhpStorm.
 * User: dengfenglai <136327134@qq.com>
 * Date: 2019/11/23 3:47 PM
 */

namespace crmeb\services\printer;

use crmeb\basic\BaseManager;
use think\facade\Config;
use think\Container;

/**
 * Class Printer
 * @package crmeb\services\auth
 * @mixin \crmeb\services\printer\storage\YiLianYun
 */
class Printer extends BaseManager
{

    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\crmeb\\services\\printer\\storage\\';

    /**
     * @var object
     */
    protected $handleAccessToken;

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('printer.default', 'yi_lian_yun');
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

        if (!$this->handleAccessToken) {
            $this->handleAccessToken = new AccessToken($this->config, $this->name, $this->configFile);
        }

        $handle = Container::getInstance()->invokeClass($class, [$this->name, $this->handleAccessToken, $this->configFile]);
        $this->config = [];
        return $handle;
    }

}