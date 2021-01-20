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

namespace app\adminapi\controller\v1\serve;


use app\adminapi\controller\AuthController;
use app\adminapi\validate\serve\ServeValidata;
use app\Request;
use app\services\message\sms\SmsAdminServices;
use crmeb\services\CacheService;
use app\services\serve\ServeServices;
use think\facade\App;

/**
 * 服务登录
 * Class Login
 * @package app\adminapi\controller\v1\serve
 */
class Login extends AuthController
{

    public function __construct(App $app, ServeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 发送验证码
     * @param string $phone
     * @return mixed
     */
    public function captcha(string $phone)
    {
        validate(ServeValidata::class)->scene('phone')->check(['phone' => $phone]);
        return app('json')->success('发送成功', $this->services->user()->code($phone));
    }

    /**
     * 注册服务
     * @param Request $request
     * @param SmsAdminServices $services
     * @return mixed
     */
    public function register(Request $request, SmsAdminServices $services)
    {
        $data = $request->postMore([
            ['phone', ''],
            ['account', ''],
            ['password', ''],
            ['verify_code', ''],
        ]);

        $data['account'] = $data['phone'];
        validate(ServeValidata::class)->scene('phone')->check($data);
        $data['password'] = md5($data['password']);
        $res = $this->services->user()->register($data);
        if ($res) {
            $services->updateSmsConfig($data['account'], md5($data['account'] . md5($data['password'])));
            return app('json')->success('注册成功');
        } else {
            return app('json')->fail('注册失败');
        }
    }

    /**
     * 平台登录
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login(SmsAdminServices $services)
    {
        [$account, $password] = $this->request->postMore([
            ['account', ''],
            ['password', '']
        ], true);

        validate(ServeValidata::class)->scene('login')->check(['account' => $account, 'password' => $password]);

        $password = md5($account . md5($password));

        $res = $this->services->user()->login($account, $password);
        if ($res) {
            CacheService::clear();
            CacheService::redisHandler()->set('sms_account', $account);
            $services->updateSmsConfig($account, $password);
            return app('json')->success('登录成功', $res);
        } else {
            return app('json')->fail('登录失败');
        }
    }
}
