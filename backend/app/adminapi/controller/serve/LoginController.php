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

namespace app\adminapi\controller\serve;


use app\adminapi\controller\AuthController;
use app\adminapi\validate\serve\ServeValidata;
use app\Request;
use crmeb\services\CacheService;
use app\services\system\config\SystemConfigServices;
use crmeb\services\yihaotong\Serve;
use think\facade\App;

/**
 * 服务登录
 * Class Login
 * @package app\adminapi\controller\serve
 */
class LoginController extends AuthController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 发送验证码
     * @param string $phone
     * @return mixed
     */
    public function captcha(string $phone)
    {
        $this->validate(['phone' => $phone], ServeValidata::class, 'phone');
        return app('json')->success('发送成功', app()->make(Serve::class, [yihaotong_config()])->code($phone));
    }

    /**
     * 验证验证码
     * @param string $phone
     * @param $code
     * @return mixed
     */
    public function checkCode()
    {
        [$phone, $verify_code] = $this->request->postMore([
            ['phone', ''],
            ['verify_code', ''],
        ], true);
        $this->validate(['phone' => $phone], ServeValidata::class, 'phone');
        return app('json')->success('success', app()->make(Serve::class, [yihaotong_config()])->checkCode($phone, $verify_code));
    }

    /**
     * 注册服务
     * @param Request $request
     * @param SystemConfigServices $configServices
     * @return mixed
     */
    public function register(Request $request, SystemConfigServices $configServices)
    {
        $data = $request->postMore([
            ['phone', ''],
            ['account', ''],
            ['password', ''],
            ['verify_code', ''],
        ]);

        $data['account'] = $data['phone'];
        $this->validate($data, ServeValidata::class);
        $data['password'] = md5($data['password']);
        $res = app()->make(Serve::class, [yihaotong_config()])->register($data);
        if ($res) {
            $configServices->setConfig('yihaotong_appid', $data['account']);
            $configServices->setConfig('yihaotong_appsecret', md5($data['account'] . md5($data['password'])));
            CacheService::clear();
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
    public function login(SystemConfigServices $configServices)
    {
        [$account, $password] = $this->request->postMore([
            ['account', ''],
            ['password', '']
        ], true);

        $this->validate(['account' => $account, 'password' => $password], ServeValidata::class, 'login');

        $password = md5($account . md5($password));

        $res = app()->make(Serve::class, [yihaotong_config()])->login($account, $password);
        if ($res) {
            // 先持久化密钥配置，成功后再写入登录态缓存，避免配置未落库而前端显示已登录
            $configServices->setConfig('yihaotong_appid', $account);
            $configServices->setConfig('yihaotong_appsecret', $password);
            CacheService::clear();
            CacheService::set('yihaotong_appid', $account);
            return app('json')->success('登录成功', $res);
        } else {
            return app('json')->fail('登录失败');
        }
    }
}
