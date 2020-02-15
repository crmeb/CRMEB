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

use Closure;
use InvalidArgumentException;
use LogicException;
use think\exception\Handle;
use Throwable;

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
     * 应用对象
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 导入中间件
     * @access public
     * @param array  $middlewares
     * @param string $type 中间件类型
     * @return void
     */
    public function import(array $middlewares = [], string $type = 'global'): void
    {
        foreach ($middlewares as $middleware) {
            $this->add($middleware, $type);
        }
    }

    /**
     * 注册中间件
     * @access public
     * @param mixed  $middleware
     * @param string $type 中间件类型
     * @return void
     */
    public function add($middleware, string $type = 'global'): void
    {
        $middleware = $this->buildMiddleware($middleware, $type);

        if ($middleware) {
            $this->queue[$type][] = $middleware;
            $this->queue[$type]   = array_unique($this->queue[$type], SORT_REGULAR);
        }
    }

    /**
     * 注册路由中间件
     * @access public
     * @param mixed $middleware
     * @return void
     */
    public function route($middleware): void
    {
        $this->add($middleware, 'route');
    }

    /**
     * 注册控制器中间件
     * @access public
     * @param mixed $middleware
     * @return void
     */
    public function controller($middleware): void
    {
        $this->add($middleware, 'controller');
    }

    /**
     * 注册中间件到开始位置
     * @access public
     * @param mixed  $middleware
     * @param string $type 中间件类型
     */
    public function unshift($middleware, string $type = 'global')
    {
        $middleware = $this->buildMiddleware($middleware, $type);

        if (!empty($middleware)) {
            if (!isset($this->queue[$type])) {
                $this->queue[$type] = [];
            }

            array_unshift($this->queue[$type], $middleware);
        }
    }

    /**
     * 获取注册的中间件
     * @access public
     * @param string $type 中间件类型
     * @return array
     */
    public function all(string $type = 'global'): array
    {
        return $this->queue[$type] ?? [];
    }

    /**
     * 调度管道
     * @access public
     * @param string $type 中间件类型
     * @return Pipeline
     */
    public function pipeline(string $type = 'global')
    {
        return (new Pipeline())
            ->through(array_map(function ($middleware) {
                return function ($request, $next) use ($middleware) {
                    list($call, $param) = $middleware;
                    if (is_array($call) && is_string($call[0])) {
                        $call = [$this->app->make($call[0]), $call[1]];
                    }
                    $response = call_user_func($call, $request, $next, $param);

                    if (!$response instanceof Response) {
                        throw new LogicException('The middleware must return Response instance');
                    }
                    return $response;
                };
            }, $this->sortMiddleware($this->queue[$type] ?? [])))
            ->whenException([$this, 'handleException']);
    }

    /**
     * 结束调度
     * @param Response $response
     */
    public function end(Response $response)
    {
        foreach ($this->queue as $queue) {
            foreach ($queue as $middleware) {
                list($call) = $middleware;
                if (is_array($call) && is_string($call[0])) {
                    $instance = $this->app->make($call[0]);
                    if (method_exists($instance, 'end')) {
                        $instance->end($response);
                    }
                }
            }
        }
    }

    /**
     * 异常处理
     * @param Request   $passable
     * @param Throwable $e
     * @return Response
     */
    public function handleException($passable, Throwable $e)
    {
        /** @var Handle $handler */
        $handler = $this->app->make(Handle::class);

        $handler->report($e);

        $response = $handler->render($passable, $e);

        return $response;
    }

    /**
     * 解析中间件
     * @access protected
     * @param mixed  $middleware
     * @param string $type 中间件类型
     * @return array
     */
    protected function buildMiddleware($middleware, string $type): array
    {
        if (is_array($middleware)) {
            list($middleware, $param) = $middleware;
        }

        if ($middleware instanceof Closure) {
            return [$middleware, $param ?? null];
        }

        if (!is_string($middleware)) {
            throw new InvalidArgumentException('The middleware is invalid');
        }

        //中间件别名检查
        $alias = $this->app->config->get('middleware.alias', []);

        if (isset($alias[$middleware])) {
            $middleware = $alias[$middleware];
        }

        if (is_array($middleware)) {
            $this->import($middleware, $type);
            return [];
        }

        return [[$middleware, 'handle'], $param ?? null];
    }

    /**
     * 中间件排序
     * @param array $middlewares
     * @return array
     */
    protected function sortMiddleware(array $middlewares)
    {
        $priority = $this->app->config->get('middleware.priority', []);
        uasort($middlewares, function ($a, $b) use ($priority) {
            $aPriority = $this->getMiddlewarePriority($priority, $a);
            $bPriority = $this->getMiddlewarePriority($priority, $b);
            return $bPriority - $aPriority;
        });

        return $middlewares;
    }

    /**
     * 获取中间件优先级
     * @param $priority
     * @param $middleware
     * @return int
     */
    protected function getMiddlewarePriority($priority, $middleware)
    {
        list($call) = $middleware;
        if (is_array($call) && is_string($call[0])) {
            $index = array_search($call[0], array_reverse($priority));
            return false === $index ? -1 : $index;
        }
        return -1;
    }

}
