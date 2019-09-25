<?php


namespace app\api\controller;


use app\http\validates\user\RegisterValidates;
use app\models\user\User;
use app\models\user\UserToken;
use app\models\user\WechatUser;
use app\Request;
use crmeb\jobs\TestJob;
use crmeb\services\CacheService;
use crmeb\services\SMSService;
use crmeb\services\UtilService;
use think\exception\ValidateException;
use think\facade\Queue;
use think\facade\Session;

/**微信小程序授权类
 * Class AuthController
 * @package app\api\controller
 */

class AuthController
{
    public function login(Request $request)
    {

        $user = User::where('account', $request->param('account'))->find();
        if($user) {
            if ($user->pwd !== md5($request->param('password')))
                return app('json')->fail('账号或密码错误');
            if ($user->pwd === md5(123456))
                return app('json')->fail('请修改您的初始密码，再尝试登陆！');
        }else{
            return app('json')->fail('账号或密码错误');
        }
        if (!$user['status'])
            return app('json')->fail('已被禁止，请联系管理员');


        // 设置推广关系
        User::setSpread(intval($request->param('spread')), $user->uid);

        $token = UserToken::createToken($user, 'user');

        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', ['token' => $token->token, 'expires_time' => $token->expires_time]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 退出登录
     * @param Request $request
     */
    public function logout(Request $request)
    {
        $request->tokenData()->delete();
        return app('json')->success('成功');
    }

    public function test()
    {
        echo 'test';
    }

    /**
     * 验证码发送
     * @param Request $request
     * @return mixed
     */
    public function verify(Request $request)
    {
        list($phone, $type) = UtilService::postMore([['phone',0],['type','']],$request, true);
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone'=>$phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if(User::checkPhone($phone) && $type == 'register') return app('json')->fail('手机号已注册');
        if(!User::checkPhone($phone) && $type == 'login') return app('json')->fail('账号不存在！');
        $time = 60;
        if(CacheService::get('code_'.$phone))
            return app('json')->fail($time.'秒内有效');
        $code = rand(100000,999999);
        $data['code'] = $code;
        $res = SMSService::send($phone,SMSService::VERIFICATION_CODE,$data);
        if($res['status'] == 400) return app('json')->fail('短信平台验证码发送失败'.$res['msg']);
        CacheService::set('code_'.$phone, $code, $time);
        return app('json')->success($res['msg'] ?? '发送失败');
    }

    /**
     * H5注册新用户
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        list($account, $captcha, $password, $spread) = UtilService::postMore([['account',''], ['captcha',''], ['password',''], ['spread',0]],$request, true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account'=>$account, 'captcha'=>$captcha, 'password'=>$password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $verifyCode = CacheService::get('code_'.$account);
        if(!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if($verifyCode != $captcha)
            return app('json')->fail('验证码错误');
        if(strlen(trim($password)) < 6 || strlen(trim($password)) > 16)
            return app('json')->fail('密码必须是在6到16位之间');
        $registerStatus = User::register($account, $password, $spread);
        if($registerStatus) return app('json')->success('注册成功');
        return app('json')->fail(User::getErrorInfo('注册失败'));
    }

    /**
     * 密码修改
     * @param Request $request
     * @return mixed
     */
    public function reset(Request $request)
    {
        list($account, $captcha, $password) = UtilService::postMore([['account',''], ['captcha',''], ['password','']],$request, true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account'=>$account, 'captcha'=>$captcha, 'password'=>$password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $verifyCode = CacheService::get('code_'.$account);
        if(!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if($verifyCode != $captcha)
            return app('json')->fail('验证码错误');
        if(strlen(trim($password)) < 6 || strlen(trim($password)) > 16)
            return app('json')->fail('密码必须是在6到16位之间');
        $resetStatus = User::reset($account, $password);
        if($resetStatus) return app('json')->success('修改成功');
        return app('json')->fail(User::getErrorInfo('修改失败'));
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
        list($phone, $captcha, $spread) = UtilService::postMore([['phone',''], ['captcha',''], ['spread',0]],$request, true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone'=>$phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        //验证验证码
        $verifyCode = CacheService::get('code_'.$phone);
        if(!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if($verifyCode != $captcha)
            return app('json')->fail('验证码错误');

        //数据库查询
        $user = User::where('account', $phone)->find();
        if (!$user)
            return app('json')->fail('用户不存在');
        if (!$user->status)
            return app('json')->fail('已被禁止，请联系管理员');

        // 设置推广关系
        User::setSpread($spread, $user->uid);

        $token = UserToken::createToken($user, 'user');

        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', ['token' => $token->token, 'expires_time' => $token->expires_time]);
        } else
            return app('json')->fail('登录失败');
    }

    /*
     * H5切换登陆
     *
     * */
    public function switch_h5(Request $request){
        $from = $request->post('from','wechat');
        $user = $request->user();
        if($from === 'h5'){
            $user = User::where('phone', $user['phone'])->where('user_type','<>','h5')->find();
            $user->login_type = 'wechat';
            $user->save();
        }else {
            //数据库查询
            $user = User::where('account|phone', $user['phone'])->where('user_type', 'h5')->find();
            if (!$user)
                return app('json')->fail('H5用户不存在,无法切换');

            if (!$user->status) return app('json')->fail('已被禁止，请联系管理员');

            $wechatUserInfo   = WechatUser::where('uid', $request->uid())->find();//当前登陆用户信息
            $wechatH5UserInfo = WechatUser::where('uid', $user->uid)->find();//H5登陆切换用户信息

            if ($wechatH5UserInfo->unionid && $wechatUserInfo->unionid != $wechatH5UserInfo->unionid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');
            if ($wechatH5UserInfo->openid && $wechatUserInfo->openid != $wechatH5UserInfo->openid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');
            if ($wechatH5UserInfo->routine_openid && $wechatUserInfo->routine_openid != $wechatH5UserInfo->routine_openid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');

            switch ($from) {
                case 'wechat':
                    if (!$wechatH5UserInfo->openid)
                        $wechatH5UserInfo->openid = $wechatUserInfo->openid;
                    if (!$wechatH5UserInfo->unionid && $wechatUserInfo->unionid)
                        $wechatH5UserInfo->unionid = $wechatUserInfo->unionid;
                    break;
                case 'routine':
                    if (!$wechatH5UserInfo->routine_openid)
                        $wechatH5UserInfo->routine_openid = $wechatUserInfo->routine_openid;
                    if (!$wechatH5UserInfo->unionid && $wechatUserInfo->unionid)
                        $wechatH5UserInfo->unionid = $wechatUserInfo->unionid;
                    break;
            }
            $wechatH5UserInfo->save();
            User::where('uid', $request->uid())->update(['login_type' => 'h5']);
        }
        $token = UserToken::createToken($user, 'user');
        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', ['userInfo'=>$user,'token' => $token->token, 'expires_time' => $token->expires_time,'time'=>strtotime($token->expires_time)]);
        } else
            return app('json')->fail('登录失败');
    }


    /*
     * 绑定手机号
     *
     * */
    public function binding_phone(Request $request){
        list($phone,$captcha,$step) = UtilService::postMore([
            ['phone',''],
            ['captcha',''],
            ['step',0]
        ],$request,true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone'=>$phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        //验证验证码
        $verifyCode = CacheService::get('code_'.$phone);
        if(!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if($verifyCode != $captcha)
            return app('json')->fail('验证码错误');

        $userInfo = User::where('uid',$request->uid())->find();
        $userPhone = $userInfo->phone;
        if(!$userInfo) return app('json')->fail('用户不存在');
        if($userInfo->phone) return app('json')->fail('您的账号已经绑定过手机号码！');
        if(User::where('phone',$phone)->where('user_type','<>','h5')->count())
            return app('json')->success('此手机已经绑定，无法多次绑定！');
        if(User::where('account',$phone)->where('phone',$phone)->where('user_type','h5')->find()){
            if(!$step) return app('json')->success('H5已有账号是否绑定此账号上',['is_bind'=>1]);
            $userInfo->phone = $phone;
        }else{
            $userInfo->account = $phone;
            $userInfo->phone = $phone;
        }
        if($userInfo->save() || $userPhone == $phone)
            return app('json')->success('绑定成功');
        else
            return app('json')->fail('绑定失败');
    }
}