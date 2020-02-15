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

use ReflectionClass;
use ReflectionMethod;

/**
 * 事件管理类
 * @package think
 */
class Event
{
    /**
     * 监听者
     * @var array
     */
    protected $listener = [];

    /**
     * 事件别名
     * @var array
     */
    protected $bind = [
        'AppInit'     => event\AppInit::class,
        'HttpRun'     => event\HttpRun::class,
        'HttpEnd'     => event\HttpEnd::class,
        'RouteLoaded' => event\RouteLoaded::class,
        'LogWrite'    => event\LogWrite::class,
    ];

    /**
     * 是否需要事件响应
     * @var bool
     */
    protected $withEvent = true;

    /**
     * 应用对象
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 设置是否开启事件响应
     * @access protected
     * @param bool $event 是否需要事件响应
     * @return $this
     */
    public function withEvent(bool $event)
    {
        $this->withEvent = $event;
        return $this;
    }

    /**
     * 批量注册事件监听
     * @access public
     * @param array $events 事件定义
     * @return $this
     */
    public function listenEvents(array $events)
    {
        if (!$this->withEvent) {
            return $this;
        }

        foreach ($events as $event => $listeners) {
            if (isset($this->bind[$event])) {
                $event = $this->bind[$event];
            }

            $this->listener[$event] = array_merge($this->listener[$event] ?? [], $listeners);
        }

        return $this;
    }

    /**
     * 注册事件监听
     * @access public
     * @param string $event    事件名称
     * @param mixed  $listener 监听操作（或者类名）
     * @param bool   $first    是否优先执行
     * @return $this
     */
    public function listen(string $event, $listener, bool $first = false)
    {
        if (!$this->withEvent) {
            return $this;
        }

        if (isset($this->bind[$event])) {
            $event = $this->bind[$event];
        }

        if ($first && isset($this->listener[$event])) {
            array_unshift($this->listener[$event], $listener);
        } else {
            $this->listener[$event][] = $listener;
        }

        return $this;
    }

    /**
     * 是否存在事件监听
     * @access public
     * @param string $event 事件名称
     * @return bool
     */
    public function hasListener(string $event): bool
    {
        if (isset($this->bind[$event])) {
            $event = $this->bind[$event];
        }

        return isset($this->listener[$event]);
    }

    /**
     * 移除事件监听
     * @access public
     * @param string $event 事件名称
     * @return void
     */
    public function remove(string $event): void
    {
        if (isset($this->bind[$event])) {
            $event = $this->bind[$event];
        }

        unset($this->listener[$event]);
    }

    /**
     * 指定事件别名标识 便于调用
     * @access public
     * @param array $events 事件别名
     * @return $this
     */
    public function bind(array $events)
    {
        $this->bind = array_merge($this->bind, $events);

        return $this;
    }

    /**
     * 注册事件订阅者
     * @access public
     * @param mixed $subscriber 订阅者
     * @return $this
     */
    public function subscribe($subscriber)
    {
        if (!$this->withEvent) {
            return $this;
        }

        $subscribers = (array) $subscriber;

        foreach ($subscribers as $subscriber) {
            if (is_string($subscriber)) {
                $subscriber = $this->app->make($subscriber);
            }

            if (method_exists($subscriber, 'subscribe')) {
                // 手动订阅
                $subscriber->subscribe($this);
            } else {
                // 智能订阅
                $this->observe($subscriber);
            }
        }

        return $this;
    }

    /**
     * 自动注册事件观察者
     * @access public
     * @param string|object $observer 观察者
     * @param null|string   $prefix   事件名前缀
     * @return $this
     */
    public function observe($observer, string $prefix = '')
    {
        if (!$this->withEvent) {
            return $this;
        }

        if (is_string($observer)) {
            $observer = $this->app->make($observer);
        }

        $reflect = new ReflectionClass($observer);
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);

        if (empty($prefix) && $reflect->hasProperty('eventPrefix')) {
            $reflectProperty = $reflect->getProperty('eventPrefix');
            $reflectProperty->setAccessible(true);
            $prefix = $reflectProperty->getValue($observer);
        }

        foreach ($methods as $method) {
            $name = $method->getName();
            if (0 === strpos($name, 'on')) {
                $this->listen($prefix . substr($name, 2), [$observer, $name]);
            }
        }

        return $this;
    }

    /**
     * 触发事件
     * @access public
     * @param string|object $event  事件名称
     * @param mixed         $params 传入参数
     * @param bool          $once   只获取一个有效返回值
     * @return mixed
     */
    public function trigger($event, $params = null, bool $once = false)
    {
        if (!$this->withEvent) {
            return;
        }

        if (is_object($event)) {
            $params = $event;
            $event  = get_class($event);
        }

        if (isset($this->bind[$event])) {
            $event = $this->bind[$event];
        }

        $result    = [];
        $listeners = $this->listener[$event] ?? [];

        foreach ($listeners as $key => $listener) {
            $result[$key] = $this->dispatch($listener, $params);

            if (false === $result[$key] || (!is_null($result[$key]) && $once)) {
                break;
            }
        }

        return $once ? end($result) : $result;
    }

    /**
     * 触发事件(只获取一个有效返回值)
     * @param      $event
     * @param null $params
     * @return mixed
     */
    public function until($event, $params = null)
    {
        return $this->trigger($event, $params, true);
    }

    /**
     * 执行事件调度
     * @access protected
     * @param mixed $event  事件方法
     * @param mixed $params 参数
     * @return mixed
     */
    protected function dispatch($event, $params = null)
    {
        if (!is_string($event)) {
            $call = $event;
        } elseif (strpos($event, '::')) {
            $call = $event;
        } else {
            $obj  = $this->app->make($event);
            $call = [$obj, 'handle'];
        }

        return $this->app->invoke($call, [$params]);
    }

}
