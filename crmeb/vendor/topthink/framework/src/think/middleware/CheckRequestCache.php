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

namespace think\middleware;

use Closure;
use think\Cache;
use think\Config;
use think\Request;
use think\Response;

/**
 * 请求缓存处理
 */
class CheckRequestCache
{
    /**
     * 缓存对象
     * @var Cache
     */
    protected $cache;

    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        // 请求缓存规则 true为自动规则
        'request_cache_key'    => true,
        // 请求缓存有效期
        'request_cache_expire' => null,
        // 全局请求缓存排除规则
        'request_cache_except' => [],
        // 请求缓存的Tag
        'request_cache_tag'    => '',
    ];

    public function __construct(Cache $cache, Config $config)
    {
        $this->cache  = $cache;
        $this->config = array_merge($this->config, $config->get('route'));
    }

    /**
     * 设置当前地址的请求缓存
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param mixed   $cache
     * @return Response
     */
    public function handle($request, Closure $next, $cache = null)
    {
        if ($request->isGet() && false !== $cache) {
            if (false === $this->config['request_cache_key']) {
                // 关闭当前缓存
                $cache = false;
            }

            $cache = $cache ?? $this->getRequestCache($request);

            if ($cache) {
                if (is_array($cache)) {
                    [$key, $expire, $tag] = array_pad($cache, 3, null);
                } else {
                    $key    = md5($request->url(true));
                    $expire = $cache;
                    $tag    = null;
                }

                $key = $this->parseCacheKey($request, $key);

                if (strtotime($request->server('HTTP_IF_MODIFIED_SINCE', '')) + $expire > $request->server('REQUEST_TIME')) {
                    // 读取缓存
                    return Response::create()->code(304);
                } elseif (($hit = $this->cache->get($key)) !== null) {
                    [$content, $header, $when] = $hit;
                    if (null === $expire || $when + $expire > $request->server('REQUEST_TIME')) {
                        return Response::create($content)->header($header);
                    }
                }
            }
        }

        $response = $next($request);

        if (isset($key) && 200 == $response->getCode() && $response->isAllowCache()) {
            $header                  = $response->getHeader();
            $header['Cache-Control'] = 'max-age=' . $expire . ',must-revalidate';
            $header['Last-Modified'] = gmdate('D, d M Y H:i:s') . ' GMT';
            $header['Expires']       = gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT';

            $this->cache->tag($tag)->set($key, [$response->getContent(), $header, time()], $expire);
        }

        return $response;
    }

    /**
     * 读取当前地址的请求缓存信息
     * @access protected
     * @param Request $request
     * @return mixed
     */
    protected function getRequestCache($request)
    {
        $key    = $this->config['request_cache_key'];
        $expire = $this->config['request_cache_expire'];
        $except = $this->config['request_cache_except'];
        $tag    = $this->config['request_cache_tag'];

        foreach ($except as $rule) {
            if (0 === stripos($request->url(), $rule)) {
                return;
            }
        }

        return [$key, $expire, $tag];
    }

    /**
     * 读取当前地址的请求缓存信息
     * @access protected
     * @param Request $request
     * @param mixed   $key
     * @return null|string
     */
    protected function parseCacheKey($request, $key)
    {
        if ($key instanceof \Closure) {
            $key = call_user_func($key, $request);
        }

        if (false === $key) {
            // 关闭当前缓存
            return;
        }

        if (true === $key) {
            // 自动缓存功能
            $key = '__URL__';
        } elseif (strpos($key, '|')) {
            [$key, $fun] = explode('|', $key);
        }

        // 特殊规则替换
        if (false !== strpos($key, '__')) {
            $key = str_replace(['__CONTROLLER__', '__ACTION__', '__URL__'], [$request->controller(), $request->action(), md5($request->url(true))], $key);
        }

        if (false !== strpos($key, ':')) {
            $param = $request->param();

            foreach ($param as $item => $val) {
                if (is_string($val) && false !== strpos($key, ':' . $item)) {
                    $key = str_replace(':' . $item, (string) $val, $key);
                }
            }
        } elseif (strpos($key, ']')) {
            if ('[' . $request->ext() . ']' == $key) {
                // 缓存某个后缀的请求
                $key = md5($request->url());
            } else {
                return;
            }
        }

        if (isset($fun)) {
            $key = $fun($key);
        }

        return $key;
    }
}
