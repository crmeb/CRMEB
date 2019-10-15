<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Slince <taosikai@yeah.net>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use InvalidArgumentException;
use LogicException;
use think\exception\HttpResponseException;

/**
 * 中间件管理类
 * @package think
 */
class Middleware
{
    /**
     * 中间件执行队列
     * @var array
     */
    protected $queue = [];

    /**
     * 配置
     * @var array
     */
    protected $config = [];

    /**
     * 应用对象
     * @var App
     */
    protected $app;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public static function __make(App $app, Config $config)
    {
        return (new static($config->get('middleware')))->setApp($app);
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 设置应用对象
     * @access public
     * @param  App  $app
     * @return $this
     */
    public function setApp(App $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * 导入中间件
     * @access public
     * @param  array  $middlewares
     * @param  string $type  中间件类型
     * @return void
     */
    public function import(array $middlewares = [], string $type = 'route'): void
    {
        foreach ($middlewares as $middleware) {
            $this->add($middleware, $type);
        }
    }

    /**
     * 注册中间件
     * @access public
     * @param  mixed  $middleware
     * @param  string $type  中间件类型
     * @return void
     */
    public function add($middleware, string $type = 'route'): void
    {
        if (is_null($middleware)) {
            return;
        }

        $middleware = $this->buildMiddleware($middleware, $type);

        if ($middleware) {
            $this->queue[$type][] = $middleware;
        }
    }

    /**
     * 注册控制器中间件
     * @access public
     * @param  mixed  $middleware
     * @return void
     */
    public function controller($middleware): void
    {
        $this->add($middleware, 'controller');
    }

    /**
     * 移除中间件
     * @access public
     * @param  mixed  $middleware
     * @param  string $type  中间件类型
     */
    public function unshift($middleware, string $type = 'route')
    {
        if (is_null($middleware)) {
            return;
        }

        $middleware = $this->buildMiddleware($middleware, $type);

        if (!empty($middleware)) {
            array_unshift($this->queue[$type], $middleware);
        }
    }

    /**
     * 获取注册的中间件
     * @access public
     * @param  string $type  中间件类型
     */
    public function all(string $type = 'route'): array
    {
        return $this->queue[$type] ?? [];
    }

    /**
     * 中间件调度
     * @access public
     * @param  Request  $request
     * @param  string   $type  中间件类型
     */
    public function dispatch(Request $request, string $type = 'route')
    {
        return call_user_func($this->resolve($type), $request);
    }

    /**
     * 解析中间件
     * @access protected
     * @param  mixed  $middleware
     * @param  string $type  中间件类型
     * @return array
     */
    protected function buildMiddleware($middleware, string $type = 'route'): array
    {
        if (is_array($middleware)) {
            list($middleware, $param) = $middleware;
        }

        if ($middleware instanceof \Closure) {
            return [$middleware, $param ?? null];
        }

        if (!is_string($middleware)) {
            throw new InvalidArgumentException('The middleware is invalid');
        }

        if (isset($this->config[$middleware])) {
            $middleware = $this->config[$middleware];
        }

        if (is_array($middleware)) {
            $this->import($middleware, $type);
            return [];
        }

        return [[$middleware, 'handle'], $param ?? null];
    }

    protected function resolve(string $type = 'route')
    {
        return function (Request $request) use ($type) {
            $middleware = array_shift($this->queue[$type]);

            if (null === $middleware) {
                throw new InvalidArgumentException('The queue was exhausted, with no response returned');
            }

            list($call, $param) = $middleware;

            if (is_array($call) && is_string($call[0])) {
                $call = [$this->app->make($call[0]), $call[1]];
            }

            try {
                $response = $this->app->invoke($call, [$request, $this->resolve($type), $param]);
            } catch (HttpResponseException $exception) {
                $response = $exception->getResponse();
            }

            if (!$response instanceof Response) {
                throw new LogicException('The middleware must return Response instance');
            }

            return $response;
        };
    }

}
