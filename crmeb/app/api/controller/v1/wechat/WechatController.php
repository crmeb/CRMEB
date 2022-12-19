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

namespace app\api\controller\v1\wechat;


use app\Request;
use app\services\wechat\WechatServices as WechatAuthServices;
use crmeb\services\CacheService;

/**
 * 微信公众号
 * Class WechatController
 * @package app\api\controller\wechat
 */
class WechatController
{
    protected $services = NUll;

    /**
     * WechatController constructor.
     * @param WechatAuthServices $services
     */
    public function __construct(WechatAuthServices $services)
    {
        $this->services = $services;
    }

    /**
     * 微信公众号服务
     * @return \think\Response
     */
    public function serve()
    {
        return $this->services->serve();
    }

    /**
     * 支付异步回调
     */
    public function notify()
    {
        return $this->services->notify();
    }

    public function v3notify()
    {
        return $this->services->v3notify();
    }

    /**
     * 公众号权限配置信息获取
     * @param Request $request
     * @return mixed
     */
    public function config(Request $request)
    {
        return app('json')->success($this->services->config($request->get('url')));
    }

    /**
     * 公众号授权登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function auth(Request $request)
    {
        [$spreadId, $login_type] = $request->getMore([
            [['spread', 'd'], 0],
            ['login_type', ''],
        ], true);
        $token = $this->services->auth($spreadId, $login_type);
        if ($token && isset($token['key'])) {
            return app('json')->success(410022, $token);
        } else if ($token) {
            return app('json')->success(410001, ['userInfo' => $token['userInfo']]);
        } else
            return app('json')->fail(410019);
    }

    /**
     * App微信登陆
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function appAuth(Request $request)
    {
        [$userInfo, $phone, $captcha] = $request->postMore([
            ['userInfo', []],
            ['phone', ''],
            ['code', '']
        ], true);
        if ($phone) {
            if (!$captcha) {
                return app('json')->fail(410004);
            }
            //验证验证码
            $verifyCode = CacheService::get('code_' . $phone);
            if (!$verifyCode)
                return app('json')->fail(410009);
            $verifyCode = substr($verifyCode, 0, 6);
            if ($verifyCode != $captcha) {
                CacheService::delete('code_' . $phone);
                return app('json')->fail(410010);
            }
        }
        $token = $this->services->appAuth($userInfo, $phone);
        if ($token) {
            return app('json')->success(410001, $token);
        } else if ($token === false) {
            return app('json')->success(410001, ['isbind' => true]);
        } else {
            return app('json')->fail(410019);
        }
    }

    /**
     * 关注二维码
     * @return mixed
     * @throws \Exception
     */
    public function follow()
    {
        $data = $this->services->follow();
        if ($data) {
            return app('json')->success($data);
        } else {
            return app('json')->fail(100016);
        }

    }
}
