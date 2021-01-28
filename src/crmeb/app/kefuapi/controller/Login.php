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

namespace app\kefuapi\controller;


use app\Request;
use crmeb\services\CacheService;
use app\services\kefu\LoginServices;
use app\kefuapi\validate\LoginValidate;
use think\facade\App;

/**
 * Class Login
 * @package app\kefu\controller
 */
class Login extends AuthController
{
    /**
     * Login constructor.
     * @param LoginServices $services
     */
    public function __construct(App $app, LoginServices $services)
    {
        $this->app = $app;
        $this->services = $services;
    }

    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }

    /**
     * 客服登录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login(Request $request)
    {
        [$account, $password] = $request->postMore([
            ['account', ''],
            ['password', ''],
        ], true);

        validate(LoginValidate::class)->check(['account' => $account, 'password' => $password]);

        $token = $this->services->authLogin($account, $password);

        return $this->success('登录成功', $token);
    }

    /**
     * 开放平台扫码登录
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wechatAuth()
    {
        return $this->success($this->services->wechatAuth());
    }

    /**
     * 获取公众平台id
     * @return mixed
     */
    public function getAppid()
    {
        return $this->success([
            'appid' => sys_config('wechat_open_app_id', 'wxc736972a4ca1e2a1'),
            'version' => get_crmeb_version(),
            'site_name' => sys_config('site_name')
        ]);
    }

    /**
     * 获取登录唯一code
     * @return mixed
     */
    public function getLoginKey()
    {
        $key = md5(time() . uniqid());
        $time = time() + 600;
        CacheService::set($key, 1, 600);
        return $this->success(['key' => $key, 'time' => $time]);
    }

    /**
     * 验证登录
     * @param string $key
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function scanLogin(string $key)
    {
        return $this->success($this->services->scanLogin($key));
    }
}
