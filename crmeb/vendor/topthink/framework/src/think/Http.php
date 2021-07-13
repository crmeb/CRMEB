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

use think\event\HttpEnd;
use think\event\HttpRun;
use think\event\RouteLoaded;
use think\exception\Handle;
use Throwable;

/**
 * Web应用管理类
 * @package think
 */
class Http
{

    /**
     * @var App
     */
    protected $app;

    /**
     * 应用名称
     * @var string
     */
    protected $name;

    /**
     * 应用路径
     * @var string
     */
    protected $path;

    /**
     * 是否绑定应用
     * @var bool
     */
    protected $isBind = false;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->routePath = $this->app->getRootPath() . 'route' . DIRECTORY_SEPARATOR;
    }

    /**
     * 设置应用名称
     * @access public
     * @param string $name 应用名称
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 获取应用名称
     * @access public
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?: '';
    }

    /**
     * 设置应用目录
     * @access public
     * @param string $path 应用目录
     * @return $this
     */
    public function path(string $path)
    {
        if (substr($path, -1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        $this->path = $path;
        return $this;
    }

    /**
     * 获取应用路径
     * @access public
     * @return string
     */
    public function getPath(): string
    {
        return $this->path ?: '';
    }

    /**
     * 获取路由目录
     * @access public
     * @return string
     */
    public function getRoutePath(): string
    {
        return $this->routePath;
    }

    /**
     * 设置路由目录
     * @access public
     * @param string $path 路由定义目录
     * @return string
     */
    public function setRoutePath(string $path): void
    {
        $this->routePath = $path;
    }

    /**
     * 设置应用绑定
     * @access public
     * @param bool $bind 是否绑定
     * @return $this
     */
    public function setBind(bool $bind = true)
    {
        $this->isBind = $bind;
        return $this;
    }

    /**
     * 是否绑定应用
     * @access public
     * @return bool
     */
    public function isBind(): bool
    {
        return $this->isBind;
    }

    /**
     * 执行应用程序
     * @access public
     * @param Request|null $request
     * @return Response
     */
    public function run(Request $request = null): Response
    {
        //自动创建request对象
        $request = $request ?? $this->app->make('request', [], true);
        $this->app->instance('request', $request);

        try {
            $response = $this->runWithRequest($request);
        } catch (Throwable $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        }

        return $response;
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
        if (!$this->app->initialized()) {
            $this->app->initialize();
        }
    }

    /**
     * 执行应用程序
     * @param Request $request
     * @return mixed
     */
    protected function runWithRequest(Request $request)
    {
        $this->initialize();

        // 加载全局中间件
        $this->loadMiddleware();

        // 监听HttpRun
        $this->app->event->trigger(HttpRun::class);

        return $this->app->middleware->pipeline()
            ->send($request)
            ->then(function ($request) {
                return $this->dispatchToRoute($request);
            });
    }

    protected function dispatchToRoute($request)
    {
        $withRoute = $this->app->config->get('app.with_route', true) ? function () {
            $this->loadRoutes();
        } : null;

        return $this->app->route->dispatch($request, $withRoute);
    }

    /**
     * 加载全局中间件
     */
    protected function loadMiddleware(): void
    {
        if (is_file($this->app->getBasePath() . 'middleware.php')) {
            $this->app->middleware->import(include $this->app->getBasePath() . 'middleware.php');
        }
    }

    /**
     * 加载路由
     * @access protected
     * @return void
     */
    protected function loadRoutes(): void
    {
        // 加载路由定义
        $routePath = $this->getRoutePath();

        if (is_dir($routePath)) {
            $files = glob($routePath . '*.php');
            foreach ($files as $file) {
                include $file;
            }
        }

        $this->app->event->trigger(RouteLoaded::class);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param Throwable $e
     * @return void
     */
    protected function reportException(Throwable $e)
    {
        $this->app->make(Handle::class)->report($e);
    }

    /**
     * Render the exception to a response.
     *
     * @param Request   $request
     * @param Throwable $e
     * @return Response
     */
    protected function renderException($request, Throwable $e)
    {
        return $this->app->make(Handle::class)->render($request, $e);
    }

    /**
     * HttpEnd
     * @param Response $response
     * @return void
     */
    public function end(Response $response): void
    {
        $this->app->event->trigger(HttpEnd::class, $response);

        //执行中间件
        $this->app->middleware->end($response);

        // 写入日志
        $this->app->log->save();
    }

}
