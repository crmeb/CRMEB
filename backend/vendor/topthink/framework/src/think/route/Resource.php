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

namespace think\route;

use Closure;
use think\Container;
use think\Route;

/**
 * 资源路由类
 */
class Resource extends RuleGroup
{
    /**
     * REST方法定义
     * @var array
     */
    protected $rest = [];

    /**
     * 模型绑定
     * @var array
     */
    protected $model = [];

    /**
     * 数据验证
     * @var array
     */
    protected $validate = [];

    /**
     * 中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 扩展规则
     * @var Closure
     */
    protected $extend;

    /**
     * 架构函数
     * @access public
     * @param  Route         $router     路由对象
     * @param  RuleGroup     $parent     上级对象
     * @param  string        $name       资源名称
     * @param  string        $route      路由地址
     * @param  array         $rest       资源定义
     */
    public function __construct(Route $router, ?RuleGroup $parent = null, string $name = '', string $route = '', array $rest = [])
    {
        $name         = ltrim($name, '/');
        $this->router = $router;
        $this->parent = $parent;
        $this->rule   = $name;
        $this->route  = $route;
        $this->name   = str_contains($name, '.') ? strstr($name, '.', true) : $name;

        $this->setFullName();

        // 资源路由默认为完整匹配
        $this->option['complete_match'] = true;

        $this->rest = $rest;

        if ($this->parent) {
            $this->domain = $this->parent->getDomain();
            $this->parent->addRuleItem($this);
        }
    }

    /**
     * 扩展额外路由规则
     * @access public
     * @param  Closure $extend 路由规则闭包定义
     * @return $this
     */
    public function extend(?Closure $extend)
    {
        $this->extend = $extend;
        return $this;
    }

    /**
     * 生成资源路由规则
     * @access public
     * @param  mixed $rule 路由规则
     * @return void
     */
    public function parseGroupRule($rule): void
    {
        $option = $this->option;
        $origin = $this->router->getGroup();
        $this->router->setGroup($this);

        if (str_contains($rule, '.')) {
            // 注册嵌套资源路由
            $array = explode('.', $rule);
            $last  = array_pop($array);
            $item  = [];

            foreach ($array as $val) {
                $item[] = $val . '/<' . ($option['var'][$val] ?? $val . '_id') . '>';
            }

            $rule = implode('/', $item) . '/' . $last;
            $id   = $option['var'][$last] ?? 'id';
        } else {
            $id = $option['var'][$rule] ?? 'id';
        }

        $prefix = substr($rule, strlen($this->name) + 1);

        // 注册资源路由
        foreach ($this->rest as $key => $val) {
            if ((isset($option['only']) && !in_array($key, $option['only']))
                || (isset($option['except']) && in_array($key, $option['except']))
            ) {
                continue;
            }

            if (str_contains($val[1], '<id>') && 'id' != $id) {
                $val[1] = str_replace('<id>', '<' . $id . '>', $val[1]);
            }

            $ruleItem = $this->addRule(trim($prefix . $val[1], '/'), $this->route . '/' . $val[2], $val[0]);

            foreach (['model', 'validate', 'middleware', 'pattern'] as $name) {
                if (isset($this->$name[$key])) {
                    call_user_func_array([$ruleItem, $name], (array) $this->$name[$key]);
                }
            }
        }

        if ($this->extend) {
            // 扩展路由规则
            $group = new RuleGroup($this->router, $this, $prefix . '/<' . $id . '>');
            $this->router->setGroup($group);
            Container::getInstance()->invokeFunction($this->extend);
        }

        $this->router->setGroup($origin);
        $this->hasParsed = true;
    }

    /**
     * 设置资源允许
     * @access public
     * @param  array $only 资源允许
     * @return $this
     */
    public function only(array $only)
    {
        return $this->setOption('only', $only);
    }

    /**
     * 设置资源排除
     * @access public
     * @param  array $except 排除资源
     * @return $this
     */
    public function except(array $except)
    {
        return $this->setOption('except', $except);
    }

    /**
     * 设置资源路由的变量
     * @access public
     * @param  array $vars 资源变量
     * @return $this
     */
    public function vars(array $vars)
    {
        return $this->setOption('var', $vars);
    }

    /**
     * 绑定资源验证
     * @access public
     * @param  array|string $name 资源类型或者验证信息
     * @param  array|string $validate 验证信息
     * @return $this
     */
    public function withValidate(array | string $name, array | string $validate = [])
    {
        if (is_array($name)) {
            $this->validate = array_merge($this->validate, $name);
        } else {
            $this->validate[$name] = $validate;
        }

        return $this;
    }

    /**
     * 绑定资源模型
     * @access public
     * @param  array|string $name 资源类型或者模型绑定
     * @param  array|string $model 模型绑定
     * @return $this
     */
    public function withModel(array | string $name, array | string $model = [])
    {
        if (is_array($name)) {
            $this->model = array_merge($this->model, $name);
        } else {
            $this->model[$name] = $model;
        }

        return $this;
    }

    /**
     * 绑定资源中间件
     * @access public
     * @param  array|string $name 资源类型或者中间件定义
     * @param  array|string $middleware 中间件定义
     * @return $this
     */
    public function withMiddleware(array | string $name, array | string $middleware = [])
    {
        if (is_array($name)) {
            $this->middleware = array_merge($this->middleware, $name);
        } else {
            $this->middleware[$name] = $middleware;
        }

        return $this;
    }

    /**
     * rest方法定义和修改
     * @access public
     * @param  array|string  $name 方法名称
     * @param  array|bool    $resource 资源
     * @return $this
     */
    public function rest(array | string $name, array | bool $resource = [])
    {
        if (is_array($name)) {
            $this->rest = $resource ? $name : array_merge($this->rest, $name);
        } else {
            $this->rest[$name] = $resource;
        }

        return $this;
    }
}
