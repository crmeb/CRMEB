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

namespace crmeb\auth;

use crmeb\exceptions\AuthException;
use crmeb\services\CacheService;
use crmeb\utils\JwtAuth;

/**
 * 认证令牌校验骨架
 * 收口四端 parseToken（api 用户/后台管理员/客服/外平台账号）中完全同构的校验链：
 * 空值检查 → 令牌桶缓存校验 → JWT 解析 → JWT 签名/时效验证（失败清理缓存并触发失败回调）
 * 各端差异（失败文案/异常码/主体查询/后置业务）由调用方自行处理
 * Class AuthTokenVerifier
 * @package crmeb\auth
 */
class AuthTokenVerifier
{
    /**
     * 校验令牌并返回解析结果
     * @param string $token 客户端令牌
     * @param string $emptyMsg 空令牌/undefined 占位值时的失败文案
     * @param string $cacheMissMsg 令牌桶缓存缺失时的失败文案
     * @param string $verifyFailMsg JWT 签名/时效验证失败时的失败文案
     * @param int $code 认证失败异常码（api/admin=401、kefu=402、out=0 默认）
     * @param callable|null $onFailure 失败回调 参数为 (?int $id, ?string $type)，用于失败前置业务（如后台编辑草稿暂存）
     * @return array 依次为 [$md5Token, $id, $type, $pwd, $cacheData]，缓存数据供调用方做身份一致性等后置校验
     * @throws AuthException
     */
    public static function verify(string $token, string $emptyMsg, string $cacheMissMsg, string $verifyFailMsg, int $code = 0, ?callable $onFailure = null): array
    {
        // 空令牌或前端未登录占位值直接拒绝
        if (!$token || $token === 'undefined') {
            throw new AuthException($emptyMsg, [], $code);
        }

        // 令牌桶缓存校验：登录时经 JwtAuth::createToken 写入，过期清理/单点登录挤下线后即失效
        $md5Token = md5($token);
        $cacheData = CacheService::get($md5Token, '');
        if (!$cacheData) {
            // 缓存虽失效但 JWT 自身可能仍可解析，还原身份供失败回调使用（如后台编辑商品草稿暂存场景）
            [$id, $type] = self::tryParse($token);
            $onFailure and $onFailure($id, $type);
            throw new AuthException($cacheMissMsg, [], $code);
        }

        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        // 解析 JWT 载荷（id/type/pwd 三元组，pwd 为后台改密踢人机制的可选密码指纹）
        [$id, $type, $pwd] = $jwtAuth->parseToken($token);

        // 签名与时效验证，失败则清理令牌桶缓存并触发失败回调
        try {
            $jwtAuth->verifyToken();
        } catch (\Throwable $e) {
            if (!request()->isCli()) {
                CacheService::delete($md5Token);
            }
            $onFailure and $onFailure($id, $type);
            throw new AuthException($verifyFailMsg, [], $code);
        }

        return [$md5Token, $id, $type, $pwd, $cacheData];
    }

    /**
     * 尝试解析 JWT 载荷（格式非法时静默返回空身份，不抛异常）
     * @param string $token 客户端令牌
     * @return array [?int $id, ?string $type]
     */
    protected static function tryParse(string $token): array
    {
        try {
            [$id, $type] = app()->make(JwtAuth::class)->parseToken($token);
            return [$id, $type];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}
