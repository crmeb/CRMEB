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

/**
 * 用户渠道认证器接口
 * 约束各用户渠道（modules/channel/{端}）登录认证器的完整认证能力面：
 * 登录（授权登录/账号登录）、手机号绑定、注册、注销
 * 提供授权类登录（OAuth 扫码、公众号网页授权、小程序授权等）的渠道必须实现本接口；
 * 纯手机号验证码登录的渠道（如 h5）可不实现，直接走 app 层 LoginServices
 *
 * 能力职责边界：
 * - login/oauthLogin/accountLogin/bindMobile 为渠道差异化能力，由各渠道实现
 * - register/logout 由 BaseChannelAuthenticator 提供默认实现（渠道不支持独立注册时抛异常；
 *   token 失效由 app 层统一入口 AuthController 处理，渠道侧无附加登录态时注销默认成功），
 *   渠道存在差异化逻辑时覆写
 * 实现类由统一入口 AuthController 通过 ChannelServiceProxy 事件代理调度，
 * 认证器只负责认证并返回 uid，token 统一由 AuthController 调用 createToken 生成
 * @package crmeb\channel
 */
interface ChannelAuthenticatorInterface
{
    /**
     * 渠道登录认证（统一分发入口）
     * 按登录方式分发到对应能力方法（oauthLogin/accountLogin/bindMobile），
     * 认证失败抛出异常由统一入口转为响应
     * @param string $loginType 登录方式标识，由各渠道自行定义（如 wechat_auth/wechat_code/mobile）
     * @param array $credentials 登录凭证，结构由各渠道按登录方式自行约定
     * @return array 统一认证结果，固定结构：
     *   [
     *     'uid'       => int    认证成功的用户ID，未完成认证（如待绑定手机号）时为 0
     *     'user_info' => array  认证成功的用户信息，未完成认证时为空数组
     *     'isbind'    => bool   是否需要绑定手机号
     *     'bind_key'  => string 绑定手机号流程的授权缓存 key，无需绑定时为空字符串
     *     'extra'     => array  渠道附加数据，透传给前端
     *   ]
     * @throws \Throwable 认证失败时抛出对应异常（如 ApiException）
     */
    public function login(string $loginType, array $credentials): array;

    /**
     * 授权登录
     * 基于第三方 OAuth 授权（公众号网页授权、小程序 code 授权、APP 端授权等）完成登录认证，
     * 含两步式授权的各阶段，具体阶段由 $loginType 区分
     * @param string $loginType 渠道授权登录方式标识
     * @param array $credentials 登录凭证，结构由各渠道按授权方式自行约定
     * @return array 统一认证结果，结构同 login()
     * @throws \Throwable 授权失败时抛出对应异常
     */
    public function oauthLogin(string $loginType, array $credentials): array;

    /**
     * 账号登录
     * 基于账号体系的登录（手机号验证码登录、账号密码登录等），具体方式由 $loginType 区分
     * @param string $loginType 渠道账号登录方式标识
     * @param array $credentials 登录凭证，结构由各渠道按登录方式自行约定
     * @return array 统一认证结果，结构同 login()
     * @throws \Throwable 登录失败时抛出对应异常
     */
    public function accountLogin(string $loginType, array $credentials): array;

    /**
     * 绑定手机号
     * 两步式登录的第二步（凭授权缓存 key 补充手机号完成认证）或一步式授权+绑定，
     * 具体流程由 $loginType 区分
     * @param string $loginType 渠道绑定流程标识
     * @param array $credentials 绑定凭证（授权缓存 key、手机号、验证码等）
     * @return array 统一认证结果，结构同 login()
     * @throws \Throwable 绑定失败时抛出对应异常
     */
    public function bindMobile(string $loginType, array $credentials): array;

    /**
     * 渠道注册
     * 渠道存在独立注册流程时实现（如扫码注册），返回认证结果结构；
     * 账号密码注册由 app 层统一处理（H5），其余渠道登录即注册
     * @param array $credentials 注册凭证
     * @return array 统一认证结果，结构同 login()
     * @throws \Throwable 注册失败时抛出对应异常
     */
    public function register(array $credentials): array;

    /**
     * 渠道注销
     * 清理渠道侧登录态（如渠道缓存的授权数据）；token 失效由 app 层统一入口处理
     * @param int $uid 用户ID
     * @return bool 渠道侧注销是否成功
     */
    public function logout(int $uid): bool;
}
