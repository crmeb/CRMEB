<?php


namespace app\http\middleware;


use app\models\user\User;
use app\models\user\UserToken;
use app\Request;
use crmeb\exceptions\AuthException;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\repositories\UserRepository;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

/**
 * token验证中间件
 * Class AuthTokenMiddleware
 * @package app\http\middleware
 */
class AuthTokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next, bool $force = true)
    {
        $authInfo = null;
        $token = trim(ltrim($request->header('Authori-zation'), 'Bearer'));
        if(!$token)  $token = trim(ltrim($request->header('Authorization'), 'Bearer'));//正式版，删除此行，某些服务器无法获取到token调整为 Authori-zation
        try {
            $authInfo = UserRepository::parseToken($token);
        } catch (AuthException $e) {
            if ($force)
                return app('json')->make($e->getCode(), $e->getMessage());
        }

        if (!is_null($authInfo)) {
            Request::macro('user', function () use (&$authInfo) {
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
            return is_null($authInfo) ? 0 : $authInfo['user']->uid;
        });

        return $next($request);
    }
}