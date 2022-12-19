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

namespace app\outapi\middleware;


use app\Request;
use app\services\out\OutAccountServices;
use app\services\out\OutInterfaceServices;
use crmeb\interfaces\MiddlewareInterface;
use think\facade\Config;

/**
 * Class AuthTokenMiddleware
 * @package app\outapi\middleware
 */
class AuthTokenMiddleware implements MiddlewareInterface
{

    /**
     * @param Request $request
     * @param \Closure $next
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handle(Request $request, \Closure $next)
    {
        $token = trim(ltrim($request->header(Config::get('cookie.token_name', 'Authori-zation')), 'Bearer'));
        /** @var OutAccountServices $services */
        $services = app()->make(OutAccountServices::class);
        $outInfo = $services->parseToken($token);
        Request::macro('outId', function () use (&$outInfo) {
            return (int)$outInfo['id'];
        });

        Request::macro('outInfo', function () use (&$outInfo) {
            return $outInfo;
        });
        /** @var OutInterfaceServices $outInterfaceServices */
        $outInterfaceServices = app()->make(OutInterfaceServices::class);
        $outInterfaceServices->verifyAuth($request);

        return $next($request);
    }
}
