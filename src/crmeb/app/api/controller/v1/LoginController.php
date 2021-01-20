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

namespace app\api\controller\v1;


use app\Request;
use crmeb\utils\Queue;
use think\facade\Cache;
use crmeb\jobs\TaskJob;
use think\facade\Config;
use crmeb\services\CacheService;
use app\services\user\LoginServices;
use think\exception\ValidateException;
use app\api\validate\user\RegisterValidates;
use app\services\message\sms\SmsSendServices;

/**微信小程序授权类
 * Class AuthController
 * @package app\api\controller
 */
class LoginController
{
    protected $services = NUll;

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
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
     /** 
     * @OA\Info(title="CRMEB API",version="2.0")
     * @OA\Post(
     *     path="/login",
     *     tags={"User"},
     *     summary="用户登录",
     *     description="用户登录接口",
     *     @OA\Parameter(name="account", in="query", @OA\Schema(type="string"), required=true, description="用户帐号"),
     *     @OA\Parameter(name="pwd", in="query", @OA\Schema(type="string"), required=true, description="用户密码"),
     *     @OA\Parameter(name="spread", in="query", @OA\Schema(type="string"), required=true, description="验证码"),
     *     @OA\Response(response="200",description="An example resource")
     * )
     */
    public function login(Request $request)
    {
        [$account, $password, $spread] = $request->postMore([
            'account', 'password', 'spread'
        ], true);
        Queue::instance()->do('emptyYesterdayAttachment')->job(TaskJob::class)->push();
        if (!$account || !$password) {
            return app('json')->fail('请输入账号和密码');
        }
        return app('json')->success('登录成功', $this->services->login($account, $password, $spread));
    }

    /**
     * 退出登录
     * @param Request $request
     */
    public function logout(Request $request)
    {
        $key = trim(ltrim($request->header(Config::get('cookie.token_name')), 'Bearer'));
        CacheService::redisHandler()->delete($key);
        return app('json')->success('成功');
    }

    public function verifyCode()
    {
        $unique = password_hash(uniqid(true), PASSWORD_BCRYPT);
        Cache::set('sms.key.' . $unique, 0, 300);
        $time = sys_config('verify_expire_time', 1);
        return app('json')->success(['key' => $unique, 'expire_time' => $time]);
    }

    public function captcha(Request $request)
    {
        ob_clean();
        $rep = captcha();
        $key = app('session')->get('captcha.key');
        $uni = $request->get('key');
        if ($uni)
            Cache::set('sms.key.cap.' . $uni, $key, 300);

        return $rep;
    }

    /**
     * 验证验证码是否正确
     *
     * @param $uni
     * @param string $code
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function checkCaptcha($uni, string $code): bool
    {
        $cacheName = 'sms.key.cap.' . $uni;
        if (!Cache::has($cacheName)) {
            return false;
        }

        $key = Cache::get($cacheName);

        $code = mb_strtolower($code, 'UTF-8');

        $res = password_verify($code, $key);

        if ($res) {
            Cache::delete($cacheName);
        }

        return $res;
    }

    /**
     * 验证码发送
     * @param Request $request
     * @return mixed
     */
    public function verify(Request $request, SmsSendServices $services)
    {
        [$phone, $type, $key, $code] = $request->postMore([['phone', 0], ['type', ''], ['key', ''], ['code', '']], true);

        $keyName = 'sms.key.' . $key;
        $nowKey = 'sms.' . date('YmdHi');

        if (!Cache::has($keyName))
            return app('json')->make(401, '发送验证码失败,请刷新页面重新获取');

        if (($num = Cache::get($keyName)) > 2) {
            if (!$code)
                return app('json')->make(402, '请输入验证码');

            if (!$this->checkCaptcha($key, $code))
                return app('json')->fail('验证码输入有误');
        }

        $total = 1;
        if ($has = Cache::has($nowKey)) {
            $total = Cache::get($nowKey);
            if ($total > Config::get('sms.maxMinuteCount', 20))
                return app('json')->success('已发送');
        }

        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $time = sys_config('verify_expire_time', 1);
        $smsCode = $this->services->verify($services, $phone, $type, $time, app()->request->ip());
        if ($smsCode) {
            CacheService::set('code_' . $phone, $smsCode, $time * 60);
            Cache::set($keyName, $num + 1, 300);
            Cache::set($nowKey, $total, 61);
            return app('json')->success('发送成功');
        } else {
            return app('json')->fail('发送失败');
        }

    }

    /**
     * H5注册新用户
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        [$account, $captcha, $password, $spread] = $request->postMore([['account', ''], ['captcha', ''], ['password', ''], ['spread', 0]], true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 16)
            return app('json')->fail('密码必须是在6到16位之间');
        if ($password == '123456') return app('json')->fail('密码太过简单，请输入较为复杂的密码');

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
     */
    public function reset(Request $request)
    {
        [$account, $captcha, $password] = $request->postMore([['account', ''], ['captcha', ''], ['password', '']], true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            return app('json')->fail('验证码错误');
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 16)
            return app('json')->fail('密码必须是在6到16位之间');
        if ($password == '123456') return app('json')->fail('密码太过简单，请输入较为复杂的密码');
        $resetStatus = $this->services->reset($account, $password);
        if ($resetStatus) return app('json')->success('修改成功');
        return app('json')->fail('修改失败');
    }

    /**
     * 手机号登录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mobile(Request $request)
    {
        [$phone, $captcha, $spread] = $request->postMore([['phone', ''], ['captcha', ''], ['spread', 0]], true);

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
            CacheService::delete('code_' . $phone);
            return app('json')->fail('验证码错误');
        }
        $user_type = $request->getFromType() ? $request->getFromType() : 'h5';
        $token = $this->services->mobile($phone, $spread, $user_type);
        if ($token) {
            CacheService::delete('code_' . $phone);
            return app('json')->success('登录成功', $token);
        } else {
            return app('json')->fail('登录失败');
        }
    }

    /**
     * H5切换登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function switch_h5(Request $request)
    {
        $from = $request->post('from', 'wechat');
        $user = $request->user();
        $token = $this->services->switchAccount($user, $from);
        if ($token) {
            $token['userInfo'] = $user;
            return app('json')->success('登录成功', $token);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function binding_phone(Request $request)
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
            CacheService::delete('code_' . $phone);
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
    public function user_binding_phone(Request $request)
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

    public function update_binding_phone(Request $request)
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
            return app('json')->fail('登录CODE不存在');
        }
        $cacheCode = CacheService::get($code);
        if ($cacheCode === false || $cacheCode === null) {
            return app('json')->fail('二维码已过期请重新扫描');
        }
        CacheService::set($code, '0', 600);
        return app('json')->success();
    }
}
