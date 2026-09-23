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

namespace think;

use Closure;
use think\exception\RouteNotFoundException;
use think\route\Dispatch;
use think\route\dispatch\Callback;
use think\route\Domain;
use think\route\Resource;
use think\route\ResourceRegister;
use think\route\Rule;
use think\route\RuleGroup;
use think\route\RuleItem;
use think\route\RuleName;
use think\route\Url as UrlBuild;

/**
 * 路由管理类
 * @package think
 */
class Route
{
    /**
     * REST定义
     * @var array
     */
    protected $rest = [
        'index'  => ['get', '', 'index'],
        'create' => ['get', '/create', 'create'],
        'edit'   => ['get', '/<id>/edit', 'edit'],
        'read'   => ['get', '/<id>', 'read'],
        'save'   => ['post', '', 'save'],
        'update' => ['put', '/<id>', 'update'],
        'delete' => ['delete', '/<id>', 'delete'],
    ];

    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        // pathinfo分隔符
        'pathinfo_depr'         => '/',
        // 是否开启路由延迟解析
        'url_lazy_route'        => false,
        // 是否强制使用路由
        'url_route_must'        => false,
        // 是否区分大小写
        'url_case_sensitive'    => false,
        // 合并路由规则
        'route_rule_merge'      => false,
        // 路由是否完全匹配
        'route_complete_match'  => false,
        // 子目录是否自动路由分组
        'route_auto_group'      => false,
        // 去除斜杠
        'remove_slash'          => false,
        // 默认的路由变量规则
        'default_route_pattern' => '[\w\.]+',
        // URL伪静态后缀
        'url_html_suffix'       => 'html',
        // 访问控制器层名称
        'controller_layer'      => 'controller',
        // 空控制器名
        'empty_controller'      => 'Error',
        // 是否使用控制器后缀
        'controller_suffix'     => false,
        // 默认模块名
        'default_module'        => 'index',
        // 默认控制器名
        'default_controller'    => 'Index',
        // 默认操作名
        'default_action'        => 'index',
        // 操作方法后缀
        'action_suffix'         => '',
        // 非路由变量是否使用普通参数方式（用于URL生成）
        'url_common_param'      => true,
        // 操作方法的参数绑定方式 route get param
        'action_bind_param'     => 'get',
        // API版本header变量
        'api_version'           => 'Api-Version',
    ];

    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * @var RuleName
     */
    protected $ruleName;

    /**
     * 当前HOST
     * @var string
     */
    protected $host;

    /**
     * 当前分组对象
     * @var RuleGroup
     */
    protected $group;

    /**
     * 域名对象
     * @var Domain[]
     */
    protected $domains = [];

    /**
     * 跨域路由规则
     * @var RuleGroup
     */
    protected $cross;

    /**
     * 路由是否延迟解析
     * @var bool
     */
    protected $lazy = false;

    /**
     * （分组）路由规则是否合并解析
     * @var bool
     */
    protected $mergeRuleRegex = false;

    /**
     * 是否去除URL最后的斜线
     * @var bool
     */
    protected $removeSlash = false;

    public function __construct(protected App $app)
    {
        $this->ruleName = new RuleName();
        $this->setDefaultDomain();

        if (is_file($this->app->getRuntimePath() . 'route.php')) {
            // 读取路由映射文件
            $this->import(include $this->app->getRuntimePath() . 'route.php');
        }

        $this->config = array_merge($this->config, $this->app->config->get('route'));

        $this->init();
    }

    protected function init()
    {
        if (!empty($this->config['middleware'])) {
            $this->app->middleware->import($this->config['middleware'], 'route');
        }

        $this->lazy($this->config['url_lazy_route']);
        $this->mergeRuleRegex = $this->config['route_rule_merge'];
        $this->removeSlash    = $this->config['remove_slash'];

        $this->group->removeSlash($this->removeSlash);

        // 注册全局MISS路由
        $this->miss(function () {
            return Response::create('', 'html', 204)->header(['Allow' => 'GET, POST, PUT, DELETE']);
        }, 'options');
    }

    public function config(?string $name = null)
    {
        if (is_null($name)) {
            return $this->config;
        }

        return $this->config[$name] ?? null;
    }

    /**
     * 设置路由域名及分组（包括资源路由）是否延迟解析
     * @access public
     * @param bool $lazy 路由是否延迟解析
     * @return $this
     */
    public function lazy(bool $lazy = true)
    {
        $this->lazy = $lazy;
        return $this;
    }

    /**
     * 设置路由域名及分组（包括资源路由）是否合并解析
     * @access public
     * @param bool $merge 路由是否合并解析
     * @return $this
     */
    public function mergeRuleRegex(bool $merge = true)
    {
        $this->mergeRuleRegex = $merge;
        $this->group->mergeRuleRegex($merge);

        return $this;
    }

    /**
     * 初始化默认域名
     * @access protected
     * @return void
     */
    protected function setDefaultDomain(): void
    {
        // 注册默认域名
        $domain = new Domain($this);

        $this->domains['-'] = $domain;

        // 默认分组
        $this->group = $domain;
    }

    /**
     * 设置当前分组
     * @access public
     * @param RuleGroup $group 域名
     * @return void
     */
    public function setGroup(RuleGroup $group): void
    {
        $this->group = $group;
    }

    /**
     * 获取指定标识的路由分组 不指定则获取当前分组
     * @access public
     * @param string $name 分组标识
     * @return RuleGroup
     */
    public function getGroup(?string $name = null)
    {
        return $name ? $this->ruleName->getGroup($name) : $this->group;
    }

    /**
     * 注册变量规则
     * @access public
     * @param array $pattern 变量规则
     * @return $this
     */
    public function pattern(array $pattern)
    {
        $this->group->pattern($pattern);

        return $this;
    }

    /**
     * 注册路由参数
     * @access public
     * @param array $option 参数
     * @return $this
     */
    public function option(array $option)
    {
        $this->group->option($option);

        return $this;
    }

    /**
     * 注册域名路由
     * @access public
     * @param string|array $name 子域名
     * @param mixed        $rule 路由规则
     * @return Domain
     */
    public function domain(string | array $name, $rule = null): Domain
    {
        // 支持多个域名使用相同路由规则
        $domainName = is_array($name) ? array_shift($name) : $name;

        if (!isset($this->domains[$domainName])) {
            $domain = (new Domain($this, $domainName, $rule, $this->lazy))
                ->removeSlash($this->removeSlash)
                ->mergeRuleRegex($this->mergeRuleRegex);

            $this->domains[$domainName] = $domain;
        } else {
            $domain = $this->domains[$domainName];
            $domain->parseGroupRule($rule);
        }

        if (is_array($name) && !empty($name)) {
            foreach ($name as $item) {
                $this->domains[$item] = $domainName;
            }
        }

        // 返回域名对象
        return $domain;
    }

    /**
     * 获取域名
     * @access public
     * @return array
     */
    public function getDomains(): array
    {
        return $this->domains;
    }

    /**
     * 获取域名路由的绑定信息
     * @access public
     * @param string $domain 子域名
     * @return string|null
     */
    public function getDomainBind(?string $domain = null)
    {
        if ($domain && isset($this->domains[$domain])) {
            $item = $this->domains[$domain];
            if (is_string($item)) {
                $item = $this->domains[$item];
            }
            return $item->getBind();
        }
    }

    /**
     * 获取RuleName对象
     * @access public
     * @return RuleName
     */
    public function getRuleName(): RuleName
    {
        return $this->ruleName;
    }

    /**
     * 读取路由标识
     * @access public
     * @param string $name   路由标识
     * @param string $domain 域名
     * @param string $method 请求类型
     * @return array
     */
    public function getName(?string $name = null, ?string $domain = null, string $method = '*'): array
    {
        return $this->ruleName->getName($name, $domain, $method);
    }

    /**
     * 批量导入路由标识
     * @access public
     * @param array $name 路由标识
     * @return void
     */
    public function import(array $name): void
    {
        $this->ruleName->import($name);
    }

    /**
     * 注册路由标识
     * @access public
     * @param string   $name     路由标识
     * @param RuleItem $ruleItem 路由规则
     * @param bool     $first    是否优先
     * @return void
     */
    public function setName(string $name, RuleItem $ruleItem, bool $first = false): void
    {
        $this->ruleName->setName($name, $ruleItem, $first);
    }

    /**
     * 保存路由规则
     * @access public
     * @param string   $rule     路由规则
     * @param RuleItem $ruleItem RuleItem对象
     * @return void
     */
    public function setRule(string $rule, ?RuleItem $ruleItem = null): void
    {
        $this->ruleName->setRule($rule, $ruleItem);
    }

    /**
     * 读取路由
     * @access public
     * @param string $rule 路由规则
     * @return RuleItem[]
     */
    public function getRule(string $rule): array
    {
        return $this->ruleName->getRule($rule);
    }

    /**
     * 读取路由列表
     * @access public
     * @return array
     */
    public function getRuleList(): array
    {
        return $this->ruleName->getRuleList();
    }

    /**
     * 清空路由规则
     * @access public
     * @return void
     */
    public function clear(): void
    {
        $this->ruleName->clear();

        if ($this->group) {
            $this->group->clear();
        }
    }

    /**
     * 注册路由规则
     * @access public
     * @param string $rule   路由规则
     * @param mixed  $route  路由地址
     * @param string $method 请求类型
     * @return RuleItem
     */
    public function rule(string $rule, $route = null, string $method = '*'): RuleItem
    {
        return $this->group->addRule($rule, $route, $method);
    }

    /**
     * 设置路由规则全局有效
     * @access public
     * @param Rule   $rule   路由规则
     * @return $this
     */
    public function setCrossDomainRule(Rule $rule)
    {
        if (!isset($this->cross)) {
            $this->cross = (new RuleGroup($this))->mergeRuleRegex($this->mergeRuleRegex);
        }

        $this->cross->addRuleItem($rule);

        return $this;
    }

    /**
     * 注册路由分组
     * @access public
     * @param string|Closure $name  分组名称或者参数
     * @param mixed           $route 分组路由
     * @return RuleGroup
     */
    public function group(string | Closure $name, $route = null): RuleGroup
    {
        if ($name instanceof Closure) {
            $route = $name;
            $name  = '';
        }

        return (new RuleGroup($this, $this->group, $name, $route, $this->lazy))
            ->removeSlash($this->removeSlash)
            ->mergeRuleRegex($this->mergeRuleRegex);
    }

    /**
     * 注册路由
     * @access public
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return RuleItem
     */
    public function any(string $rule, $route): RuleItem
    {
        return $this->rule($rule, $route, '*');
    }

    /**
     * 注册GET路由
     * @access public
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return RuleItem
     */
    public function get(string $rule, $route): RuleItem
    {
        return $this->rule($rule, $route, 'GET');
    }

    /**
     * 注册POST路由
     * @access public
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return RuleItem
     */
    public function post(string $rule, $route): RuleItem
    {
        return $this->rule($rule, $route, 'POST');
    }

    /**
     * 注册PUT路由
     * @access public
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return RuleItem
     */
    public function put(string $rule, $route): RuleItem
    {
        return $this->rule($rule, $route, 'PUT');
    }

    /**
     * 注册DELETE路由
     * @access public
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return RuleItem
     */
    public function delete(string $rule, $route): RuleItem
    {
        return $this->rule($rule, $route, 'DELETE');
    }

    /**
     * 注册PATCH路由
     * @access public
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return RuleItem
     */
    public function patch(string $rule, $route): RuleItem
    {
        return $this->rule($rule, $route, 'PATCH');
    }

    /**
     * 注册HEAD路由
     * @access public
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return RuleItem
     */
    public function head(string $rule, $route): RuleItem
    {
        return $this->rule($rule, $route, 'HEAD');
    }

    /**
     * 注册OPTIONS路由
     * @access public
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return RuleItem
     */
    public function options(string $rule, $route): RuleItem
    {
        return $this->rule($rule, $route, 'OPTIONS');
    }

    /**
     * 注册资源路由
     * @access public
     * @param string $rule  路由规则
     * @param string $route 路由地址
     * @param Closure $extend 扩展规则
     * @return Resource|ResourceRegister
     */
    public function resource(string $rule, string $route, ?Closure $extend = null)
    {
        $resource = (new Resource($this, $this->group, $rule, $route, $this->rest))->extend($extend);

        if (!$this->lazy) {
            return new ResourceRegister($resource);
        }

        return $resource;
    }

    /**
     * 注册视图路由
     * @access public
     * @param string $rule     路由规则
     * @param string $template 路由模板地址
     * @param array  $vars     模板变量
     * @return RuleItem
     */
    public function view(string $rule, string $template = '', array $vars = []): RuleItem
    {
        return $this->rule($rule, function () use ($vars, $template) {
            return Response::create($template, 'view')->assign($vars);
        }, 'GET');
    }

    /**
     * 注册重定向路由
     * @access public
     * @param string $rule   路由规则
     * @param string $route  路由地址
     * @param int    $status 状态码
     * @return RuleItem
     */
    public function redirect(string $rule, string $route = '', int $status = 301): RuleItem
    {
        return $this->rule($rule, function (Request $request) use ($status, $route) {
            $search  = $replace  = [];
            $matches = $request->rule()->getVars();

            foreach ($matches as $key => $value) {
                $search[]  = '<' . $key . '>';
                $replace[] = $value;
                $search[]  = '{' . $key . '}';
                $replace[] = $value;
                $search[]  = ':' . $key;
                $replace[] = $value;
            }

            $route = str_replace($search, $replace, $route);
            return Response::create($route, 'redirect')->code($status);
        }, '*');
    }

    /**
     * rest方法定义和修改
     * @access public
     * @param string|array $name     方法名称
     * @param array|bool   $resource 资源
     * @return $this
     */
    public function rest(string | array $name, array | bool $resource = [])
    {
        if (is_array($name)) {
            $this->rest = $resource ? $name : array_merge($this->rest, $name);
        } else {
            $this->rest[$name] = $resource;
        }

        return $this;
    }

    /**
     * 获取rest方法定义的参数
     * @access public
     * @param string $name 方法名称
     * @return array|null
     */
    public function getRest(?string $name = null)
    {
        if (is_null($name)) {
            return $this->rest;
        }

        return $this->rest[$name] ?? null;
    }

    /**
     * 注册未匹配路由规则后的处理
     * @access public
     * @param string|Closure $route  路由地址
     * @param string         $method 请求类型
     * @return RuleItem
     */
    public function miss(string | Closure $route, string $method = '*'): RuleItem
    {
        return $this->group->miss($route, $method);
    }

    /**
     * 路由调度
     * @param Request $request
     * @param Closure|bool $withRoute
     * @return Response
     */
    public function dispatch(Request $request, Closure | bool $withRoute = true)
    {
        $this->request = $request;
        $this->host    = $this->request->host(true);
        $completeMatch = (bool) $this->config['route_complete_match'];
        $url           = str_replace($this->config['pathinfo_depr'], '|', $this->path());

        if ($withRoute) {
            if ($withRoute instanceof Closure) {
                $withRoute();
            }
            // 路由检测
            $dispatch = $this->check($url, $completeMatch);
        }

        if (empty($dispatch)) {
            // 默认URL调度
            $dispatch = $this->checkUrlDispatch($url);
        }

        $dispatch->init($this->app);

        return $this->app->middleware->pipeline('route')
            ->send($request)
            ->then(function () use ($dispatch) {
                return $dispatch->run();
            });
    }

    /**
     * 检测URL路由
     * @access public
     * @param  bool $completeMatch
     * @return Dispatch|false
     * @throws RouteNotFoundException
     */
    public function check(string $url, bool $completeMatch = false)
    {
        // 检测域名路由
        $result = $this->checkDomain()->check($this->request, $url, $completeMatch);

        if (false === $result && !empty($this->cross)) {
            // 检测跨域路由
            $result = $this->cross->check($this->request, $url, $completeMatch);
        }

        if (false === $result && $this->config['url_route_must']) {
            // 开启强制路由
            throw new RouteNotFoundException();
        }

        return $result;
    }

    /**
     * 获取当前请求URL的pathinfo信息(不含URL后缀)
     * @access protected
     * @return string
     */
    protected function path(): string
    {
        $suffix   = $this->config['url_html_suffix'];
        $pathinfo = $this->request->pathinfo();

        if (false === $suffix) {
            // 禁止伪静态访问
            $path = $pathinfo;
        } elseif ($suffix) {
            // 去除正常的URL后缀
            $path = preg_replace('/\.(' . ltrim($suffix, '.') . ')$/i', '', $pathinfo);
        } else {
            // 允许任何后缀访问
            $path = preg_replace('/\.' . $this->request->ext() . '$/i', '', $pathinfo);
        }

        return $path;
    }

    /**
     * 自动多模块URL路由 如使用多模块在路由定义文件最后定义
     * @access public
     * @param  string $rule    路由规则
     * @param  mixed  $route   路由地址
     * @param  bool   $middleware  自动注册中间件
     * @return RuleItem
     */
    public function auto(string $rule = '[:__module__]/[:__controller__]/[:__action__]', $route = ':__module__/:__controller__/:__action__', bool $middleware = false): RuleItem
    {
        return $this->rule($rule, $route)
            ->name('__think_auto_route__')
            ->pattern([
                '__module__'     => '[A-Za-z0-9\.\_]+',
                '__controller__' => '[A-Za-z0-9\.\_]+',
                '__action__'     => '[A-Za-z0-9\_]+',
            ])->default([
                '__module__'     => $this->config['default_module'],
                '__controller__' => $this->config['default_controller'],
                '__action__'     => $this->config['default_action'],
            ])->autoMiddleware($middleware);
    }

    /**
     * 检测默认URL解析路由
     * @access public
     * @param  string   $url URL
     * @return Dispatch
     */
    protected function checkUrlDispatch(string $url): Dispatch
    {
        if ($this->request->method() == 'OPTIONS') {
            // 自动响应options请求
            return new Callback($this->request, $this->group, function () {
                return Response::create('', 'html', 204)->header(['Allow' => 'GET, POST, PUT, DELETE']);
            });
        }

        return $this->group->auto()->checkBind($this->request, $url);
    }

    /**
     * 检测域名的路由规则
     * @access protected
     * @return Domain
     */
    protected function checkDomain(): Domain
    {
        $item = false;

        if (count($this->domains) > 1) {
            // 获取当前子域名
            $subDomain = $this->request->subDomain();
            $domain    = $subDomain ? explode('.', $subDomain) : [];
            $domain2   = $domain ? array_pop($domain) : '';

            if ($domain) {
                // 存在三级域名
                $domain3 = array_pop($domain);
            }

            if (isset($this->domains[$this->host])) {
                // 子域名配置
                $item = $this->domains[$this->host];
            } elseif (isset($this->domains[$subDomain])) {
                $item = $this->domains[$subDomain];
            } elseif (isset($this->domains['*.' . $domain2]) && !empty($domain3)) {
                // 泛三级域名
                $item      = $this->domains['*.' . $domain2];
                $panDomain = $domain3;
            } elseif (isset($this->domains['*']) && !empty($domain2)) {
                // 泛二级域名
                if ('www' != $domain2) {
                    $item      = $this->domains['*'];
                    $panDomain = $domain2;
                }
            }

            if (isset($panDomain)) {
                // 保存当前泛域名
                $this->request->setPanDomain($panDomain);
            }
        }

        if (false === $item) {
            // 检测全局域名规则
            $item = $this->domains['-'];
        }

        if (is_string($item)) {
            $item = $this->domains[$item];
        }

        return $item;
    }

    /**
     * URL生成 支持路由反射
     * @access public
     * @param string $url  路由地址
     * @param array  $vars 参数 ['a'=>'val1', 'b'=>'val2']
     * @return UrlBuild
     */
    public function buildUrl(string $url = '', array $vars = []): UrlBuild
    {
        return $this->app->make(UrlBuild::class, [$this, $this->app, $url, $vars], true);
    }

    /**
     * 设置全局的路由分组参数
     * @access public
     * @param string $method 方法名
     * @param array  $args   调用参数
     * @return RuleGroup
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->group, $method], $args);
    }
}
