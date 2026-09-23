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

use app\Request;
use app\services\message\channel\SmsChannel;
use think\facade\Config;
use crmeb\services\CacheService;
use app\services\user\LoginServices;
use think\exception\ValidateException;
use app\api\validate\user\RegisterValidates;
use think\facade\Env;

/**
 * 用户登录控制器（旧入口）
 *
 * @deprecated 该控制器为历史遗留的分散登录入口，仅保留向后兼容。
 *             新业务请统一使用 {@see AuthController}：
 *               - 账号密码登录：POST /api/auth/login      (channel=h5,    login_type=password)
 *               - 手机号登录：  POST /api/auth/login      (channel=h5,    login_type=mobile)
 *               - Apple 登录：  POST /api/auth/login      (channel=wechat_mp, login_type=app_auth)
 *               - H5 注册：     POST /api/auth/register
 *               - 退出登录：    GET  /api/auth/logout
 *             旧入口内部已转发到统一逻辑（LoginServices::createToken/logout 已触发 UserLogin/UserLogout 事件），
 *             新增渠道不要再追加方法到本控制器。
 * Class LoginController
 * @package app\api\controller\v1
 */
class LoginController
{
    /**
     * @var LoginServices
     */
    protected $services;

    /**
     * LoginController constructor.
     * @param LoginServices $services
     */
    public function __construct(LoginServices $services)
    {
        $this->services = $services;
    }

    /**
     * H5账号登陆
     *
     * @deprecated 请使用 POST /api/auth/login（channel=h5, login_type=password）
     *             旧入口内部已转发到统一逻辑：LoginServices::login → createToken 时触发 UserLogin 事件
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(Request $request)
    {
        [$account, $password, $spread, $agent_id] = $request->postMore([
            'account',
            'password',
            'spread',
            ['agent_id', 0]
        ], true);
        if (!$account || !$password) {
            return app('json')->fail('请输入账号和密码');
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail('账号密码必须是在6到32位之间');
        }
        return app('json')->success('登录成功', $this->services->login($account, $password, $spread, $agent_id));
    }

    /**
     * 退出登录
     *
     * @deprecated 请使用 GET /api/auth/logout
     *             旧入口内部已转发到统一逻辑：LoginServices::logout 触发 UserLogout 事件
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $key = trim(ltrim($request->header(Config::get('cookie.token_name')), 'Bearer'));
        CacheService::delete(md5($key));
        // 触发 UserLogout 事件（logout 路由在授权中间件组，可获取 uid）
        $uid = (int)$request->uid();
        if ($uid) {
            $this->services->logout($uid);
        }
        return app('json')->success('退出成功');
    }

    /**
     * 获取发送验证码key
     * @return mixed
     */
    public function verifyCode()
    {
        $unique = password_hash(uniqid(true), PASSWORD_BCRYPT);
        CacheService::set('sms.key.' . $unique, 0, 300);
        $time = sys_config('verify_expire_time', 1);
        return app('json')->success(['key' => $unique, 'expire_time' => $time]);
    }

    /**
     * 获取图片验证码
     * @param Request $request
     * @return \think\Response
     */
    public function captcha(Request $request)
    {
        ob_clean();
        $rep = captcha();
        $key = app('session')->get('captcha.key');
        $uni = $request->get('key');
        if ($uni) {
            CacheService::set('sms.key.cap.' . $uni, $key, 300);
        }
        return $rep;
    }

    /**
     * 验证验证码是否正确
     * @param $uni
     * @param string $code
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function checkCaptcha($uni, string $code): bool
    {
        $cacheName = 'sms.key.cap.' . $uni;
        if (!CacheService::has($cacheName)) {
            return false;
        }
        $key = CacheService::get($cacheName);
        $code = mb_strtolower($code, 'UTF-8');
        $res = password_verify($code, $key);
        if ($res) {
            CacheService::delete($cacheName);
        }
        return $res;
    }

    /**
     * 验证码发送
     * @param Request $request
     * @param SmsChannel $services
     * @return mixed
     */
    public function verify(Request $request, SmsChannel $services)
    {
        [$phone, $type, $key, $captchaType, $captchaVerification] = $request->postMore([
            ['phone', 0],
            ['type', ''],
            ['key', ''],
            ['captchaType', ''],
            ['captchaVerification', ''],
        ], true);

        $keyName = 'sms.key.' . $key;
        if (!CacheService::has($keyName)) return app('json')->fail('发送验证码失败,请刷新页面重新获取');

        // 验证限制
        // 验证码每分钟发送上限
        $maxMinuteCountKey = 'sms.minute.' . $phone . date('YmdHi');
        $minuteCount = 0;
        if (CacheService::has($maxMinuteCountKey)) {
            $minuteCount = CacheService::get($maxMinuteCountKey) ?? 0;
            $maxMinuteCount = Config::get('sms.maxMinuteCount', 5);
            if ($minuteCount > $maxMinuteCount) return app('json')->fail('同一手机号每分钟最多发送' . $maxMinuteCount . '条');
        }

        // 验证码单个手机每日发送上限
        $maxPhoneCountKey = 'sms.phone.' . $phone . '.' . date('Ymd');
        $phoneCount = 0;
        if (CacheService::has($maxPhoneCountKey)) {
            $phoneCount = CacheService::get($maxPhoneCountKey) ?? 0;
            $maxPhoneCount = Config::get('sms.maxPhoneCount', 20);
            if ($phoneCount > $maxPhoneCount) return app('json')->fail('同一手机号每天最多发送' . $maxPhoneCount . '条');
        }

        // 验证码单个手机每日发送上限
        $maxIpCountKey = 'sms.ip.' . app()->request->ip() . '.' . date('Ymd');
        $ipCount = 0;
        if (CacheService::has($maxIpCountKey)) {
            $ipCount = CacheService::get($maxIpCountKey) ?? 0;
            $maxIpCount = Config::get('sms.maxIpCount', 50);
            if ($ipCount > $maxIpCount) return app('json')->fail('同一IP每天最多发送' . $maxIpCount . '条');
        }

        //二次验证
        try {
            aj_captcha_check_two($captchaType, $captchaVerification);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }

        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getMessage());
        }
        $time = sys_config('verify_expire_time', 1);
        $smsCode = $this->services->verify($services, $phone, $type, $time);
        if ($smsCode) {
            CacheService::set('code_' . $phone, $smsCode, $time * 60);
            CacheService::set($maxMinuteCountKey, (int)$minuteCount + 1, 61);
            CacheService::set($maxPhoneCountKey, (int)$phoneCount + 1, 86401);
            CacheService::set($maxIpCountKey, (int)$ipCount + 1, 86401);
            return app('json')->success('验证码发送成功');
        } else {
            return app('json')->fail('验证码发送失败');
        }
    }

    /**
     * H5注册新用户
     *
     * @deprecated 请使用 POST /api/auth/register（channel=h5）
     *             旧入口内部已转发到统一逻辑：LoginServices::register 内部触发 UserRegister 事件
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register(Request $request)
    {
        [$account, $captcha, $password, $spread] = $request->postMore([['account', ''], ['captcha', ''], ['password', ''], ['spread', 0]], true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail('账号密码必须是在6到32位之间');
        }
        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');
        if (md5($password) == md5('123456')) return app('json')->fail('密码太过简单，请输入较为复杂的密码');

        $registerStatus = $this->services->register($account, $password, $spread, 'h5');
        if ($registerStatus) {
            return app('json')->success('注册成功');
        }
        return app('json')->fail('注册失败');
    }

    /**
     * 密码修改
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function reset(Request $request)
    {
        [$account, $captcha, $password] = $request->postMore([['account', ''], ['captcha', ''], ['password', '']], true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail('账号密码必须是在6到32位之间');
        }
        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            return app('json')->fail('验证码错误');
        }
        if ($password == '123456') return app('json')->fail('密码太过简单，请输入较为复杂的密码');
        $resetStatus = $this->services->reset($account, $password);
        if ($resetStatus) return app('json')->success('修改成功');
        return app('json')->fail('修改失败');
    }

    /**
     * 手机号登录
     *
     * @deprecated 请使用 POST /api/auth/login（channel=h5, login_type=mobile）
     *             旧入口内部已转发到统一逻辑：LoginServices::mobile → createToken 时触发 UserLogin 事件
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mobile(Request $request)
    {
        [$phone, $captcha, $spread, $agent_id] = $request->postMore([['phone', ''], ['captcha', ''], ['spread', 0], ['agent_id', 0]], true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            return app('json')->fail('验证码错误');
        }
        $user_type = $request->getFromType() ? $request->getFromType() : 'h5';
        $token = $this->services->mobile($phone, $spread, $user_type, $agent_id);
        if ($token) {
            CacheService::delete('code_' . $phone);
            return app('json')->success('登录成功', $token);
        } else {
            return app('json')->fail('退出成功');
        }
    }

    /**
     * H5切换登陆
     *
     * @deprecated 切换登录场景后续将由统一认证接口承载，请勿新增调用
     *             旧入口内部已转发到统一逻辑：LoginServices::switchAccount → createToken 时触发 UserLogin 事件
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function switchH5(Request $request)
    {
        $from = $request->post('from', 'wechat');
        $user = $request->user();
        $token = $this->services->switchAccount($user, $from);
        if ($token) {
            $token['userInfo'] = $user;
            return app('json')->success('登录成功', $token);
        } else
            return app('json')->fail('退出成功');
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bindingPhone(Request $request)
    {
        list($phone, $captcha, $key) = $request->postMore([
            ['phone', ''],
            ['captcha', ''],
            ['key', '']
        ], true);
        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (!$key) {
            return app('json')->fail('参数错误');
        }
        if (!$phone) {
            return app('json')->fail('请输入手机号');
        }
        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            return app('json')->fail('验证码错误');
        }
        $re = $this->services->bindind_phone($phone, $key);
        if ($re) {
            CacheService::delete('code_' . $phone);
            return app('json')->success('绑定成功', $re);
        } else
            return app('json')->fail('绑定失败');
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userBindingPhone(Request $request)
    {
        list($phone, $captcha, $step) = $request->postMore([
            ['phone', ''],
            ['captcha', ''],
            ['step', 0]
        ], true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (!$step) {
            //验证验证码
            $verifyCode = CacheService::get('code_' . $phone);
            if (!$verifyCode)
                return app('json')->fail('请先获取验证码');
            $verifyCode = substr($verifyCode, 0, 6);
            if ($verifyCode != $captcha)
                return app('json')->fail('验证码错误');
        }
        $uid = (int)$request->uid();
        $re = $this->services->userBindindPhone($uid, $phone, $step);
        if ($re) {
            CacheService::delete('code_' . $phone);
            return app('json')->success($re['msg'] ?? '绑定成功', $re['data'] ?? []);
        } else
            return app('json')->fail('绑定失败');
    }

    public function updateBindingPhone(Request $request)
    {
        [$phone, $captcha] = $request->postMore([
            ['phone', ''],
            ['captcha', ''],
        ], true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');
        $uid = (int)$request->uid();
        $re = $this->services->updateBindindPhone($uid, $phone);
        if ($re) {
            CacheService::delete('code_' . $phone);
            return app('json')->success($re['msg'] ?? '修改成功', $re['data'] ?? []);
        } else
            return app('json')->fail('修改失败');
    }

    /**
     * 设置扫描二维码状态
     * @param string $code
     * @return mixed
     */
    public function setLoginKey(string $code)
    {
        if (!$code) {
            return app('json')->fail('扫码失败请重新扫描');
        }
        $cacheCode = CacheService::get($code);
        if ($cacheCode === false || $cacheCode === null) {
            return app('json')->fail('二维码已过期请重新扫描');
        }
        CacheService::set($code, '0', 600);
        return app('json')->success();
    }

    /**
     * apple快捷登陆
     *
     * @deprecated 请使用 POST /api/auth/login（channel=wechat_mp, login_type=app_auth）
     *             旧入口内部已转发到统一逻辑：通过 ChannelServiceProxy 调用 wechat_mp 的 appAuth
     *             登录成功后由 LoginServices::createToken 触发 UserLogin 事件
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function appleLogin(Request $request)
    {
        [$openId, $phone, $email, $captcha] = $request->postMore([
            ['openId', ''],
            ['phone', ''],
            ['email', ''],
            ['captcha', '']
        ], true);
        if ($phone) {
            if (!$captcha) {
                return app('json')->fail('请输入验证码');
            }
            //验证验证码
            $verifyCode = CacheService::get('code_' . $phone);
            if (!$verifyCode)
                return app('json')->fail('请先获取验证码');
            $verifyCode = substr($verifyCode, 0, 6);
            if ($verifyCode != $captcha) {
                CacheService::delete('code_' . $phone);
                return app('json')->fail('验证码错误');
            }
        } else {
			if (!$openId) {
				return app('json')->fail('参数错误');
			}
		}
        if ($email === '') $email = substr(md5($openId), 0, 12);
        $userInfo = [
            'openId' => $openId,
            'unionid' => '',
            'avatarUrl' => sys_config('h5_avatar'),
            'nickName' => $email,
        ];
        //通过事件代理调用微信服务的 appAuth 方法，避免 app 层直接引用 modules
        $results = event('ChannelServiceProxy', ['endpoint' => 'wechat_mp', 'service' => 'WechatServices', 'action' => 'appAuth', 'args' => [$userInfo, $phone, 'apple']]);
        $token = proxy_event_result($results, null);
        if ($token) {
            return app('json')->success('登录成功', $token);
        } else if ($token === false) {
            return app('json')->success('登录成功', ['isbind' => true]);
        } else {
            return app('json')->fail('登录失败');
        }
    }

    /**
     * 滑块验证
     * @return mixed
     */
    public function ajcaptcha(Request $request)
    {
        $captchaType = $request->get('captchaType');
        return app('json')->success(aj_captcha_create($captchaType));
    }

    /**
     * 一次验证
     * @return mixed
     */
    public function ajcheck(Request $request)
    {
        [$token, $pointJson, $captchaType] = $request->postMore([
            ['token', ''],
            ['pointJson', ''],
            ['captchaType', ''],
        ], true);
        try {
            aj_captcha_check_one($captchaType, $token, $pointJson);
            return app('json')->success();
        } catch (\Throwable $e) {
            return app('json')->fail('验证码错误');
        }
    }

    /**
     * 远程登录接口
     *
     * @deprecated 调试场景接口，仅在 app_debug=true 时可用；统一认证接口未覆盖此场景。
     *             旧入口内部已转发到统一逻辑：LoginServices::remoteRegister → createToken 时触发 UserLogin 事件
     * @param Request $request
     * @return \think\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/21
     */
    public function remoteRegister(Request $request)
    {
        if (!Env::get('app_debug', false)) {
            return app('json')->fail('生产环境无法使用此功能，如需使用请修改.env文件中app_debug项为true');
        }
        [$remote_token] = $request->getMore([
            ['remote_token', ''],
        ], true);
        if ($remote_token === '') return app('json')->success('登录失败', ['get_remote_login_url' => sys_config('get_remote_login_url')]);
        return app('json')->success('登录成功', $this->services->remoteRegister($remote_token));
    }
}
