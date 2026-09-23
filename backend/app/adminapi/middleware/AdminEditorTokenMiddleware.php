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

namespace app\adminapi\middleware;


use think\Request;
use crmeb\exceptions\AuthException;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\services\CacheService;
use crmeb\utils\JwtAuth;
use think\facade\Config;

/**
 * 后台登陆验证中间件
 * Class AdminAuthTokenMiddleware
 * @package app\adminapi\middleware
 */
class AdminEditorTokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        // Missing fileToken should be handled by parseToken as an expired login,
        // without passing null into trim on PHP 8.1+.
        $fileToken = $request->get('fileToken', '');
        $token = CacheService::get(trim((string) $fileToken));

        // 文件编辑 token 校验（原 SystemFileServices::parseToken 逻辑内联，避免 app 层引用模块服务）
        if (!$token || $token === 'undefined') {
            throw new AuthException('登录已过期,请重新登录', [], 403);
        }

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        //设置解析token
        [$id, $type, $pwd] = $jwtAuth->parseToken($token);

        //检测token是否过期
        $md5Token = md5($token);
        if (!CacheService::has($md5Token) || !(CacheService::get($md5Token))) {
            throw new AuthException('登录已过期,请重新登录', [], 403);
        }

        //验证token
        try {
            $jwtAuth->verifyToken();
        } catch (\Throwable $e) {
            if (!request()->isCli()) {
                CacheService::delete($md5Token);
            }
            throw new AuthException('登录已过期,请重新登录', [], 403);
        }

        if ($id !== md5(config('filesystem.password'))) {
            throw new AuthException('登录已过期,请重新登录', [], 403);
        }

        if ($pwd !== md5(config('filesystem.password'))) {
            throw new AuthException('登录已过期,请重新登录', [], 403);
        }

        return $next($request);
    }
}
