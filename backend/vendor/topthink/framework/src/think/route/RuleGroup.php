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
use DirectoryIterator;
use think\Container;
use think\Exception;
use think\helper\Str;
use think\Request;
use think\Route;
use think\route\dispatch\Callback as CallbackDispatch;
use think\route\dispatch\Controller as ControllerDispatch;

/**
 * 路由分组类
 */
class RuleGroup extends Rule
{
    /**
     * 分组路由（包括子分组）
     * @var Rule[]
     */
    protected $rules = [];

    /**
     * MISS路由
     * @var RuleItem
     */
    protected $miss;

    /**
     * 完整名称
     * @var string
     */
    protected $fullName;

    /**
     * 分组别名
     * @var string
     */
    protected $alias;

    /**
     * 分组子目录
     * @var string
     */
    protected $sub;

    /**
     * 分组绑定
     * @var string
     */
    protected $bind;

    /**
     * 是否已经解析
     * @var bool
     */
    protected $hasParsed;

    /**
     * 架构函数
     * @access public
     * @param  Route     $router 路由对象
     * @param  RuleGroup $parent 上级对象
     * @param  string    $name   分组名称
     * @param  mixed     $rule   分组路由
     * @param  bool      $lazy   延迟解析
     */
    public function __construct(Route $router, ?RuleGroup $parent = null, string $name = '', $rule = null, bool $lazy = false)
    {
        $this->router = $router;
        $this->parent = $parent;
        $this->rule   = $rule;
        $this->name   = trim($name, '/');

        if ($name && is_string($rule) || is_null($rule)) {
            if ($rule && is_subclass_of($rule, Dispatch::class, false)) {
                $this->dispatcher($rule);
                $this->rule = '';
            } else {
                $this->sub  =  $rule ?: $this->name;
            }
        }

        $this->setFullName();

        if ($this->parent) {
            $this->domain = $this->parent->getDomain();
            $this->parent->addRuleItem($this);
        }

        if (!$lazy) {
            $this->parseGroupRule($rule);
        }
    }

    /**
     * 设置分组的路由规则
     * @access public
     * @return void
     */
    protected function setFullName(): void
    {
        if (str_contains($this->name, ':')) {
            $this->name = preg_replace(['/\[\:(\w+)\]/', '/\:(\w+)/'], ['<\1?>', '<\1>'], $this->name);
        }

        if ($this->parent && $this->parent->getFullName()) {
            $this->fullName = $this->parent->getFullName() . ($this->name ? '/' . $this->name : '');
            if ($this->sub) {
                $this->sub  = $this->parent->getFullName() . '/' . $this->sub;
            }
        } else {
            $this->fullName = $this->name;
        }

        if ($this->name) {
            $this->router->getRuleName()->setGroup($this->name, $this);
        }
    }

    /**
     * 获取所属域名
     * @access public
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain ?: '-';
    }

    /**
     * 获取分组别名
     * @access public
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias ?: '';
    }

    /**
     * 自动加载分组路由
     * @access protected
     * @param  string  $dir 目录名
     * @return void
     */
    protected function loadRoutes(string $dir): void
    {
        $routePath = root_path('route' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR);
        if (is_dir($routePath)) {
            // 动态加载分组路由
            $files = glob($routePath . '*.php');
            foreach ($files as $file) {
                include_once $file;
            }

            // 自动扫描下级分组
            $dirs = $this->config('route_auto_group') ? glob($routePath . '*', GLOB_ONLYDIR) : [];
            foreach ($dirs as $dir) {
                $groupName = str_replace('\\', '/', substr_replace($dir, '', 0, strlen($routePath)));
                if (!$this->router->getRuleName()->hasGroup($groupName)) {
                    $this->router->group($groupName);
                }
            }
        }
    }

    /**
     * 检测分组路由
     * @access public
     * @param  Request $request       请求对象
     * @param  string  $url           访问地址
     * @param  bool    $completeMatch 路由是否完全匹配
     * @return Dispatch|false
     */
    public function check(Request $request, string $url, bool $completeMatch = false)
    {
        // 检查分组有效性
        if (!$this->checkOption($this->option, $request) || !$this->checkUrl($url)) {
            return false;
        }

        // 解析分组路由
        if (!$this->hasParsed) {
            $this->parseGroupRule($this->rule);
        }

        // 获取当前路由规则
        $method = strtolower($request->method());
        $rules  = $this->getRules($method);
        $option = $this->getOption();

        if (isset($option['complete_match'])) {
            $completeMatch = $option['complete_match'];
        }

        if (!empty($option['merge_rule_regex'])) {
            // 路由合并检查
            $result = $this->checkMergeRuleRegex($request, $rules, $url, $completeMatch);

            if (false !== $result) {
                return $result;
            }
        } else {
            // 检查分组路由
            foreach ($rules as $item) {
                $result = $item->check($request, $url, $completeMatch);

                if (false !== $result) {
                    return $result;
                }
            }
        }

        $miss = $this->getMissRule($method);
        if ($this->bind) {
            // 检查分组绑定
            return $this->checkBind($request, $url, $option, $miss);
        }

        if ($miss) {
            // MISS路由
            return $miss->parseRule($request, '', $miss->getRoute(), $url, $miss->getOption());
        }

        return false;
    }

    /**
     * 分组URL匹配检查
     * @access protected
     * @param  string $url URL
     * @return bool
     */
    protected function checkUrl(string $url): bool
    {
        $url = str_replace('|', '/', $url);
        if (!$this->config('url_route_must')) {
            $item = $this->router->getRuleName()->getName($url);
            if (!empty($item) && $item[0]['rule'] != $url){
                // 定义过路由地址的 不支持访问
                return false;
            }
        }

        if ($this->fullName) {
            $pos = strpos($this->fullName, '<');

            if (false !== $pos) {
                $str = substr($this->fullName, 0, $pos);
            } else {
                $str = $this->fullName;
            }

            if ($str && 0 !== stripos($url . '/', $str . '/')) {
                return false;
            }
        }

        return true;
    }

    /**
     * 设置路由分组别名
     * @access public
     * @param  string $alias 路由分组别名
     * @return $this
     */
    public function alias(string $alias)
    {
        $this->alias = $alias;
        $this->router->getRuleName()->setGroup($alias, $this);

        return $this;
    }

    /**
     * 解析分组和域名的路由规则及绑定
     * @access public
     * @param  mixed $rule 路由规则
     * @return void
     */
    public function parseGroupRule($rule): void
    {
        $origin = $this->router->getGroup();
        $this->router->setGroup($this);

        if ($rule instanceof Closure) {
            Container::getInstance()->invokeFunction($rule);
        } elseif ($this->sub) {
            $this->loadRoutes($this->sub);
        }

        $this->router->setGroup($origin);
        $this->hasParsed = true;
    }

    /**
     * 检测分组路由
     * @access public
     * @param  Request $request       请求对象
     * @param  array   $rules         路由规则
     * @param  string  $url           访问地址
     * @param  bool    $completeMatch 路由是否完全匹配
     * @return Dispatch|false
     */
    protected function checkMergeRuleRegex(Request $request, array &$rules, string $url, bool $completeMatch)
    {
        $depr  = $this->config('pathinfo_depr');
        $url   = $depr . str_replace('|', $depr, $url);
        $regex = [];
        $items = [];

        foreach ($rules as $key => $item) {
            if ($item instanceof RuleItem) {
                $rule = $depr . str_replace('/', $depr, $item->getRule());
                if ($depr == $rule && $depr != $url) {
                    unset($rules[$key]);
                    continue;
                }

                $complete = $item->getOption('complete_match', $completeMatch);

                if (!str_contains($rule, '<')) {
                    if (0 === strcasecmp($rule, $url) || (!$complete && 0 === strncasecmp($rule, $url, strlen($rule)))) {
                        return $item->checkRule($request, $url, []);
                    }

                    unset($rules[$key]);
                    continue;
                }

                $slash = preg_quote('/-' . $depr, '/');

                if ($matchRule = preg_split('/[' . $slash . ']<\w+\??>/', $rule, 2)) {
                    if ($matchRule[0] && 0 !== strncasecmp($rule, $url, strlen($matchRule[0]))) {
                        unset($rules[$key]);
                        continue;
                    }
                }

                if (preg_match_all('/[' . $slash . ']?<?\w+\??>?/', $rule, $matches)) {
                    unset($rules[$key]);
                    $pattern = array_merge($this->getPattern(), $item->getPattern());
                    $option  = array_merge($this->getOption(), $item->getOption());

                    $regex[$key] = $this->buildRuleRegex($rule, $matches[0], $pattern, $option, $complete, '_THINK_' . $key);
                    $items[$key] = $item;
                }
            } elseif ($item instanceof RuleGroup) {
                $array = $item->getrules();
                return $this->checkMergeRuleRegex($request, $array, ltrim($url, $depr), $completeMatch);
            }
        }

        if (empty($regex)) {
            return false;
        }

        try {
            $result = preg_match('~^(?:' . implode('|', $regex) . ')~u', $url, $match);
        } catch (\Exception $e) {
            throw new Exception('route pattern error');
        }

        if ($result) {
            $var = [];
            foreach ($match as $key => $val) {
                if (is_string($key) && '' !== $val) {
                    [$name, $pos] = explode('_THINK_', $key);

                    $var[$name] = $val;
                }
            }

            if (!isset($pos)) {
                foreach ($regex as $key => $item) {
                    if (str_starts_with(str_replace(['\/', '\-', '\\' . $depr], ['/', '-', $depr], $item), $match[0])) {
                        $pos = $key;
                        break;
                    }
                }
            }

            $rule  = $items[$pos]->getRule();
            $array = $this->router->getRule($rule);

            foreach ($array as $item) {
                if (in_array($item->getMethod(), ['*', strtolower($request->method())])) {
                    $result = $item->checkRule($request, $url, $var);

                    if (false !== $result) {
                        return $result;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 注册MISS路由
     * @access public
     * @param  string|Closure $route  路由地址
     * @param  string         $method 请求类型
     * @return RuleItem
     */
    public function miss(string | Closure $route, string $method = '*'): RuleItem
    {
        // 创建路由规则实例
        $method   = strtolower($method);
        $ruleItem = new RuleItem($this->router, $this, null, '', $route, $method);

        $this->miss[$method] = $ruleItem->setMiss();

        return $ruleItem;
    }

    /**
     * 获取分组下的MISS路由
     * @access public
     * @param  string $method 请求类型
     * @return RuleItem|null
     */
    public function getMissRule(string $method = '*'): ?RuleItem
    {
        if (isset($this->miss[$method])) {
            $miss = $this->miss[$method];
        } elseif (isset($this->miss['*'])) {
            $miss = $this->miss['*'];
        }
        return $miss ?? null;
    }

    /**
     * 分组自动URL调度  默认绑定到当前分组名所在的控制器分级
     * @access public
     * @param  string       $bind 绑定资源 绑定规则 class @controller :namespace /layer
     * @param  string|array $middleware 中间件
     * @return $this
     */
    public function auto(string $bind = '', string | array $middleware = '')
    {
        $this->bind = $bind ?: '/' . $this->getFullName();
        if ($middleware) {
            $this->middleware($middleware);
        }

        return $this;
    }

    /**
     * 分组绑定到类
     * @access public
     * @param  string $class
     * @param  bool   $prefix
     * @return $this
     */
    public function class(string $class, bool $prefix = true)
    {
        $this->bind = '\\' . $class;
        if ($prefix) {
            $this->prefix('\\' . $class . '@');
        }
        return $this;
    }

    /**
     * 分组绑定到控制器
     * @access public
     * @param  string $controller
     * @param  bool   $prefix
     * @return $this
     */
    public function controller(string $controller, bool $prefix = true)
    {
        $this->bind = '@' . $controller;
        if ($prefix) {
            $this->prefix($controller . '/');
        }
        return $this;
    }

    /**
     * 分组绑定到命名空间
     * @access public
     * @param  string $namespace
     * @param  bool   $prefix
     * @return $this
     */
    public function namespace(string $namespace, bool $prefix = true)
    {
        $this->bind = ':' . $namespace;
        if ($prefix) {
            $this->prefix($namespace . '\\');
        }
        return $this;
    }

    /**
     * 分组绑定到控制器分级
     * @access public
     * @param  string $namespace
     * @param  bool   $prefix
     * @return $this
     */
    public function layer(string $layer, bool $prefix = true)
    {
        $this->bind = '/' . $layer;
        if ($prefix) {
            $this->prefix($layer . '/');
        }
        return $this;
    }

    /**
     * 获取分组绑定信息
     * @access public
     * @return string
     */
    public function getBind()
    {
        return $this->bind ?? '';
    }

    /**
     * 检测URL绑定
     * @access private
     * @param  Request   $request
     * @param  string    $url URL地址
     * @param  array     $option 分组参数
     * @param  RuleItem  $miss
     * @return Dispatch
     */
    public function checkBind(Request $request, string $url, array $option = [], ?RuleItem $miss = null): Dispatch
    {
        [$bind, $param] = $this->parseBindAppendParam($this->bind);

        [$call, $bind] = match (substr($bind, 0, 1)) {
            '\\' => ['bindToClass', substr($bind, 1)],
            '@' => ['bindToController', substr($bind, 1)],
            '/' => ['bindToLayer', substr($bind, 1)],
            ':' => ['bindToNamespace', substr($bind, 1)],
            default => ['bindToClass', $bind],
        };

        $name = $this->getFullName();
        $url  = trim(substr(str_replace('|', '/', $url), strlen($name)), '/');

        return $this->$call($request, $url, $bind, $param, $option, $miss);
    }

    protected function parseBindAppendParam(string $bind)
    {
        $vars = [];
        if (str_contains($bind, '?')) {
            [$bind, $query] = explode('?', $bind);
            parse_str($query, $vars);
        }
        return [$bind, $vars];
    }

    /**
     * 绑定到类
     * @access protected
     * @param  Request   $request
     * @param  string    $url URL地址
     * @param  string    $class 类名（带命名空间）
     * @param  array     $param  路由变量
     * @param  array     $option 分组参数
     * @param  RuleItem  $miss
     * @return CallbackDispatch
     */
    protected function bindToClass(Request $request, string $url, string $class, array $param = [], array $option = [], ?RuleItem $miss = null): CallbackDispatch
    {
        $array  = explode('/', $url, 2);
        $action = !empty($array[0]) ? $array[0] : $this->config('default_action');

        if (!empty($array[1])) {
            $this->parseUrlParams($array[1], $param);
        }

        return new CallbackDispatch($request, $this, [$class, $action], $param, $option, $miss);
    }

    /**
     * 绑定到命名空间
     * @access protected
     * @param  Request   $request
     * @param  string    $url URL地址
     * @param  string    $namespace 命名空间
     * @param  array     $param  路由变量
     * @param  array     $option 分组参数
     * @param  RuleItem  $miss
     * @return CallbackDispatch
     */
    protected function bindToNamespace(Request $request, string $url, string $namespace, array $param = [], array $option = [], ?RuleItem $miss = null): CallbackDispatch
    {
        $array  = explode('/', $url, 3);
        $class  = !empty($array[0]) ? $array[0] : $this->config('default_controller');
        $method = !empty($array[1]) ? $array[1] : $this->config('default_action');
        $class .= $this->config('controller_suffix') ? 'Controller' : '';

        if (!empty($array[2])) {
            $this->parseUrlParams($array[2], $param);
        }

        return new CallbackDispatch($request, $this, [trim($namespace, '\\') . '\\' . Str::studly($class), $method], $param, $option, $miss);
    }

    /**
     * 绑定到控制器
     * @access protected
     * @param  Request   $request
     * @param  string    $url URL地址
     * @param  string    $controller 控制器名
     * @param  array     $param  路由变量
     * @param  array     $option 分组参数
     * @param  RuleItem  $miss
     * @return ControllerDispatch
     */
    protected function bindToController(Request $request, string $url, string $controller, array $param = [], array $option = [], ?RuleItem $miss = null): ControllerDispatch
    {
        $array  = explode('/', $url, 2);
        $action = !empty($array[0]) ? $array[0] : $this->config('default_action');

        if (!empty($array[1])) {
            $this->parseUrlParams($array[1], $param);
        }

        return new ControllerDispatch($request, $this, [$controller, $action], $param, $option, $miss);
    }

    /**
     * 绑定到控制器分级
     * @access protected
     * @param  Request   $request
     * @param  string    $url URL地址
     * @param  string    $controller 控制器名
     * @param  array     $param  路由变量
     * @param  array     $option 分组参数
     * @param  RuleItem  $miss
     * @return ControllerDispatch
     */
    protected function bindToLayer(Request $request, string $url, string $layer, array $param = [], array $option = [], ?RuleItem $miss = null): ControllerDispatch
    {
        $array      = explode('/', $url, 3);
        $controller = !empty($array[0]) ? $array[0] : $this->config('default_controller');
        $action     = !empty($array[1]) ? $array[1] : $this->config('default_action');

        if (!empty($array[2])) {
            $this->parseUrlParams($array[2], $param);
        }

        return new ControllerDispatch($request, $this, [$layer, $controller, $action], $param, $option, $miss);
    }

    /**
     * 添加分组下的路由规则
     * @access public
     * @param  string $rule   路由规则
     * @param  mixed  $route  路由地址
     * @param  string $method 请求类型
     * @return RuleItem
     */
    public function addRule(string $rule, $route = null, string $method = '*'): RuleItem
    {
        // 读取路由标识
        $name   = is_string($route) ? $route : null;
        $method = strtolower($method);
        if ('' === $rule || '/' === $rule) {
            $rule .= '$';
        }

        // 创建路由规则实例
        $ruleItem = new RuleItem($this->router, $this, $name, $rule, $route, $method);

        $this->addRuleItem($ruleItem);

        return $ruleItem;
    }

    /**
     * 注册分组下的路由规则
     * @access public
     * @param  Rule   $rule   路由规则
     * @return $this
     */
    public function addRuleItem(Rule $rule)
    {
        $this->rules[] = $rule;
        return $this;
    }

    /**
     * 设置分组的路由前缀
     * @access public
     * @param  string $prefix 路由前缀
     * @return $this
     */
    public function prefix(string $prefix)
    {
        if ($this->parent && $this->parent->getOption('prefix')) {
            $prefix = $this->parent->getOption('prefix') . $prefix;
        }

        return $this->setOption('prefix', $prefix);
    }

    /**
     * 合并分组的路由规则正则
     * @access public
     * @param  bool $merge 是否合并
     * @return $this
     */
    public function mergeRuleRegex(bool $merge = true)
    {
        return $this->setOption('merge_rule_regex', $merge);
    }

    /**
     * 设置分组的Dispatch调度
     * @access public
     * @param  string $dispatch 调度类
     * @return $this
     */
    public function dispatcher(string $dispatch)
    {
        return $this->setOption('dispatcher', $dispatch);
    }

    /**
     * 获取完整分组Name
     * @access public
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName ?: '';
    }

    /**
     * 获取分组的路由规则
     * @access public
     * @param  string $method 请求类型
     * @return array
     */
    public function getRules(string $method = ''): array
    {
        if ('' === $method) {
            return $this->rules;
        }

        return array_filter($this->rules, function ($item) use ($method) {
            $ruleMethod = $item->getMethod();
            return '*' == $ruleMethod || str_contains($ruleMethod, $method);
        });
    }

    /**
     * 清空分组下的路由规则
     * @access public
     * @return void
     */
    public function clear(): void
    {
        $this->rules = [];
    }
}
