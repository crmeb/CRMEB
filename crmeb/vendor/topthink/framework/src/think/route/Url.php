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

namespace think\route;

use think\App;
use think\Route;

/**
 * 路由地址生成
 */
class Url
{
    /**
     * 应用对象
     * @var App
     */
    protected $app;

    /**
     * 路由对象
     * @var Route
     */
    protected $route;

    /**
     * URL变量
     * @var array
     */
    protected $vars = [];

    /**
     * 路由URL
     * @var string
     */
    protected $url;

    /**
     * URL 根地址
     * @var string
     */
    protected $root = '';

    /**
     * URL后缀
     * @var string|bool
     */
    protected $suffix = true;

    /**
     * URL域名
     * @var string|bool
     */
    protected $domain = false;

    /**
     * 架构函数
     * @access public
     * @param  string $url URL地址
     * @param  array  $vars 参数
     */
    public function __construct(Route $route, App $app, string $url = '', array $vars = [])
    {
        $this->route = $route;
        $this->app   = $app;
        $this->url   = $url;
        $this->vars  = $vars;
    }

    /**
     * 设置URL参数
     * @access public
     * @param  array $vars URL参数
     * @return $this
     */
    public function vars(array $vars = [])
    {
        $this->vars = $vars;
        return $this;
    }

    /**
     * 设置URL后缀
     * @access public
     * @param  string|bool $suffix URL后缀
     * @return $this
     */
    public function suffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * 设置URL域名（或者子域名）
     * @access public
     * @param  string|bool $domain URL域名
     * @return $this
     */
    public function domain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * 设置URL 根地址
     * @access public
     * @param  string $root URL root
     * @return $this
     */
    public function root(string $root)
    {
        $this->root = $root;
        return $this;
    }

    /**
     * 检测域名
     * @access protected
     * @param  string      $url URL
     * @param  string|true $domain 域名
     * @return string
     */
    protected function parseDomain(string &$url, $domain): string
    {
        if (!$domain) {
            return '';
        }

        $request    = $this->app->request;
        $rootDomain = $request->rootDomain();

        if (true === $domain) {
            // 自动判断域名
            $domain  = $request->host();
            $domains = $this->route->getDomains();

            if (!empty($domains)) {
                $route_domain = array_keys($domains);
                foreach ($route_domain as $domain_prefix) {
                    if (0 === strpos($domain_prefix, '*.') && strpos($domain, ltrim($domain_prefix, '*.')) !== false) {
                        foreach ($domains as $key => $rule) {
                            $rule = is_array($rule) ? $rule[0] : $rule;
                            if (is_string($rule) && false === strpos($key, '*') && 0 === strpos($url, $rule)) {
                                $url    = ltrim($url, $rule);
                                $domain = $key;

                                // 生成对应子域名
                                if (!empty($rootDomain)) {
                                    $domain .= $rootDomain;
                                }
                                break;
                            } elseif (false !== strpos($key, '*')) {
                                if (!empty($rootDomain)) {
                                    $domain .= $rootDomain;
                                }

                                break;
                            }
                        }
                    }
                }
            }
        } elseif (false === strpos($domain, '.') && 0 !== strpos($domain, $rootDomain)) {
            $domain .= '.' . $rootDomain;
        }

        if (false !== strpos($domain, '://')) {
            $scheme = '';
        } else {
            $scheme = $request->isSsl() ? 'https://' : 'http://';
        }

        return $scheme . $domain;
    }

    /**
     * 解析URL后缀
     * @access protected
     * @param  string|bool $suffix 后缀
     * @return string
     */
    protected function parseSuffix($suffix): string
    {
        if ($suffix) {
            $suffix = true === $suffix ? $this->route->config('url_html_suffix') : $suffix;

            if ($pos = strpos($suffix, '|')) {
                $suffix = substr($suffix, 0, $pos);
            }
        }

        return (empty($suffix) || 0 === strpos($suffix, '.')) ? (string) $suffix : '.' . $suffix;
    }

    /**
     * 直接解析URL地址
     * @access protected
     * @param  string      $url URL
     * @param  string|bool $domain Domain
     * @return string
     */
    protected function parseUrl(string $url, &$domain): string
    {
        $request = $this->app->request;

        if (0 === strpos($url, '/')) {
            // 直接作为路由地址解析
            $url = substr($url, 1);
        } elseif (false !== strpos($url, '\\')) {
            // 解析到类
            $url = ltrim(str_replace('\\', '/', $url), '/');
        } elseif (0 === strpos($url, '@')) {
            // 解析到控制器
            $url = substr($url, 1);
        } else {
            // 解析到 应用/控制器/操作
            $app        = $request->app();
            $controller = $request->controller();

            if ('' == $url) {
                $action = $request->action();
            } else {
                $path       = explode('/', $url);
                $action     = array_pop($path);
                $controller = empty($path) ? $controller : array_pop($path);
                $app        = empty($path) ? $app : array_pop($path);
            }

            if ($this->route->config('url_convert')) {
                $action     = strtolower($action);
                $controller = App::parseName($controller);
            }

            $url = $controller . '/' . $action;

            if ($app && $this->app->config->get('app.auto_multi_app')) {
                $bind = $this->app->config->get('app.domain_bind', []);
                if ($key = array_search($app, $bind)) {
                    $domain = true === $domain ? $key : $domain;
                } else {
                    $map = $this->app->config->get('app.app_map', []);

                    if ($key = array_search($app, $map)) {
                        $url = $key . '/' . $url;
                    } else {
                        $url = $app . '/' . $url;
                    }
                }
            }
        }

        return $url;
    }

    /**
     * 分析路由规则中的变量
     * @access protected
     * @param  string $rule 路由规则
     * @return array
     */
    protected function parseVar(string $rule): array
    {
        // 提取路由规则中的变量
        $var = [];

        if (preg_match_all('/<\w+\??>/', $rule, $matches)) {
            foreach ($matches[0] as $name) {
                $optional = false;

                if (strpos($name, '?')) {
                    $name     = substr($name, 1, -2);
                    $optional = true;
                } else {
                    $name = substr($name, 1, -1);
                }

                $var[$name] = $optional ? 2 : 1;
            }
        }

        return $var;
    }

    /**
     * 匹配路由地址
     * @access protected
     * @param  array $rule 路由规则
     * @param  array $vars 路由变量
     * @param  mixed $allowDomain 允许域名
     * @return array
     */
    protected function getRuleUrl(array $rule, array &$vars = [], $allowDomain = ''): array
    {
        $request = $this->app->request;

        foreach ($rule as $item) {
            $url     = $item->getRule();
            $pattern = $this->parseVar($url);
            $domain  = $item->getDomain();
            $suffix  = $item->getSuffix();

            if ('-' == $domain) {
                $domain = $request->host(true);
            }

            if (is_string($allowDomain) && $domain != $allowDomain) {
                continue;
            }

            if (!in_array($request->port(), [80, 443])) {
                $domain .= ':' . $request->port();
            }

            if (empty($pattern)) {
                return [rtrim($url, '?/-'), $domain, $suffix];
            }

            $type = $this->route->config('url_common_param');

            foreach ($pattern as $key => $val) {
                if (isset($vars[$key])) {
                    $url = str_replace(['[:' . $key . ']', '<' . $key . '?>', ':' . $key, '<' . $key . '>'], $type ? $vars[$key] : urlencode((string) $vars[$key]), $url);
                    unset($vars[$key]);
                    $url    = str_replace(['/?', '-?'], ['/', '-'], $url);
                    $result = [rtrim($url, '?/-'), $domain, $suffix];
                } elseif (2 == $val) {
                    $url    = str_replace(['/[:' . $key . ']', '[:' . $key . ']', '<' . $key . '?>'], '', $url);
                    $url    = str_replace(['/?', '-?'], ['/', '-'], $url);
                    $result = [rtrim($url, '?/-'), $domain, $suffix];
                } else {
                    break;
                }
            }

            if (isset($result)) {
                return $result;
            }
        }

        return [];
    }

    public function build()
    {
        // 解析URL
        $url     = $this->url;
        $suffix  = $this->suffix;
        $domain  = $this->domain;
        $request = $this->app->request;
        $vars    = $this->vars;

        if (0 === strpos($url, '[') && $pos = strpos($url, ']')) {
            // [name] 表示使用路由命名标识生成URL
            $name = substr($url, 1, $pos - 1);
            $url  = 'name' . substr($url, $pos + 1);
        }

        if (false === strpos($url, '://') && 0 !== strpos($url, '/')) {
            $info = parse_url($url);
            $url  = !empty($info['path']) ? $info['path'] : '';

            if (isset($info['fragment'])) {
                // 解析锚点
                $anchor = $info['fragment'];

                if (false !== strpos($anchor, '?')) {
                    // 解析参数
                    list($anchor, $info['query']) = explode('?', $anchor, 2);
                }

                if (false !== strpos($anchor, '@')) {
                    // 解析域名
                    list($anchor, $domain) = explode('@', $anchor, 2);
                }
            } elseif (strpos($url, '@') && false === strpos($url, '\\')) {
                // 解析域名
                list($url, $domain) = explode('@', $url, 2);
            }
        }

        if ($url) {
            $checkName   = isset($name) ? $name : $url . (isset($info['query']) ? '?' . $info['query'] : '');
            $checkDomain = $domain && is_string($domain) ? $domain : null;

            $rule = $this->route->getName($checkName, $checkDomain);

            if (empty($rule) && isset($info['query'])) {
                $rule = $this->route->getName($url, $checkDomain);
                // 解析地址里面参数 合并到vars
                parse_str($info['query'], $params);
                $vars = array_merge($params, $vars);
                unset($info['query']);
            }
        }

        if (!empty($rule) && $match = $this->getRuleUrl($rule, $vars, $domain)) {
            // 匹配路由命名标识
            $url = $match[0];

            if ($domain && !empty($match[1])) {
                $domain = $match[1];
            }

            if (!is_null($match[2])) {
                $suffix = $match[2];
            }

            if ($request->app() && $this->app->config->get('app.auto_multi_app') && !$this->app->http->isBindDomain()) {
                $url = $request->app() . '/' . $url;
            }
        } elseif (!empty($rule) && isset($name)) {
            throw new \InvalidArgumentException('route name not exists:' . $name);
        } else {
            // 检测URL绑定
            $bind = $this->route->getDomainBind($domain && is_string($domain) ? $domain : null);

            if ($bind && 0 === strpos($url, $bind)) {
                $url = substr($url, strlen($bind) + 1);
            } else {
                $binds = $this->route->getBind();

                foreach ($binds as $key => $val) {
                    if (is_string($val) && 0 === strpos($url, $val) && substr_count($val, '/') > 1) {
                        $url    = substr($url, strlen($val) + 1);
                        $domain = $key;
                        break;
                    }
                }
            }

            // 路由标识不存在 直接解析
            $url = $this->parseUrl($url, $domain);

            if (isset($info['query'])) {
                // 解析地址里面参数 合并到vars
                parse_str($info['query'], $params);
                $vars = array_merge($params, $vars);
            }
        }

        // 还原URL分隔符
        $depr = $this->route->config('pathinfo_depr');
        $url  = str_replace('/', $depr, $url);

        $file = $request->baseFile();
        if ($file && 0 !== strpos($request->url(), $file)) {
            $file = str_replace('\\', '/', dirname($file));
        }

        $url = rtrim($file, '/') . '/' . $url;

        // URL后缀
        if ('/' == substr($url, -1) || '' == $url) {
            $suffix = '';
        } else {
            $suffix = $this->parseSuffix($suffix);
        }

        // 锚点
        $anchor = !empty($anchor) ? '#' . $anchor : '';

        // 参数组装
        if (!empty($vars)) {
            // 添加参数
            if ($this->route->config('url_common_param')) {
                $vars = http_build_query($vars);
                $url .= $suffix . '?' . $vars . $anchor;
            } else {
                foreach ($vars as $var => $val) {
                    if ('' !== $val) {
                        $url .= $depr . $var . $depr . urlencode((string) $val);
                    }
                }

                $url .= $suffix . $anchor;
            }
        } else {
            $url .= $suffix . $anchor;
        }

        // 检测域名
        $domain = $this->parseDomain($url, $domain);

        // URL组装
        return $domain . rtrim($this->root, '/') . '/' . ltrim($url, '/');
    }

    public function __toString()
    {
        return $this->build();
    }

    public function __debugInfo()
    {
        return [
            'url'    => $this->url,
            'vars'   => $this->vars,
            'suffix' => $this->suffix,
            'domain' => $this->domain,
        ];
    }
}
