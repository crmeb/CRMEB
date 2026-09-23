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
declare(strict_types=1);

namespace think\route;

use think\App;
use think\Route;

/**
 * 路由地址生成
 */
class Url
{
    /**
     * URL 根地址
     * @var string
     */
    protected $root = '';

    /**
     * HTTPS
     * @var bool
     */
    protected $https;

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
     * @param  Route  $route 路由对象
     * @param  App    $app App对象
     * @param  string $url URL地址
     * @param  array  $vars 参数
     */
    public function __construct(protected Route $route, protected App $app, protected string $url = '', protected array $vars = [])
    {
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
    public function suffix(string|bool $suffix)
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
    public function domain(string|bool $domain)
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
     * 设置是否使用HTTPS
     * @access public
     * @param  bool $https
     * @return $this
     */
    public function https(bool $https = true)
    {
        $this->https = $https;
        return $this;
    }

    /**
     * 检测域名
     * @access protected
     * @param  string      $url URL
     * @param  string|true $domain 域名
     * @return string
     */
    protected function parseDomain(string &$url, string|bool $domain): string
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
                $routeDomain = array_keys($domains);
                foreach ($routeDomain as $domainPrefix) {
                    if (str_starts_with($domainPrefix, '*.') && str_contains($domain, ltrim($domainPrefix, '*.')) !== false) {
                        foreach ($domains as $key => $rule) {
                            $rule = is_array($rule) ? $rule[0] : $rule;
                            if (is_string($rule) && !str_contains($key, '*') && str_starts_with($url, $rule)) {
                                $url    = ltrim($url, $rule);
                                $domain = $key;

                                // 生成对应子域名
                                if (!empty($rootDomain)) {
                                    $domain .= $rootDomain;
                                }
                                break;
                            } elseif (str_contains($key, '*')) {
                                if (!empty($rootDomain)) {
                                    $domain .= $rootDomain;
                                }

                                break;
                            }
                        }
                    }
                }
            }
        } elseif (!str_contains($domain, '.') && !str_starts_with($domain, $rootDomain)) {
            $domain .= '.' . $rootDomain;
        }

        if (str_contains($domain, '://')) {
            $scheme = '';
        } else {
            $scheme = $this->https || $request->isSsl() ? 'https://' : 'http://';
        }

        return $scheme . $domain;
    }

    /**
     * 解析URL后缀
     * @access protected
     * @param  string|bool $suffix 后缀
     * @return string
     */
    protected function parseSuffix(string|bool $suffix): string
    {
        if ($suffix) {
            $suffix = true === $suffix ? $this->route->config('url_html_suffix') : $suffix;

            if (is_string($suffix) && $pos = strpos($suffix, '|')) {
                $suffix = substr($suffix, 0, $pos);
            }
        }

        return (empty($suffix) || str_starts_with($suffix, '.')) ? (string) $suffix : '.' . $suffix;
    }

    /**
     * 直接解析URL地址
     * @access protected
     * @param  string      $url URL
     * @param  string|bool $domain Domain
     * @return string
     */
    protected function parseUrl(string $url, string | bool &$domain): string
    {
        $request = $this->app->request;

        if (str_starts_with($url, '/')) {
            // 直接作为路由地址解析
            $url = substr($url, 1);
        } elseif ('' === $url) {
            $url  = $request->pathinfo();
        } else {
            $controller = $request->controller();
            $path       = explode('/', $url);
            $action     = array_pop($path);
            $controller = empty($path) ? $controller : array_pop($path);
            $url        = $controller . '/' . $action;
            $auto       = $this->route->getName('__think_auto_route__');
            if (!empty($auto) && !strpos($controller, '.')) {
                $module = empty($path) ? $request->layer() : array_pop($path);
                $url    = $module . '/' . $url;
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

                if (str_contains($name, '?')) {
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
     * @param  string|bool $allowDomain 允许域名
     * @return array
     */
    protected function getRuleUrl(array $rule, array &$vars = [], string|bool $allowDomain = ''): array
    {
        $request = $this->app->request;
        if (is_string($allowDomain) && !str_contains($allowDomain, '.')) {
            $allowDomain .= '.' . $request->rootDomain();
        }
        $port = $request->port();

        foreach ($rule as $item) {
            $url     = $item['rule'];
            $pattern = $this->parseVar($url);
            $domain  = $item['domain'];
            $suffix  = $item['suffix'];

            if ('-' == $domain) {
                $domain = is_string($allowDomain) ? $allowDomain : $request->host(true);
            }

            if (is_string($allowDomain) && $domain != $allowDomain) {
                continue;
            }

            if ($port && !in_array($port, [80, 443])) {
                $domain .= ':' . $port;
            }

            if (empty($pattern)) {
                return [rtrim($url, '?-'), $domain, $suffix];
            }

            $type = $this->route->config('url_common_param');
            $keys = [];

            foreach ($pattern as $key => $val) {
                if (isset($vars[$key])) {
                    $url    = str_replace(['[:' . $key . ']', '<' . $key . '?>', ':' . $key, '<' . $key . '>'], $type ? (string) $vars[$key] : urlencode((string) $vars[$key]), $url);
                    $keys[] = $key;
                    $url    = str_replace(['/?', '-?'], ['/', '-'], $url);
                    $result = [rtrim($url, '?-'), $domain, $suffix];
                } elseif (2 == $val) {
                    $url    = str_replace(['/[:' . $key . ']', '[:' . $key . ']', '<' . $key . '?>'], '', $url);
                    $url    = str_replace(['/?', '-?'], ['/', '-'], $url);
                    $result = [rtrim($url, '?-'), $domain, $suffix];
                } else {
                    $result = null;
                    $keys   = [];
                    break;
                }
            }

            $vars = array_diff_key($vars, array_flip($keys));

            if (isset($result)) {
                return $result;
            }
        }

        return [];
    }

    /**
     * 生成URL地址
     * @access public
     * @return string
     */
    public function build(): string
    {
        // 解析URL
        $url     = $this->url;
        $suffix  = $this->suffix;
        $domain  = $this->domain;
        $request = $this->app->request;
        $vars    = $this->vars;

        if (str_starts_with($url, '[') && $pos = strpos($url, ']')) {
            // [name] 表示使用路由命名标识生成URL
            $name = substr($url, 1, $pos - 1);
            $url  = 'name' . substr($url, $pos + 1);
        }

        if (!str_contains($url, '://') && !str_starts_with($url, '/')) {
            $info = parse_url($url);
            $url  = !empty($info['path']) ? $info['path'] : '';

            if (isset($info['fragment'])) {
                // 解析锚点
                $anchor = $info['fragment'];

                if (str_contains($anchor, '?')) {
                    // 解析参数
                    [$anchor, $info['query']] = explode('?', $anchor, 2);
                }

                if (str_contains($anchor, '@')) {
                    // 解析域名
                    [$anchor, $domain] = explode('@', $anchor, 2);
                }
            } elseif (str_contains($url, '@') && !str_contains($url, '\\')) {
                // 解析域名
                [$url, $domain] = explode('@', $url, 2);
            }
        }

        if ($url) {
            $checkName   = $name ?? $url . (isset($info['query']) ? '?' . $info['query'] : '');
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
        } elseif (!empty($rule) && isset($name)) {
            throw new \InvalidArgumentException('route name not exists:' . $name);
        } else {
            // 检测URL绑定
            $bind = $this->route->getDomainBind($domain && is_string($domain) ? $domain : null);

            if ($bind && str_starts_with($url, $bind)) {
                $url = substr($url, strlen($bind) + 1);
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
        if ($file && !str_starts_with($request->url(), $file)) {
            $file = str_replace('\\', '/', dirname($file));
        }

        $url = rtrim($file, '/') . '/' . $url;

        // URL后缀
        if (str_ends_with($url, '/') || '' == $url) {
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
                $url .= $suffix . ($vars ? '?' . $vars : '') . $anchor;
            } else {
                foreach ($vars as $var => $val) {
                    $val = (string) $val;
                    if ('' !== $val) {
                        $url .= $depr . $var . $depr . urlencode($val);
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
