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

use think\Exception;
use think\Request;
use think\Route;

/**
 * 路由规则类
 */
class RuleItem extends Rule
{
    /**
     * 是否为MISS规则
     * @var bool
     */
    protected $miss = false;

    /**
     * 是否为额外自动注册的OPTIONS规则
     * @var bool
     */
    protected $autoOption = false;

    /**
     * 架构函数
     * @access public
     * @param  Route             $router 路由实例
     * @param  RuleGroup         $parent 上级对象
     * @param  string            $name 路由标识
     * @param  string            $rule 路由规则
     * @param  string|\Closure   $route 路由地址
     * @param  string            $method 请求类型
     */
    public function __construct(Route $router, RuleGroup $parent, string $name = null, string $rule = '', $route = null, string $method = '*')
    {
        $this->router = $router;
        $this->parent = $parent;
        $this->name   = $name;
        $this->route  = $route;
        $this->method = $method;

        $this->setRule($rule);

        $this->router->setRule($this->rule, $this);
    }

    /**
     * 设置当前路由规则为MISS路由
     * @access public
     * @return $this
     */
    public function setMiss()
    {
        $this->miss = true;
        return $this;
    }

    /**
     * 判断当前路由规则是否为MISS路由
     * @access public
     * @return bool
     */
    public function isMiss(): bool
    {
        return $this->miss;
    }

    /**
     * 设置当前路由为自动注册OPTIONS
     * @access public
     * @return $this
     */
    public function setAutoOptions()
    {
        $this->autoOption = true;
        return $this;
    }

    /**
     * 判断当前路由规则是否为自动注册的OPTIONS路由
     * @access public
     * @return bool
     */
    public function isAutoOptions(): bool
    {
        return $this->autoOption;
    }

    /**
     * 获取当前路由的URL后缀
     * @access public
     * @return string|null
     */
    public function getSuffix()
    {
        if (isset($this->option['ext'])) {
            $suffix = $this->option['ext'];
        } elseif ($this->parent->getOption('ext')) {
            $suffix = $this->parent->getOption('ext');
        } else {
            $suffix = null;
        }

        return $suffix;
    }

    /**
     * 路由规则预处理
     * @access public
     * @param  string      $rule     路由规则
     * @return void
     */
    public function setRule(string $rule): void
    {
        if ('$' == substr($rule, -1, 1)) {
            // 是否完整匹配
            $rule = substr($rule, 0, -1);

            $this->option['complete_match'] = true;
        }

        $rule = '/' != $rule ? ltrim($rule, '/') : '';

        if ($this->parent && $prefix = $this->parent->getFullName()) {
            $rule = $prefix . ($rule ? '/' . ltrim($rule, '/') : '');
        }

        if (false !== strpos($rule, ':')) {
            $this->rule = preg_replace(['/\[\:(\w+)\]/', '/\:(\w+)/'], ['<\1?>', '<\1>'], $rule);
        } else {
            $this->rule = $rule;
        }

        // 生成路由标识的快捷访问
        $this->setRuleName();
    }

    /**
     * 设置别名
     * @access public
     * @param  string     $name
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;
        $this->setRuleName(true);

        return $this;
    }

    /**
     * 设置路由标识 用于URL反解生成
     * @access protected
     * @param  bool $first 是否插入开头
     * @return void
     */
    protected function setRuleName(bool $first = false): void
    {
        if ($this->name) {
            $this->router->setName($this->name, $this, $first);
        }
    }

    /**
     * 检测路由
     * @access public
     * @param  Request      $request  请求对象
     * @param  string       $url      访问地址
     * @param  array        $match    匹配路由变量
     * @param  bool         $completeMatch   路由是否完全匹配
     * @return Dispatch|false
     */
    public function checkRule(Request $request, string $url, $match = null, bool $completeMatch = false)
    {
        // 检查参数有效性
        if (!$this->checkOption($this->option, $request)) {
            return false;
        }

        // 合并分组参数
        $option  = $this->getOption();
        $pattern = $this->getPattern();
        $url     = $this->urlSuffixCheck($request, $url, $option);

        if (is_null($match)) {
            $match = $this->checkMatch($url, $option, $pattern, $completeMatch);
        }

        if (false !== $match) {
            return $this->parseRule($request, $this->rule, $this->route, $url, $option, $match);
        }

        return false;
    }

    /**
     * 检测路由（含路由匹配）
     * @access public
     * @param  Request      $request  请求对象
     * @param  string       $url      访问地址
     * @param  bool         $completeMatch   路由是否完全匹配
     * @return Dispatch|false
     */
    public function check(Request $request, string $url, bool $completeMatch = false)
    {
        return $this->checkRule($request, $url, null, $completeMatch);
    }

    /**
     * URL后缀及Slash检查
     * @access protected
     * @param  Request      $request  请求对象
     * @param  string       $url      访问地址
     * @param  array        $option   路由参数
     * @return string
     */
    protected function urlSuffixCheck(Request $request, string $url, array $option = []): string
    {
        // 是否区分 / 地址访问
        if (!empty($option['remove_slash']) && '/' != $this->rule) {
            $this->rule = rtrim($this->rule, '/');
            $url        = rtrim($url, '|');
        }

        if (isset($option['ext'])) {
            // 路由ext参数 优先于系统配置的URL伪静态后缀参数
            $url = preg_replace('/\.(' . $request->ext() . ')$/i', '', $url);
        }

        return $url;
    }

    /**
     * 检测URL和规则路由是否匹配
     * @access private
     * @param  string    $url URL地址
     * @param  array     $option    路由参数
     * @param  array     $pattern   变量规则
     * @param  bool      $completeMatch   是否完全匹配
     * @return array|false
     */
    private function checkMatch(string $url, array $option, array $pattern, bool $completeMatch)
    {
        if (isset($option['complete_match'])) {
            $completeMatch = $option['complete_match'];
        }

        $depr = $this->router->config('pathinfo_depr');

        // 检查完整规则定义
        if (isset($pattern['__url__']) && !preg_match(0 === strpos($pattern['__url__'], '/') ? $pattern['__url__'] : '/^' . $pattern['__url__'] . ($completeMatch ? '$' : '') . '/', str_replace('|', $depr, $url))) {
            return false;
        }

        $var  = [];
        $url  = $depr . str_replace('|', $depr, $url);
        $rule = $depr . str_replace('/', $depr, $this->rule);

        if ($depr == $rule && $depr != $url) {
            return false;
        }

        if (false === strpos($rule, '<')) {
            if (0 === strcasecmp($rule, $url) || (!$completeMatch && 0 === strncasecmp($rule . $depr, $url . $depr, strlen($rule . $depr)))) {
                return $var;
            }
            return false;
        }

        $slash = preg_quote('/-' . $depr, '/');

        if ($matchRule = preg_split('/[' . $slash . ']?<\w+\??>/', $rule, 2)) {
            if ($matchRule[0] && 0 !== strncasecmp($rule, $url, strlen($matchRule[0]))) {
                return false;
            }
        }

        if (preg_match_all('/[' . $slash . ']?<?\w+\??>?/', $rule, $matches)) {
            $regex = $this->buildRuleRegex($rule, $matches[0], $pattern, $option, $completeMatch);

            try {
                if (!preg_match('~^' . $regex . '~u', $url, $match)) {
                    return false;
                }
            } catch (\Exception $e) {
                throw new Exception('route pattern error');
            }

            foreach ($match as $key => $val) {
                if (is_string($key)) {
                    $var[$key] = $val;
                }
            }
        }

        // 成功匹配后返回URL中的动态变量数组
        return $var;
    }

    /**
     * 设置路由所属分组（用于注解路由）
     * @access public
     * @param  string $name 分组名称或者标识
     * @return $this
     */
    public function group(string $name)
    {
        $group = $this->router->getRuleName()->getGroup($name);

        if ($group) {
            $this->parent = $group;
            $this->setRule($this->rule);
        }

        return $this;
    }
}
