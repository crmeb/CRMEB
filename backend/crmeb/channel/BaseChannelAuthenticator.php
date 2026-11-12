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
declare(strict_types=1);

namespace crmeb\channel;

use crmeb\basic\BaseService;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;

/**
 * 用户渠道认证器抽象基类
 * 提供认证结果统一组装与授权缓存读取的公共实现，
 * 各渠道认证器继承本类，只需实现 login() 的登录方式分发与具体认证逻辑
 * @package crmeb\channel
 */
abstract class BaseChannelAuthenticator extends BaseService implements ChannelAuthenticatorInterface
{
    /**
     * 组装统一认证结果
     * 固化 ChannelAuthenticatorInterface 约定的返回结构，避免各认证逻辑重复手写字面量数组
     * @param int $uid 认证成功的用户ID，未完成认证传 0
     * @param array $userInfo 认证成功的用户信息，未完成认证传空数组
     * @param bool $isbind 是否需要绑定手机号
     * @param string $bindKey 绑定手机号流程的授权缓存 key，无需绑定时传空
     * @param array $extra 渠道附加数据，透传给前端
     * @return array 统一认证结果结构
     */
    protected function authResult(int $uid, array $userInfo, bool $isbind, string $bindKey = '', array $extra = []): array
    {
        return [
            'uid'       => $uid,
            'user_info' => $userInfo,
            'isbind'    => $isbind,
            'bind_key'  => $bindKey,
            'extra'     => $extra,
        ];
    }

    /**
     * 读取授权流程缓存数据
     * 两步式登录（先授权后绑定手机号）中，第一步授权数据缓存后凭 key 取回
     * @param string $key 授权缓存 key
     * @return array 授权缓存数据
     * @throws ApiException 缓存不存在或已过期时抛出
     */
    protected function getAuthCache(string $key): array
    {
        $data = CacheService::get($key);
        if (!$data) {
            throw new ApiException('授权已过期，请重新登录');
        }
        return $data;
    }

    /**
     * 授权登录默认实现
     * 无授权登录能力的渠道默认不支持，需授权登录的渠道必须覆写本方法
     * @param string $loginType 渠道授权登录方式标识
     * @param array $credentials 登录凭证
     * @return array 统一认证结果，结构同 login()
     * @throws ApiException 渠道未覆写时抛出不支持异常
     */
    public function oauthLogin(string $loginType, array $credentials): array
    {
        throw new ApiException('当前渠道不支持授权登录');
    }

    /**
     * 账号登录默认实现
     * 无账号登录能力的渠道（如纯授权登录的公众号）默认不支持，需账号登录的渠道覆写本方法
     * @param string $loginType 渠道账号登录方式标识
     * @param array $credentials 登录凭证
     * @return array 统一认证结果，结构同 login()
     * @throws ApiException 渠道未覆写时抛出不支持异常
     */
    public function accountLogin(string $loginType, array $credentials): array
    {
        throw new ApiException('当前渠道不支持账号登录');
    }

    /**
     * 绑定手机号默认实现
     * 无绑定流程能力的渠道默认不支持，需绑定手机号的渠道覆写本方法
     * @param string $loginType 渠道绑定流程标识
     * @param array $credentials 绑定凭证
     * @return array 统一认证结果，结构同 login()
     * @throws ApiException 渠道未覆写时抛出不支持异常
     */
    public function bindMobile(string $loginType, array $credentials): array
    {
        throw new ApiException('当前渠道不支持绑定手机号');
    }

    /**
     * 渠道注册默认实现
     * 账号密码注册由 app 层 LoginServices 统一处理（仅 H5），其余渠道登录即注册，
     * 渠道默认不支持独立注册，存在独立注册流程的渠道覆写本方法
     * @param array $credentials 注册凭证
     * @return array 统一认证结果，结构同 login()
     * @throws ApiException 渠道未覆写时抛出不支持异常
     */
    public function register(array $credentials): array
    {
        throw new ApiException('当前渠道不支持独立注册');
    }

    /**
     * 渠道注销默认实现
     * token 失效由 app 层统一入口 AuthController 处理，渠道侧无附加登录态需清理时默认成功，
     * 存在渠道侧登录态（如渠道缓存授权数据）需清理的渠道覆写本方法
     * @param int $uid 用户ID
     * @return bool 渠道侧注销是否成功
     */
    public function logout(int $uid): bool
    {
        return true;
    }
}
