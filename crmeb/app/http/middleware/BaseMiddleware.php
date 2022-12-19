<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\http\middleware;


use app\Request;
use crmeb\interfaces\MiddlewareInterface;

/**
 * Class BaseMiddleware
 * @package app\api\middleware
 */
class BaseMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next, bool $force = true)
    {
        if (!Request::hasMacro('uid')) {
            Request::macro('uid', function(){ return 0; });
        }
        if (!Request::hasMacro('adminId')) {
            Request::macro('adminId', function(){ return 0; });
        }
        if (!Request::hasMacro('kefuId')) {
            Request::macro('kefuId', function(){ return 0; });
        }

        return $next($request);
    }
}
