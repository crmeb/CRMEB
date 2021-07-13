<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\api\middleware;


use app\Request;
use crmeb\interfaces\MiddlewareInterface;

/**
 * Class StationOpenMiddleware
 * @package app\api\middleware
 */
class StationOpenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        if (!sys_config('station_open', 1)) {
            return app('json')->make('410010', '站点升级中，请稍候访问');
        }
        return $next($request);
    }
}
