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
use app\services\user\UserAuthServices;
use crmeb\exceptions\AuthException;
use crmeb\interfaces\MiddlewareInterface;
use think\exception\DbException;

/**
 * Class AuthTokenMiddleware
 * @package app\api\middleware
 */
class AuthTokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next, bool $force = true)
    {
        $authInfo = null;
        $token = trim(ltrim($request->header('Authori-zation'), 'Bearer'));
        if (!$token) $token = trim(ltrim($request->header('Authorization'), 'Bearer'));//正式版，删除此行，某些服务器无法获取到token调整为 Authori-zation
        try {
            /** @var UserAuthServices $service */
            $service = app()->make(UserAuthServices::class);
            $authInfo = $service->parseToken($token);
        } catch (AuthException $e) {
            if ($force)
                return app('json')->make($e->getCode(), $e->getMessage());
        }

        if (!is_null($authInfo)) {
            Request::macro('user', function (string $key = null) use (&$authInfo) {
                if ($key) {
                    return $authInfo['user'][$key] ?? '';
                }
                return $authInfo['user'];
            });
            Request::macro('tokenData', function () use (&$authInfo) {
                return $authInfo['tokenData'];
            });
        }
        Request::macro('isLogin', function () use (&$authInfo) {
            return !is_null($authInfo);
        });
        Request::macro('uid', function () use (&$authInfo) {
            return is_null($authInfo) ? 0 : (int)$authInfo['user']->uid;
        });

        return $next($request);
    }
}
