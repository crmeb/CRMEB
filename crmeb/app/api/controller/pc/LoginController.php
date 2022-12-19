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

namespace app\api\controller\pc;

use app\services\pc\LoginServices;
use crmeb\services\CacheService;

class LoginController
{
    protected $services;

    public function __construct(LoginServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取扫码登陆KEY
     * @return mixed
     */
    public function getLoginKey()
    {
        $key = md5(time() . uniqid());
        $time = time() + 600;
        CacheService::set($key, 1, 600);
        return app('json')->success(['key' => $key, 'time' => $time]);
    }

    /**
     * 扫码登陆
     * @param string $key
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function scanLogin(string $key)
    {
        return app('json')->success($this->services->scanLogin($key));
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
        return app('json')->success($this->services->wechatAuth());
    }

    /**
     * 获取公众平台id
     * @return mixed
     */
    public function getAppid()
    {
        return app('json')->success([
            'appid' => sys_config('wechat_open_app_id'),
            'version' => get_crmeb_version()
        ]);
    }
}
