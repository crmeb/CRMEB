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
namespace app\adminapi\controller;

use think\facade\App;
use crmeb\utils\Captcha;
use crmeb\basic\BaseController;
use app\services\system\admin\SystemAdminServices;

/**
 * 后台登陆
 * Class Login
 * @package app\adminapi\controller
 */
class Login extends BaseController
{

    /**
     * Login constructor.
     * @param App $app
     * @param SystemAdminServices $services
     */
    public function __construct(App $app, SystemAdminServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }

    /**
     * 验证码
     * @return $this|\think\Response
     */
    public function captcha()
    {
        return app()->make(Captcha::class)->create();
    }

    /**
     * 登陆
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login()
    {
        [$account, $password, $imgcode] = $this->request->postMore([
            'account', 'pwd', ['imgcode', '']
        ], true);

        if (!app()->make(Captcha::class)->check($imgcode)) {
            return app('json')->fail('验证码错误，请重新输入');
        }

        validate(\app\adminapi\validate\setting\SystemAdminValidata::class)->scene('get')->check(['account' => $account, 'pwd' => $password]);
        return app('json')->success($this->services->login($account, $password, 'admin'));
    }

    /**
     * 获取后台登录页轮播图以及LOGO
     * @return mixed
     */
    public function info()
    {
        return app('json')->success($this->services->getLoginInfo());
    }
}
