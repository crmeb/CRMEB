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

/**
 * Class BaseMiddleware
 * @package app\api\middleware
 */
class BaseMiddleware implements MiddlewareInterface
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @param bool $force
     * @return mixed
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/04/07
     */
    public function handle(Request $request, \Closure $next, bool $force = true)
    {
        if (!$request->hasMacro('uid')) {
            $request->macro('uid', function(){ return 0; });
        }
        if (!$request->hasMacro('adminId')) {
            $request->macro('adminId', function(){ return 0; });
        }
        if (!$request->hasMacro('kefuId')) {
            $request->macro('kefuId', function(){ return 0; });
        }

        return $next($request);
    }
}
