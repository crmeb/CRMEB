<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\http\middleware;


use think\Request;
use crmeb\interfaces\MiddlewareInterface;
use think\facade\Config;
use think\Response;

/**
 * 跨域中间件
 * Class AllowOriginMiddleware
 * @package app\http\middleware
 */
class AllowOriginMiddleware implements MiddlewareInterface
{

    /**
     * 允许跨域的域名
     * @var string
     */
    protected $cookieDomain;

    /**
     * 获取跨域响应头
     * @param Request|null $request
     * @return array
     */
    public static function corsHeaders($request = null): array
    {
        $header = Config::get('cookie.header', []);
        if (!is_array($header)) {
            $header = [];
        }

        $origin = $request ? $request->header('origin') : request()->header('origin');
        $cookieDomain = Config::get('cookie.domain', '');
        if ($origin) {
            if ($cookieDomain === '' || strpos($origin, $cookieDomain) !== false) {
                $header['Access-Control-Allow-Origin'] = $origin;
                $header['Vary'] = 'Origin';
            } else {
                unset($header['Access-Control-Allow-Origin']);
                unset($header['Access-Control-Allow-Credentials']);
            }
        } elseif ($cookieDomain === '') {
            $header['Access-Control-Allow-Origin'] = '*';
            unset($header['Access-Control-Allow-Credentials']);
        } else {
            unset($header['Access-Control-Allow-Origin']);
            unset($header['Access-Control-Allow-Credentials']);
        }

        $requestHeaders = $request ? $request->header('access-control-request-headers') : '';
        if ($requestHeaders) {
            $headers = array_filter(array_map('trim', explode(',', ($header['Access-Control-Allow-Headers'] ?? '') . ',' . $requestHeaders)));
            $header['Access-Control-Allow-Headers'] = implode(', ', array_unique($headers));
        }

        return $header;
    }

    /**
     * @param Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle(Request $request, \Closure $next)
    {
        $header = self::corsHeaders($request);

        // 使用原生 header() 确保 CORS 头始终发送，防止因 output buffering 或服务端配置导致丢失
        if (!headers_sent()) {
            foreach ($header as $name => $val) {
                header($name . ': ' . $val);
            }
        }

        if ($request->method(true) == 'OPTIONS') {
            $response = Response::create('ok')->code(200);
        } else {
            $response = $next($request);
        }

        // 同时通过 Response 对象设置，双保险
        return $response->header($header);
    }
}
