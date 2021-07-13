<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use think\event\LogWrite;
use think\helper\Arr;
use think\log\Channel;
use think\log\ChannelSet;

/**
 * 日志管理类
 * @package think
 * @mixin Channel
 */
class Log extends Manager implements LoggerInterface
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';
    const SQL       = 'sql';

    protected $namespace = '\\think\\log\\driver\\';

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('default');
    }

    /**
     * 获取日志配置
     * @access public
     * @param null|string $name    名称
     * @param mixed       $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('log.' . $name, $default);
        }

        return $this->app->config->get('log');
    }

    /**
     * 获取渠道配置
     * @param string $channel
     * @param null   $name
     * @param null   $default
     * @return array
     */
    public function getChannelConfig($channel, $name = null, $default = null)
    {
        if ($config = $this->getConfig("channels.{$channel}")) {
            return Arr::get($config, $name, $default);
        }

        throw new InvalidArgumentException("Channel [$channel] not found.");
    }

    /**
     * driver()的别名
     * @param string|array $name 渠道名
     * @return Channel|ChannelSet
     */
    public function channel($name = null)
    {
        if (is_array($name)) {
            return new ChannelSet($this, $name);
        }

        return $this->driver($name);
    }

    protected function resolveType(string $name)
    {
        return $this->getChannelConfig($name, 'type', 'file');
    }

    public function createDriver(string $name)
    {
        $driver = parent::createDriver($name);

        $lazy  = !$this->getChannelConfig($name, "realtime_write", false) && !$this->app->runningInConsole();
        $allow = array_merge($this->getConfig("level", []), $this->getChannelConfig($name, "level", []));

        return new Channel($name, $driver, $allow, $lazy, $this->app->event);
    }

    protected function resolveConfig(string $name)
    {
        return $this->getChannelConfig($name);
    }

    /**
     * 清空日志信息
     * @access public
     * @param string|array $channel 日志通道名
     * @return $this
     */
    public function clear($channel = '*')
    {
        if ('*' == $channel) {
            $channel = array_keys($this->drivers);
        }

        $this->channel($channel)->clear();

        return $this;
    }

    /**
     * 关闭本次请求日志写入
     * @access public
     * @param string|array $channel 日志通道名
     * @return $this
     */
    public function close($channel = '*')
    {
        if ('*' == $channel) {
            $channel = array_keys($this->drivers);
        }

        $this->channel($channel)->close();

        return $this;
    }

    /**
     * 获取日志信息
     * @access public
     * @param string $channel 日志通道名
     * @return array
     */
    public function getLog(string $channel = null): array
    {
        return $this->channel($channel)->getLog();
    }

    /**
     * 保存日志信息
     * @access public
     * @return bool
     */
    public function save(): bool
    {
        /** @var Channel $channel */
        foreach ($this->drivers as $channel) {
            $channel->save();
        }

        return true;
    }

    /**
     * 记录日志信息
     * @access public
     * @param mixed  $msg     日志信息
     * @param string $type    日志级别
     * @param array  $context 替换内容
     * @param bool   $lazy
     * @return $this
     */
    public function record($msg, string $type = 'info', array $context = [], bool $lazy = true)
    {
        $channel = $this->getConfig('type_channel.' . $type);

        $this->channel($channel)->record($msg, $type, $context, $lazy);

        return $this;
    }

    /**
     * 实时写入日志信息
     * @access public
     * @param mixed  $msg     调试信息
     * @param string $type    日志级别
     * @param array  $context 替换内容
     * @return $this
     */
    public function write($msg, string $type = 'info', array $context = [])
    {
        return $this->record($msg, $type, $context, false);
    }

    /**
     * 注册日志写入事件监听
     * @param $listener
     * @return Event
     */
    public function listen($listener)
    {
        return $this->app->event->listen(LogWrite::class, $listener);
    }

    /**
     * 记录日志信息
     * @access public
     * @param string $level   日志级别
     * @param mixed  $message 日志信息
     * @param array  $context 替换内容
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->record($message, $level, $context);
    }

    /**
     * 记录emergency信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录警报信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录紧急情况
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录错误信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录warning信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录notice信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录一般信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录调试信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录sql信息
     * @access public
     * @param mixed $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function sql($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function __call($method, $parameters)
    {
        $this->log($method, ...$parameters);
    }
}
