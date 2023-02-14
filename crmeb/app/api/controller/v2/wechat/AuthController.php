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
use app\services\wechat\RoutineServices;
use crmeb\services\CacheService;


/**
 * Class AuthController
 * @package app\api\controller\v2\wechat
 */
class AuthController
{

    protected $services = NUll;

    /**
     * AuthController constructor.
     * @param RoutineServices $services
     */
    public function __construct(RoutineServices $services)
    {
        $this->services = $services;
    }

    /**
     * 静默授权
     * @param $code
     * @param string $spread_code
     * @param string $spread_spid
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function silenceAuth($code, $spread_code = '', $spread_spid = '')
    {
        $token = $this->services->silenceAuth($code, $spread_code, $spread_spid);
        if ($token && isset($token['key'])) {
            return app('json')->success(410022, $token);
        } else if ($token) {
            return app('json')->success(410001, ['token' => $token['token'], 'expires_time' => $token['params']['exp'], 'new_user' => $token['new_user']]);
        } else
            return app('json')->fail(410019);
    }

    /**
     * 授权获取小程序用户手机号 直接绑定
     * @param string $code
     * @param string $iv
     * @param string $encryptedData
     * @param string $spread_code
     * @param string $spread_spid
     * @param string $key
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function authBindingPhone($code = '', $iv = '', $encryptedData = '', $spread_code = '', $spread_spid = '', $key = '')
    {
        if (!$code || !$iv || !$encryptedData)
            return app('json')->fail(100100);
        $token = $this->services->authBindingPhone($code, $iv, $encryptedData, $spread_code, $spread_spid, $key);
        if ($token) {
            return app('json')->success(410001, $token);
        } else
            return app('json')->fail(410019);
    }

    /** 以下方法该版本暂未使用 */
    /**
     * 小程序授权登录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function auth(Request $request)
    {
        [$code, $spid, $spread, $iv, $encryptedData] = $request->postMore([
            ['code', ''],
            ['spread_spid', 0],
            ['spread_code', ''],
            ['iv', ''],
            ['encryptedData', ''],
        ], true);
        $token = $this->services->newAuth($code, $spid, $spread, $iv, $encryptedData);
        if ($token) {
            if (isset($token['key']) && $token['key']) {
                return app('json')->success(410022, $token);
            } else {
                return app('json')->success(410001, ['token' => $token['token'], 'userInfo' => $token['userInfo'], 'expires_time' => $token['params']['exp']]);
            }
        } else
            return app('json')->fail(410019);
    }

    /**
     * 静默授权 不登录
     * @param $code
     * @param string $spread_code
     * @param string $spread_spid
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function silenceAuthNoLogin($code, $spread_code = '', $spread_spid = '')
    {
        $token = $this->services->silenceAuthNoLogin($code, $spread_code, $spread_spid);
        if ($token && isset($token['auth_login'])) {
            return app('json')->success(410023);
        } else if ($token) {
            return app('json')->success(410001, ['token' => $token['token'], 'userInfo' => $token['userInfo'], 'expires_time' => $token['params']['exp']]);
        } else
            return app('json')->fail(410019);
    }

    /**
     * 静默授权
     * @param string $code
     * @param string $spread_code
     * @param string $spread_spid
     * @param string $phone
     * @param string $captcha
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function silenceAuthBindingPhone($code = '', $spread_code = '', $spread_spid = '', $phone = '', $captcha = '')
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
        $token = $this->services->silenceAuthBindingPhone($code, $spread_code, $spread_spid, $phone);
        if ($token) {
            return app('json')->success(410001, ['token' => $token['token'], 'expires_time' => $token['params']['exp'], 'new_user' => $token['new_user']]);
        } else
            return app('json')->fail(410019);
    }

    /**
     * 更新用户信息
     * @param Request $request
     * @param $userInfo
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateInfo(Request $request, $userInfo)
    {
        if (!$userInfo) {
            return app('json')->fail(100100);
        }
        $uid = (int)$request->uid();
        $re = $this->services->updateUserInfo($uid, $userInfo);
        if ($re) {
            return app('json')->success(100012);
        } else
            return app('json')->fail(100013);
    }
}
