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
use think\Container;

/**
 * 日志管理类
 */
class LogManager implements LoggerInterface
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

    /**
     * 日志信息
     * @var array
     */
    protected $log = [];

    /**
     * 日志通道
     * @var array
     */
    protected $channel = [];

    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * 日志写入驱动
     * @var array
     */
    protected $driver = [];

    /**
     * 日志处理
     *
     * @var array
     */
    protected $processor = [];

    /**
     * 关闭日志（渠道）
     * @var array
     */
    protected $close = [];

    /**
     * （通道）允许写入类型
     * @var array
     */
    protected $allow = [];

    /**
     * 是否控制台执行
     * @var bool
     */
    protected $isCli = false;

    /**
     * 初始化
     * @access public
     */
    public function init(array $config = [])
    {
        $this->config = $config;

        if (isset($this->config['processor'])) {
            $this->processor($this->config['processor']);
        }

        if (!empty($this->config['close'])) {
            $this->close['*'] = true;
        }

        if (!empty($this->config['level'])) {
            $this->allow['*'] = $this->config['level'];
        }

        $this->isCli = $this->runningInConsole();
        $this->channel();
    }

    /**
     * 是否运行在命令行下
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * 获取日志配置
     * @access public
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 注册一个日志回调处理
     *
     * @param  callable $callback 回调
     * @param  string   $channel  日志通道名
     * @return void
     */
    public function processor(callable $callback, string $channel = '*'): void
    {
        $this->processor[$channel][] = $callback;
    }

    /**
     * 切换日志通道
     * @access public
     * @param  string|array $name 日志通道名
     * @return $this
     */
    public function channel($name = '')
    {
        if ('' == $name) {
            $name = $this->config['default'] ?? 'think';
        }

        $names = (array) $name;

        foreach ($names as $name) {
            if (!isset($this->config['channels'][$name])) {
                throw new InvalidArgumentException('Undefined log config:' . $name);
            }

            $config = $this->config['channels'][$name];

            if (!empty($config['processor'])) {
                $this->processor($config['processor'], $name);
            }

            if (!empty($config['close'])) {
                $this->close[$name] = true;
            }

            if (!empty($config['level'])) {
                $this->allow[$name] = $config['level'];
            }
        }

        $this->channel = $names;
        return $this;
    }

    /**
     * 实例化日志写入驱动
     * @access public
     * @param  string $name 日志通道名
     * @return object
     */
    protected function driver(string $name)
    {
        if (!isset($this->driver[$name])) {
            $config = $this->config['channels'][$name];
            $type   = !empty($config['type']) ? $config['type'] : 'File';

            $config['is_cli'] = $this->isCli;

            $this->driver[$name] = Container::factory($type, '\\think\\log\\driver\\', $config);
        }

        return $this->driver[$name];
    }

    /**
     * 获取日志信息
     * @access public
     * @param  string $channel 日志通道
     * @return array
     */
    public function getLog(string $channel = ''): array
    {
        $channel = $channel ?: array_shift($this->channel);
        return $this->log[$channel] ?? [];
    }

    /**
     * 记录日志信息
     * @access public
     * @param  mixed  $msg       日志信息
     * @param  string $type      日志级别
     * @param  array  $context   替换内容
     * @return $this
     */
    public function record($msg, string $type = 'info', array $context = [])
    {
        if (!empty($this->allow['*']) && !in_array($type, $this->allow['*'])) {
            return $this;
        }

        if (is_string($msg) && !empty($context)) {
            $replace = [];
            foreach ($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }

            $msg = strtr($msg, $replace);
        }

        if (isset($this->config['type_channel'][$type])) {
            $channels = (array) $this->config['type_channel'][$type];
        } else {
            $channels = $this->channel;
        }

        foreach ($channels as $channel) {
            if (empty($this->allow[$channel]) || in_array($type, $this->allow[$channel])) {
                $this->channelLog($channel, $msg, $type);
            }
        }

        return $this;
    }

    /**
     * 记录通道日志
     * @access public
     * @param  string $channel 日志通道
     * @param  mixed  $msg  日志信息
     * @param  string $type 日志级别
     * @return void
     */
    protected function channelLog(string $channel, $msg, string $type): void
    {
        if (!empty($this->close['*']) || !empty($this->close[$channel])) {
            return;
        }

        if ($this->isCli || !empty($this->config['channels'][$channel]['realtime_write'])) {
            // 实时写入
            $this->write($msg, $type, true, $channel);
        } else {
            $this->log[$channel][$type][] = $msg;
        }
    }

    /**
     * 清空日志信息
     * @access public
     * @param  string  $channel 日志通道名
     * @return $this
     */
    public function clear(string $channel = '')
    {
        if ($channel) {
            $this->log[$channel] = [];
        } else {
            $this->log = [];
        }

        return $this;
    }

    /**
     * 关闭本次请求日志写入
     * @access public
     * @param  string  $channel 日志通道名
     * @return $this
     */
    public function close(string $channel = '*')
    {
        $this->close[$channel] = true;

        $this->clear('*' == $channel ? '' : $channel);

        return $this;
    }

    /**
     * 保存日志信息
     * @access public
     * @return bool
     */
    public function save(): bool
    {
        if (!empty($this->close['*'])) {
            return true;
        }

        foreach ($this->log as $channel => $logs) {
            if (!empty($this->close[$channel])) {
                continue;
            }

            $result = $this->saveChannel($channel, $logs);

            if ($result) {
                $this->log[$channel] = [];
            }
        }

        return true;
    }

    /**
     * 保存某个通道的日志信息
     * @access protected
     * @param  string $channel 日志通道名
     * @param  array  $log    日志信息
     * @return bool
     */
    protected function saveChannel(string $channel, array $log = []): bool
    {
        // 日志处理
        $processors = array_merge($this->processor[$channel] ?? [], $this->processor['*'] ?? []);

        foreach ($processors as $callback) {
            $log = $callback($log, $channel);

            if (false === $log) {
                return false;
            }
        }

        return $this->driver($channel)->save($log);
    }

    /**
     * 实时写入日志信息
     * @access public
     * @param  mixed  $msg   调试信息
     * @param  string $type  日志级别
     * @param  bool   $force 是否强制写入
     * @param  string $channel  日志通道
     * @return bool
     */
    public function write($msg, string $type = 'info', bool $force = false, $channel = ''): bool
    {
        if (empty($this->allow['*'])) {
            $force = true;
        }

        $log = [];

        if (true === $force || in_array($type, $this->allow['*'])) {
            $log[$type][] = $msg;
        } else {
            return false;
        }

        // 写入日志
        $channels = $channel ? (array) $channel : $this->channel;

        foreach ($channels as $channel) {
            if (empty($this->allow[$channel]) || in_array($type, $this->allow[$channel])) {
                $this->saveChannel($channel, $log);
            }
        }

        return true;
    }

    /**
     * 记录日志信息
     * @access public
     * @param  string $level     日志级别
     * @param  mixed  $message   日志信息
     * @param  array  $context   替换内容
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->record($message, $level, $context);
    }

    /**
     * 记录emergency信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录警报信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录紧急情况
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录错误信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录warning信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录notice信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录一般信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录调试信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录sql信息
     * @access public
     * @param  mixed $message 日志信息
     * @param  array $context 替换内容
     * @return void
     */
    public function sql($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function __call($method, $args)
    {
        array_unshift($args, $method);
        call_user_func_array([$this, 'log'], $args);
    }
}
