<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\route\dispatch;

use Closure;
use think\App;
use think\exception\ClassNotFoundException;
use think\exception\HttpException;
use think\helper\Str;
use think\route\Dispatch;

/**
 * Controller Dispatcher
 */
class Controller extends Dispatch
{
    /**
     * 控制器名
     * @var string
     */
    protected $controller;

    /**
     * 操作名
     * @var string
     */
    protected $actionName;

    public function init(App $app)
    {
        $this->app = $app;
        $this->parseDispatch();
        $this->doRouteAfter();
    }

    protected function parseDispatch()
    {
        $path = $this->dispatch;
        if (is_string($path)) {
            $path = explode('/', $path);
        }

        $action     = !empty($path) ? array_pop($path) : $this->rule->config('default_action');
        $controller = !empty($path) ? array_pop($path) : $this->rule->config('default_controller');
        $layer      = !empty($path) ? implode('/', $path) : '';

        if ($layer && !empty($this->option['auto_middleware'])) {
            // 自动为顶层layer注册中间件
            $alias = $this->app->config->get('middleware.alias', []);

            if (isset($alias[$layer])) {
                $this->option['middleware'] = array_merge($this->option['middleware'] ?? [], [$layer]);
            }
        }

        // 获取控制器名和分层（目录）名
        if (str_contains($controller, '.')) {
            $pos        = strrpos($controller, '.');
            $layer      = ($layer ? $layer . '.' : '') . substr($controller, 0, $pos);
            $controller = Str::studly(substr($controller, $pos + 1));
        } else {
            $controller = Str::studly($controller);
        }

        $this->actionName = strip_tags($action);
        $this->controller = strip_tags(($layer ? $layer . '.' : '') . $controller);

        // 设置当前请求的控制器、操作
        $this->request
            ->setLayer(strip_tags($layer))
            ->setController($this->controller)
            ->setAction($this->actionName);
    }

    public function exec()
    {
        try {
            // 实例化控制器
            $instance = $this->controller($this->controller);
            if ($this->miss && !method_exists($instance, $this->actionName . $this->rule->config('action_suffix'))) {
                throw new ClassNotFoundException('class not exists:');
            }
        } catch (ClassNotFoundException $e) {
            if ($this->miss) {
                $route = $this->miss->getRoute();
                if ($route instanceof Closure) {
                    $vars = $this->getActionBindVars();
                    return $this->app->invoke($route, $vars);
                }
                // 检查分组绑定
                $prefix = $this->rule->getOption('prefix');
                if (!str_starts_with($route, $prefix)) {
                    $route = $prefix . $route;
                }
                $this->parseDispatch($route);
                $instance = $this->controller($this->controller);
            } else {
                throw new HttpException(404, 'controller not exists:' . $e->getClass());
            }
        }

        return $this->responseWithMiddlewarePipeline($instance, $this->actionName);
    }

    /**
     * 实例化访问控制器
     * @access public
     * @param string $name 资源地址
     * @return object
     * @throws ClassNotFoundException
     */
    public function controller(string $name)
    {
        $suffix = $this->rule->config('controller_suffix') ? 'Controller' : '';

        $controllerLayer = $this->rule->config('controller_layer') ?: 'controller';
        $emptyController = $this->rule->config('empty_controller') ?: 'Error';

        $class = $this->app->parseClass($controllerLayer, $name . $suffix);

        if (class_exists($class)) {
            return $this->app->make($class, [], true);
        } elseif ($emptyController && class_exists($emptyClass = $this->app->parseClass($controllerLayer, $emptyController . $suffix))) {
            return $this->app->make($emptyClass, [], true);
        }

        throw new ClassNotFoundException('class not exists:' . $class, $class);
    }
}
