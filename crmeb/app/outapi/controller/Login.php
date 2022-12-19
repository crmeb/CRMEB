<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\outapi\controller;

use app\outapi\validate\LoginValidate;
use app\Request;
use think\facade\App;
use app\services\out\OutAccountServices;

/**
 * Class Login
 * @package app\out\controller
 */
class Login extends AuthController
{
    /**
     * OutAccount constructor.
     * @param App $app
     * @param OutAccountServices $services
     */
    public function __construct(App $app, OutAccountServices $services)
    {
        parent::__construct($app);
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
    public function getToken(Request $request)
    {
        [$appid, $appsecret] = $request->postMore([
            ['appid', ''],
            ['appsecret', ''],
        ], true);
        $this->validate(['appid' => $appid, 'appsecret' => $appsecret], LoginValidate::class);

        $token = $this->services->authLogin($appid, $appsecret);

        return app('json')->success(100010, $token);
    }

    /**
     * 刷新token
     * @return void
     */
    public function refreshToken(Request $request)
    {
        [$token] = $request->postMore([
            ['access_token', ''],
        ], true);
        $token = $this->services->refresh($token);
        return app('json')->success(100010, $token);
    }

}
