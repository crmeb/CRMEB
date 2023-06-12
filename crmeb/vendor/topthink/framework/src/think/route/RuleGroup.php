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

use Closure;
use think\Container;
use think\Exception;
use think\Request;
use think\Route;

/**
 * 路由分组类
 */
class RuleGroup extends Rule
{
    /**
     * 分组路由（包括子分组）
     * @var array
     */
    protected $rules = [];

    /**
     * 分组路由规则
     * @var mixed
     */
    protected $rule;

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
     * 架构函数
     * @access public
     * @param  Route     $router 路由对象
     * @param  RuleGroup $parent 上级对象
     * @param  string    $name   分组名称
     * @param  mixed     $rule   分组路由
     */
    public function __construct(Route $router, RuleGroup $parent = null, string $name = '', $rule = null)
    {
        $this->router = $router;
        $this->parent = $parent;
        $this->rule   = $rule;
        $this->name   = trim($name, '/');

        $this->setFullName();

        if ($this->parent) {
            $this->domain = $this->parent->getDomain();
            $this->parent->addRuleItem($this);
        }

        if ($router->isTest()) {
            $this->lazy(false);
        }
    }

    /**
     * 设置分组的路由规则
     * @access public
     * @return void
     */
    protected function setFullName(): void
    {
        if (false !== strpos($this->name, ':')) {
            $this->name = preg_replace(['/\[\:(\w+)\]/', '/\:(\w+)/'], ['<\1?>', '<\1>'], $this->name);
        }

        if ($this->parent && $this->parent->getFullName()) {
            $this->fullName = $this->parent->getFullName() . ($this->name ? '/' . $this->name : '');
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
        if ($this instanceof Resource) {
            $this->buildResourceRule();
        } else {
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
            // 合并路由正则规则进行路由匹配检查
            $result = $this->checkMergeRuleRegex($request, $rules, $url, $completeMatch);

            if (false !== $result) {
                return $result;
            }
        }

        // 检查分组路由
        foreach ($rules as $key => $item) {
            $result = $item[1]->check($request, $url, $completeMatch);

            if (false !== $result) {
                return $result;
            }
        }

        if (!empty($option['dispatcher'])) {
            $result = $this->parseRule($request, '', $option['dispatcher'], $url, $option);
        } elseif ($this->miss && in_array($this->miss->getMethod(), ['*', $method])) {
            // 未匹配所有路由的路由规则处理
            $result = $this->parseRule($request, '', $this->miss->getRoute(), $url, $this->miss->getOption());
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * 分组URL匹配检查
     * @access protected
     * @param  string $url URL
     * @return bool
     */
    protected function checkUrl(string $url): bool
    {
        if ($this->fullName) {
            $pos = strpos($this->fullName, '<');

            if (false !== $pos) {
                $str = substr($this->fullName, 0, $pos);
            } else {
                $str = $this->fullName;
            }

            if ($str && 0 !== stripos(str_replace('|', '/', $url), $str)) {
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
     * 延迟解析分组的路由规则
     * @access public
     * @param  bool $lazy 路由是否延迟解析
     * @return $this
     */
    public function lazy(bool $lazy = true)
    {
        if (!$lazy) {
            $this->parseGroupRule($this->rule);
            $this->rule = null;
        }

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
        if (is_string($rule) && is_subclass_of($rule, Dispatch::class)) {
            $this->dispatcher($rule);
            return;
        }

        $origin = $this->router->getGroup();
        $this->router->setGroup($this);

        if ($rule instanceof \Closure) {
            Container::getInstance()->invokeFunction($rule);
        } elseif (is_string($rule) && $rule) {
            $this->router->bind($rule, $this->domain);
        }

        $this->router->setGroup($origin);
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
        $depr  = $this->router->config('pathinfo_depr');
        $url   = $depr . str_replace('|', $depr, $url);
        $regex = [];
        $items = [];

        foreach ($rules as $key => $val) {
            $item = $val[1];
            if ($item instanceof RuleItem) {
                $rule = $depr . str_replace('/', $depr, $item->getRule());
                if ($depr == $rule && $depr != $url) {
                    unset($rules[$key]);
                    continue;
                }

                $complete = $item->getOption('complete_match', $completeMatch);

                if (false === strpos($rule, '<')) {
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
                    if (0 === strpos(str_replace(['\/', '\-', '\\' . $depr], ['/', '-', $depr], $item), $match[0])) {
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
     * 获取分组的MISS路由
     * @access public
     * @return RuleItem|null
     */
    public function getMissRule():  ? RuleItem
    {
        return $this->miss;
    }

    /**
     * 注册MISS路由
     * @access public
     * @param  string|Closure $route  路由地址
     * @param  string         $method 请求类型
     * @return RuleItem
     */
    public function miss($route, string $method = '*') : RuleItem
    {
        // 创建路由规则实例
        $ruleItem = new RuleItem($this->router, $this, null, '', $route, strtolower($method));

        $ruleItem->setMiss();
        $this->miss = $ruleItem;

        return $ruleItem;
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
        if (is_string($route)) {
            $name = $route;
        } else {
            $name = null;
        }

        $method = strtolower($method);

        if ('' === $rule || '/' === $rule) {
            $rule .= '$';
        }

        // 创建路由规则实例
        $ruleItem = new RuleItem($this->router, $this, $name, $rule, $route, $method);

        $this->addRuleItem($ruleItem, $method);

        return $ruleItem;
    }

    /**
     * 注册分组下的路由规则
     * @access public
     * @param  Rule   $rule   路由规则
     * @param  string $method 请求类型
     * @return $this
     */
    public function addRuleItem(Rule $rule, string $method = '*')
    {
        if (strpos($method, '|')) {
            $rule->method($method);
            $method = '*';
        }

        $this->rules[] = [$method, $rule];

        if ($rule instanceof RuleItem && 'options' != $method) {
            $this->rules[] = ['options', $rule->setAutoOptions()];
        }

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
            return $method == $item[0] || '*' == $item[0];
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
