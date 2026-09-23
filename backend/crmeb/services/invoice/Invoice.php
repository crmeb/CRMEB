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
namespace crmeb\services\invoice;

use crmeb\basic\BaseManager;
use crmeb\services\yihaotong\AccessTokenServeService;
use think\Container;
use think\facade\Config;
use think\helper\Str;

class Invoice extends BaseManager
{
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\driver\\invoice\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
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