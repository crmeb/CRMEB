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

namespace app\kefuapi\middleware;


use app\Request;
use app\services\kefu\LoginServices;
use crmeb\interfaces\MiddlewareInterface;
use think\facade\Config;

/**
 * Class KefuAuthTokenMiddleware
 * @package app\kefu\middleware
 */
class KefuAuthTokenMiddleware implements MiddlewareInterface
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
        $authInfo = null;
        $token = trim(ltrim($request->header(Config::get('cookie.token_name', 'Authori-zation')), 'Bearer'));
        /** @var LoginServices $services */
        $services = app()->make(LoginServices::class);
        $kefuInfo = $services->parseToken($token);
        Request::macro('kefuId', function () use (&$kefuInfo) {
            return (int)$kefuInfo['id'];
        });

        Request::macro('kefuInfo', function () use (&$kefuInfo) {
            return $kefuInfo;
        });

        return $next($request);
    }
}
