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

namespace app\api\controller\v1;

use app\api\validate\user\RegisterValidates;
use app\Request;
use app\services\user\LoginServices;
use app\services\user\UserVisitServices;
use crmeb\services\CacheService;
use think\exception\ValidateException;
use think\facade\Config;

/**
 * 统一用户认证控制器
 * 提供跨渠道统一的登录、注册、注销接口
 * 通过 ChannelServiceProxy 事件代理调度各渠道 Authenticator，app 层不直接引用 modules
 * Class AuthController
 * @package app\api\controller\v1
 */
class AuthController
{
    /**
     * @var LoginServices
     */
    protected $services;

    /**
     * AuthController constructor.
     * @param LoginServices $services
     */
    public function __construct(LoginServices $services)
    {
        $this->services = $services;
    }

    /**
     * 统一登录入口
     * 通过 channel + login_type 路由到对应渠道认证器，认证成功后统一生成 token 触发 UserLogin 事件
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        // 接收渠道、登录方式、凭证
        [$channel, $loginType, $credentials] = $request->postMore([
            ['channel', ''],
            ['login_type', ''],
            ['credentials', []]
        ], true);

        if (!$channel || !$loginType) {
            return app('json')->fail('缺少渠道或登录方式');
        }

        // 通过事件代理调用渠道认证器（app 层不直接引用 modules）
        $results = event('ChannelServiceProxy', [
            'endpoint' => $channel,
            'service'  => 'Authenticator',
            'action'   => 'login',
            'args'     => [$loginType, $credentials]
        ]);

        $authResult = proxy_event_result($results, null);

        if (!$authResult) {
            return app('json')->fail('登录失败，不支持的渠道或认证异常');
        }

        // 需要绑定手机号：返回 bind_key 给前端
        if (!empty($authResult['isbind'])) {
            return app('json')->success('需要绑定手机号', [
                'isbind' => true,
                'key'    => $authResult['bind_key'] ?? '',
            ]);
        }

        $uid = (int)($authResult['uid'] ?? 0);

        // PC 扫码轮询未完成：返回轮询状态给前端
        if (!$uid && isset($authResult['extra']['status'])) {
            return app('json')->success('扫码中', $authResult['extra']);
        }

        if (!$uid) {
            return app('json')->fail('登录失败');
        }

        // 统一生成 token 并触发 UserLogin 事件
        $token = $this->services->createToken($uid, 'api', '', $channel, $loginType);

        // 记录用户访问
        if (!empty($authResult['user_info'])) {
            app()->make(UserVisitServices::class)->loginSaveVisit($authResult['user_info']);
        }

        return app('json')->success('登录成功', [
            'token'        => $token['token'],
            'expires_time' => $token['params']['exp'],
            'user_info'    => $authResult['user_info'] ?? [],
        ]);
    }

    /**
     * 统一注册入口
     * 目前仅支持 H5 账号密码注册，其他渠道采用"登录即注册"
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register(Request $request)
    {
        [$channel, $account, $captcha, $password, $spread] = $request->postMore([
            ['channel', 'h5'],
            ['account', ''],
            ['captcha', ''],
            ['password', ''],
            ['spread', 0]
        ], true);

        // 验证账号密码格式
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail('账号密码必须是在6到32位之间');
        }

        // 验证码校验
        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode) {
            return app('json')->fail('请先获取验证码');
        }
        if (substr($verifyCode, 0, 6) != $captcha) {
            return app('json')->fail('验证码错误');
        }
        if (md5($password) == md5('123456')) {
            return app('json')->fail('密码太过简单，请输入较为复杂的密码');
        }

        // 调用注册服务（UserRegister 事件在 LoginServices::register 内部触发）
        $user_type = $channel ?: 'h5';
        $registerStatus = $this->services->register($account, $password, $spread, $user_type);

        if ($registerStatus) {
            return app('json')->success('注册成功');
        }
        return app('json')->fail('注册失败');
    }

    /**
     * 统一退出登录
     * 删除 token 缓存并触发 UserLogout 事件
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        // 删除 token 缓存
        $key = trim(ltrim($request->header(Config::get('cookie.token_name')), 'Bearer'));
        CacheService::delete(md5($key));

        // 触发 UserLogout 事件（需授权中间件注入 uid）
        $uid = (int)$request->uid();
        if ($uid) {
            $this->services->logout($uid);
        }
        return app('json')->success('退出成功');
    }
}
