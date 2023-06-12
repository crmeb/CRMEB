<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\route;

use think\helper\Str;
use think\Request;
use think\Route;
use think\route\dispatch\Callback as CallbackDispatch;
use think\route\dispatch\Controller as ControllerDispatch;

/**
 * 域名路由
 */
class Domain extends RuleGroup
{
    /**
     * 架构函数
     * @access public
     * @param  Route       $router   路由对象
     * @param  string      $name     路由域名
     * @param  mixed       $rule     域名路由
     */
    public function __construct(Route $router, string $name = null, $rule = null)
    {
        $this->router = $router;
        $this->domain = $name;
        $this->rule   = $rule;
    }

    /**
     * 检测域名路由
     * @access public
     * @param  Request      $request  请求对象
     * @param  string       $url      访问地址
     * @param  bool         $completeMatch   路由是否完全匹配
     * @return Dispatch|false
     */
    public function check(Request $request, string $url, bool $completeMatch = false)
    {
        // 检测URL绑定
        $result = $this->checkUrlBind($request, $url);

        if (!empty($this->option['append'])) {
            $request->setRoute($this->option['append']);
            unset($this->option['append']);
        }

        if (false !== $result) {
            return $result;
        }

        return parent::check($request, $url, $completeMatch);
    }

    /**
     * 设置路由绑定
     * @access public
     * @param  string     $bind 绑定信息
     * @return $this
     */
    public function bind(string $bind)
    {
        $this->router->bind($bind, $this->domain);

        return $this;
    }

    /**
     * 检测URL绑定
     * @access private
     * @param  Request   $request
     * @param  string    $url URL地址
     * @return Dispatch|false
     */
    private function checkUrlBind(Request $request, string $url)
    {
        $bind = $this->router->getDomainBind($this->domain);

        if ($bind) {
            $this->parseBindAppendParam($bind);

            // 如果有URL绑定 则进行绑定检测
            $type = substr($bind, 0, 1);
            $bind = substr($bind, 1);

            $bindTo = [
                '\\' => 'bindToClass',
                '@'  => 'bindToController',
                ':'  => 'bindToNamespace',
            ];

            if (isset($bindTo[$type])) {
                return $this->{$bindTo[$type]}($request, $url, $bind);
            }
        }

        return false;
    }

    protected function parseBindAppendParam(string &$bind): void
    {
        if (false !== strpos($bind, '?')) {
            [$bind, $query] = explode('?', $bind);
            parse_str($query, $vars);
            $this->append($vars);
        }
    }

    /**
     * 绑定到类
     * @access protected
     * @param  Request   $request
     * @param  string    $url URL地址
     * @param  string    $class 类名（带命名空间）
     * @return CallbackDispatch
     */
    protected function bindToClass(Request $request, string $url, string $class): CallbackDispatch
    {
        $array  = explode('|', $url, 2);
        $action = !empty($array[0]) ? $array[0] : $this->router->config('default_action');
        $param  = [];

        if (!empty($array[1])) {
            $this->parseUrlParams($array[1], $param);
        }

        return new CallbackDispatch($request, $this, [$class, $action], $param);
    }

    /**
     * 绑定到命名空间
     * @access protected
     * @param  Request   $request
     * @param  string    $url URL地址
     * @param  string    $namespace 命名空间
     * @return CallbackDispatch
     */
    protected function bindToNamespace(Request $request, string $url, string $namespace): CallbackDispatch
    {
        $array  = explode('|', $url, 3);
        $class  = !empty($array[0]) ? $array[0] : $this->router->config('default_controller');
        $method = !empty($array[1]) ? $array[1] : $this->router->config('default_action');
        $param  = [];

        if (!empty($array[2])) {
            $this->parseUrlParams($array[2], $param);
        }

        return new CallbackDispatch($request, $this, [$namespace . '\\' . Str::studly($class), $method], $param);
    }

    /**
     * 绑定到控制器
     * @access protected
     * @param  Request   $request
     * @param  string    $url URL地址
     * @param  string    $controller 控制器名
     * @return ControllerDispatch
     */
    protected function bindToController(Request $request, string $url, string $controller): ControllerDispatch
    {
        $array  = explode('|', $url, 2);
        $action = !empty($array[0]) ? $array[0] : $this->router->config('default_action');
        $param  = [];

        if (!empty($array[1])) {
            $this->parseUrlParams($array[1], $param);
        }

        return new ControllerDispatch($request, $this, $controller . '/' . $action, $param);
    }

}
