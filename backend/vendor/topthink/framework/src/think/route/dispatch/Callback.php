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

use think\App;
use think\exception\ClassNotFoundException;
use think\helper\Str;
use think\route\Dispatch;

/**
 * Callback Dispatcher
 */
class Callback extends Dispatch
{
    /**
     * 类名
     * @var string
     */
    protected $class;

    /**
     * 操作名
     * @var string
     */
    protected $action;

    public function init(App $app)
    {
        $this->app = $app;
        if (is_array($this->dispatch)) {
            $this->parseDispatch();
        }
        $this->doRouteAfter();
    }

    protected function parseDispatch()
    {
        // 执行回调方法
        [$class, $action] = $this->dispatch;
        if ($this->miss && !method_exists($class, $action . $this->rule->config('action_suffix'))) {
            $route = $this->miss->getRoute();
            if (is_string($route)) {
                $route = explode('/', $route, 3);
            }
            if (is_array($route)) {
                // 检查分组命名空间绑定
                $bind = $this->rule->getBind();
                $type = substr($bind, 0, 1);
                if ('\\' == $type) {
                    $class  = substr($bind, 1);
                    $action = $route[0];
                } elseif (':' == $type) {
                    [$class, $action] = $route;

                    $namespace = substr($bind, 1);
                    $class     = trim($namespace, '\\') . '\\' . Str::studly($class);
                }
            } else {
                $vars = $this->getActionBindVars();
                return $this->app->invoke($route, $vars);
            }
        }

        // 设置当前请求的控制器、操作
        $controllerLayer = $this->rule->config('controller_layer') ?: 'controller';
        if (str_contains($class, '\\' . $controllerLayer . '\\')) {
            [$layer, $controller] = explode('/' . $controllerLayer . '/', trim(str_replace('\\', '/', $class), '/'));
            $layer                = trim(str_replace('app', '', $layer), '/');
        } else {
            $layer      = '';
            $controller = trim(str_replace('\\', '/', $class), '/');
        }

        if ($layer && !empty($this->option['auto_middleware'])) {
            // 自动为顶层layer注册中间件
            $alias = $this->app->config->get('middleware.alias', []);

            if (isset($alias[$layer])) {
                $this->option['middleware'] = array_merge($this->option['middleware'] ?? [], [$layer]);
            }
        }

        $this->action = $action;
        $this->class  = $class;

        $this->request
            ->setLayer($layer)
            ->setController($controller)
            ->setAction($action);
    }

    public function exec()
    {
        if (is_array($this->dispatch)) {
            if (class_exists($this->class)) {
                $instance = $this->app->invokeClass($this->class);
            } else {
                throw new ClassNotFoundException('class not exists:' . $this->class, $this->class);
            }

            return $this->responseWithMiddlewarePipeline($instance, $this->action);            
        }

        $vars = $this->getActionBindVars();
        return $this->app->invoke($this->dispatch, $vars);
    }
}
