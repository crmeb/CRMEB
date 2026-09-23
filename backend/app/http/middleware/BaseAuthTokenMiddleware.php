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

namespace app\http\middleware;

use crmeb\auth\TokenExtractor;
use crmeb\exceptions\AuthException;
use crmeb\interfaces\MiddlewareInterface;
use think\Request;

/**
 * 认证中间件公共骨架
 * 收口三端（api/adminapi/outapi）token 认证中间件的重复流程：
 * OPTIONS 预检放行 → 统一提取令牌 → 调用各端认证服务解析 → 身份宏注入
 * 子类只需声明认证服务与身份绑定方式；force=false 时认证失败降级为游客态放行
 * Class BaseAuthTokenMiddleware
 * @package app\http\middleware
 */
abstract class BaseAuthTokenMiddleware implements MiddlewareInterface
{
    /**
     * 各端认证服务类名（须提供 parseToken(string $token) 方法）
     * @return string
     */
    abstract protected function authService(): string;

    /**
     * 将解析出的身份信息绑定为请求宏（如 uid()/adminId() 等）
     * @param Request $request 请求对象
     * @param mixed $authInfo 认证结果，游客态（force=false 且认证失败）时为 null
     * @return void
     */
    abstract protected function bindIdentity(Request $request, $authInfo): void;

    /**
     * 认证失败（force=true）时的响应处理
     * 默认将异常抛给全局异常处理器，子类可覆写为直接返回 JSON（如 C 端 api）
     * @param AuthException $e 认证异常
     * @return mixed
     */
    protected function deny(AuthException $e)
    {
        throw $e;
    }

    /**
     * 中间件统一入口
     * @param Request $request 请求对象
     * @param \Closure $next 下一步
     * @param bool $force 是否强制登录（true=认证失败即拒绝；false=降级游客态放行）
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, bool $force = true)
    {
        // 跨域预检请求直接放行
        if ($request->method(true) === 'OPTIONS') {
            return $next($request);
        }

        $authInfo = null;
        try {
            $authInfo = app()->make($this->authService())->parseToken(TokenExtractor::extract($request));
        } catch (AuthException $e) {
            // 强制登录场景拒绝请求；可选登录场景降级为游客态
            if ($force) {
                return $this->deny($e);
            }
        }

        $this->bindIdentity($request, $authInfo);
        return $next($request);
    }
}
