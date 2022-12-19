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
namespace app\api\controller\v2\wechat;

use app\Request;
use app\services\wechat\WechatServices;
use crmeb\services\CacheService;

/**
 * Class WechatController
 * @package app\api\controller\v2\wechat
 */
class WechatController
{
    protected $services = NUll;

    /**
     * WechatController constructor.
     * @param WechatServices $services
     */
    public function __construct(WechatServices $services)
    {
        $this->services = $services;
    }

    /**
     * 公众号授权登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function auth(Request $request)
    {
        [$spreadId, $login_type] = $request->getMore([
            [['spread', 'd'], 0],
            ['login_type', 'wechat'],
        ], true);
        $token = $this->services->newAuth($spreadId, $login_type);
        if ($token && isset($token['key'])) {
            return app('json')->success(410022, $token);
        } else if ($token) {
            return app('json')->success(410001, ['token' => $token['token'], 'userInfo' => $token['userInfo'], 'expires_time' => $token['params']['exp']]);
        } else
            return app('json')->fail(410019);
    }

    /**
     * 微信公众号静默授权
     * @param string $spread
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function silenceAuth($spread = '')
    {
        $token = $this->services->silenceAuth($spread);
        if ($token && isset($token['key'])) {
            return app('json')->success(410022, $token);
        } else if ($token) {
            return app('json')->success(410001, ['token' => $token['token'], 'expires_time' => $token['params']['exp']]);
        } else
            return app('json')->fail(410019);
    }

    /**
     * 微信公众号静默授权
     * @param string $spread
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function silenceAuthNoLogin($spread = '')
    {
        $token = $this->services->silenceAuthNoLogin($spread);
        if ($token && isset($token['auth_login'])) {
            return app('json')->success(410023, $token);
        } else if ($token) {
            return app('json')->success(410001, ['token' => $token['token'], 'userInfo' => $token['userInfo'], 'expires_time' => $token['params']['exp']]);
        } else
            return app('json')->fail(410019);
    }

    /**
     * 静默授权 手机号直接注册登录
     * @param string $key
     * @param string $phone
     * @param string $captcha
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function silenceAuthBindingPhone($key = '', $phone = '', $captcha = '')
    {
        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail(410009);
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            CacheService::delete('code_' . $phone);
            return app('json')->fail(410010);
        }
        CacheService::delete('code_' . $phone);
        $token = $this->services->silenceAuthBindingPhone($key, $phone);
        if ($token) {
            return app('json')->success(410001, $token);
        } else
            return app('json')->fail(410019);
    }
}
