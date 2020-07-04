<?php

namespace app\admin\controller;


use app\admin\model\system\SystemAdmin;
use crmeb\services\UtilService;
use think\facade\Session;
use think\facade\Route as Url;

/**
 * 登录验证控制器
 * Class Login
 * @package app\admin\controller
 */
class Login extends SystemBasic
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 登录验证 + 验证码验证
     */
    public function verify()
    {
        if (!request()->isPost()) return $this->failed('请登陆!');
        list($account, $pwd, $verify) = UtilService::postMore([
            'account', 'pwd', 'verify'
        ], null, true);
        //检验验证码
        if (!captcha_check($verify)) return $this->failed('验证码错误，请重新输入');
        $error = Session::get('login_error') ?: ['num' => 0, 'time' => time()];
        $error['num'] = 0;
        if ($error['num'] >= 5 && $error['time'] > strtotime('- 5 minutes'))
            return $this->failed('错误次数过多,请稍候再试!');
        //检验帐号密码
        $res = SystemAdmin::login($account, $pwd);
        if ($res) {
            Session::set('login_error', null);
            Session::save();
            return $this->successful(['url' => Url::buildUrl('Index/index')->build()]);
        } else {
            $error['num'] += 1;
            $error['time'] = time();
            Session::set('login_error', $error);
            Session::save();
            return $this->failed(SystemAdmin::getErrorInfo('用户名错误，请重新输入'));
        }
    }

    public function captcha()
    {
        ob_clean();
        return captcha();
    }

    /**
     * 退出登陆
     */
    public function logout()
    {
        SystemAdmin::clearLoginInfo();
        $this->redirect(Url::buildUrl('index')->build());
    }
}