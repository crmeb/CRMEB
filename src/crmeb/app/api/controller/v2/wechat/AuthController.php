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

namespace app\api\controller\v2\wechat;

use app\Request;
use app\services\wechat\RoutineServices;
use crmeb\jobs\TaskJob;
use crmeb\services\CacheService;
use crmeb\utils\Queue;


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
     * 小程序授权登录
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
                return app('json')->successful('授权成功，请绑定手机号', $token);
            } else {
                return app('json')->successful('登录成功！', ['token' => $token['token'], 'userInfo' => $token['userInfo'], 'expires_time' => $token['params']['exp']]);
            }
        } else
            return app('json')->fail('获取用户访问token失败!');
    }

    /**
     * 静默授权
     * @param $code
     * @param $spread
     * @return mixed
     */
    public function silenceAuth($code, $spread_code, $spread_spid)
    {
        $token = $this->services->silenceAuth($code, $spread_code, $spread_spid);
        Queue::instance()->do('emptyYesterdayAttachment')->job(TaskJob::class)->push();
        if ($token && isset($token['key'])) {
            return app('json')->success('授权成功，请绑定手机号', $token);
        } else if ($token) {
            return app('json')->success('登录成功', ['token' => $token['token'], 'expires_time' => $token['params']['exp']]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 静默授权 不登录
     * @param $code
     * @param $spread
     * @return mixed
     */
    public function silenceAuthNoLogin($code, $spread_code, $spread_spid)
    {
        $token = $this->services->silenceAuthNoLogin($code, $spread_code, $spread_spid);
        Queue::instance()->do('emptyYesterdayAttachment')->job(TaskJob::class)->push();
        if ($token && isset($token['auth_login'])) {
            return app('json')->success('授权成功');
        } else if ($token) {
            return app('json')->success('登录成功', ['token' => $token['token'], 'userInfo' => $token['userInfo'], 'expires_time' => $token['params']['exp']]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 静默授权
     * @param $code
     * @param $spread
     * @return mixed
     */
    public function silenceAuthBindingPhone($code, $spread_code = '', $spread_spid = '', $phone, $captcha)
    {
        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            CacheService::delete('code_' . $phone);
            return app('json')->fail('验证码错误');
        }
        CacheService::delete('code_' . $phone);
        $token = $this->services->silenceAuthBindingPhone($code, $spread_code, $spread_spid, $phone);
        Queue::instance()->do('emptyYesterdayAttachment')->job(TaskJob::class)->push();
        if ($token) {
            return app('json')->success('登录成功', ['token' => $token['token'], 'expires_time' => $token['params']['exp']]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 授权获取小程序用户手机号 直接绑定
     * @param $code
     * @param $iv
     * @param $encryptedData
     * @return mixed
     */
    public function authBindingPhone($code, $iv, $encryptedData, $spread_code, $spread_spid, $key = '')
    {
        if (!$code || !$iv || !$encryptedData)
            return app('json')->fail('参数有误');
        $token = $this->services->authBindingPhone($code, $iv, $encryptedData, $spread_code, $spread_spid, $key);
        if ($token) {
            return app('json')->success('登录成功', $token);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     *  更新用户信息
     * @param $userInfo
     * @return mixed
     */
    public function updateInfo(Request $request, $userInfo)
    {
        if (!$userInfo) {
            return app('json')->fail('参数有误');
        }
        $uid = (int)$request->uid();
        $re = $this->services->updateUserInfo($uid, $userInfo);
        if ($re) {
            return app('json')->success('更新成功');
        } else
            return app('json')->fail('更新失败');
    }
}
