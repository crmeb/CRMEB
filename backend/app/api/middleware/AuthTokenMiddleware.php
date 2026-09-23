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

namespace app\api\middleware;

use app\http\middleware\BaseAuthTokenMiddleware;
use app\services\user\UserAuthServices;
use crmeb\exceptions\AuthException;
use think\Request;

/**
 * C 端用户认证中间件（令牌提取/校验骨架见 BaseAuthTokenMiddleware）
 * Class AuthTokenMiddleware
 * @package app\api\middleware
 */
class AuthTokenMiddleware extends BaseAuthTokenMiddleware
{
    /**
     * 用户端认证服务
     * @return string
     */
    protected function authService(): string
    {
        return UserAuthServices::class;
    }

    /**
     * 强制登录失败时直接返回 JSON（保持 C 端接口无跳转语义）
     * @param AuthException $e 认证异常
     * @return mixed
     */
    protected function deny(AuthException $e)
    {
        return app('json')->make($e->getCode(), $e->getMessage());
    }

    /**
     * 绑定用户身份宏（user/tokenData/isLogin/uid），游客态时 isLogin=false、uid=0
     * @param Request $request 请求对象
     * @param mixed $authInfo 认证结果，游客态为 null
     * @return void
     */
    protected function bindIdentity(Request $request, $authInfo): void
    {
        if (!is_null($authInfo)) {
            $request->macro('user', function (?string $key = null) use (&$authInfo) {
                if ($key) {
                    return $authInfo['user'][$key] ?? '';
                }
                return $authInfo['user'];
            });
            $request->macro('tokenData', function () use (&$authInfo) {
                return $authInfo['tokenData'];
            });
        }
        $request->macro('isLogin', function () use (&$authInfo) {
            return !is_null($authInfo);
        });
        $request->macro('uid', function () use (&$authInfo) {
            return is_null($authInfo) ? 0 : (int)$authInfo['user']->uid;
        });
    }
}
