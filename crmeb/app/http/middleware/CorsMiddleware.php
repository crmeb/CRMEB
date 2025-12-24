<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\http\middleware;

use app\Request;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\utils\Cors;
use think\Response;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        $origin = $request->header("origin");
        $header = Cors::buildHeaders($origin);

        if ($request->method(true) === "OPTIONS") {
            // 预检请求：无 Origin 的 OPTIONS 直接 200（不下发 Allow-Origin），避免影响非浏览器客户端。
            // 有 Origin 且不在白名单：403。
            if ($origin && Cors::allowedOrigin($origin) === "") {
                return Response::create("forbidden")
                    ->code(403)
                    ->header($header);
            }
            return Response::create("ok")->code(200)->header($header);
        }
        return $next($request);
    }
}
