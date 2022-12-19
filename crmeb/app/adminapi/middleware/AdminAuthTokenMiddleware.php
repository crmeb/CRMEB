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

namespace app\adminapi\middleware;


use app\Request;
use app\services\system\admin\AdminAuthServices;
use crmeb\interfaces\MiddlewareInterface;
use think\facade\Config;
use crmeb\services\CacheService;

/**
 * 后台登陆验证中间件
 * Class AdminAuthTokenMiddleware
 * @package app\adminapi\middleware
 */
class AdminAuthTokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        $authInfo = null;
        $token = trim(ltrim($request->header(Config::get('cookie.token_name', 'Authori-zation')), 'Bearer'));
        /** @var AdminAuthServices $service */
        $service = app()->make(AdminAuthServices::class);
        $adminInfo = $service->parseToken($token);
        Request::macro('isAdminLogin', function () use (&$adminInfo) {
            return !is_null($adminInfo);
        });
        Request::macro('adminId', function () use (&$adminInfo) {
            return $adminInfo['id'];
        });

        Request::macro('adminInfo', function () use (&$adminInfo) {
            return $adminInfo;
        });

        return $next($request);
    }
}
