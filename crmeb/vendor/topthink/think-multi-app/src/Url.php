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

namespace think\app;

use think\App;
use think\Route;
use think\route\Url as UrlBuild;

/**
 * 路由地址生成
 */
class Url extends UrlBuild
{

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
        } elseif ('' === $url) {
            $url = $this->app->http->getName() . '/' . $request->controller() . '/' . $request->action();
        } else {
            // 解析到 应用/控制器/操作
            $controller = $request->controller();
            $app        = $this->app->http->getName();

            $path       = explode('/', $url);
            $action     = array_pop($path);
            $controller = empty($path) ? $controller : array_pop($path);
            $app        = empty($path) ? $app : array_pop($path);

            $url = $controller . '/' . $action;

            $bind = $this->app->config->get('app.domain_bind', []);

            if ($key = array_search($app, $bind)) {
                $domain = is_bool($domain) ? $key : $domain;
            } else {
                $map = $this->app->config->get('app.app_map', []);

                if ($key = array_search($app, $map)) {
                    $url = $key . '/' . $url;
                } else {
                    $url = $app . '/' . $url;
                }
            }

        }

        return $url;
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

            if (!$this->app->http->isBind()) {
                $url = $this->app->http->getName() . '/' . $url;
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

        $url = rtrim($file, '/') . '/' . ltrim($url, '/');

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

}
