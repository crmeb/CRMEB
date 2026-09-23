<?php
declare(strict_types=1);

namespace crmeb\services\email;

use crmeb\basic\BaseManager;
use think\facade\Config;
use think\Container;
use think\helper\Str;

/**
 * Class Email
 * @package crmeb\services\email
 * @mixin \extend\email\Smtp\Driver
 */
class Email extends BaseManager
{
    protected $namespace = '\\driver\\email\\';

    protected function getDefaultDriver()
    {
        return Config::get('email.default', 'smtp');
    }

    protected function invokeClass($class)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException('class not exists: ' . $class);
        }
        $this->getConfigFile();
        if (!$this->config) {
            $this->config = Config::get($this->configFile . '.stores.' . $this->name, []);
        }
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
