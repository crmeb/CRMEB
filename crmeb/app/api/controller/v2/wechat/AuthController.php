<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
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
     * 返回用户信息的缓存key，返回是否强制绑定手机号
     * @param $code
     * @param string $spread_code
     * @param string $spread_spid
     * @return \think\Response
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/12
     */
    public function authType($code, $spread_code = '', $spread_spid = '')
    {
        $data = $this->services->authType($code, $spread_code, $spread_spid);
        return app('json')->success($data);
    }

    /**
     * 根据缓存获取token
     * @param $key
     * @return \think\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/12
     */
    public function authLogin($key)
    {
        $data = $this->services->authLogin($key);
        return app('json')->success($data);
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
        $data = $this->services->authBindingPhone($code, $iv, $encryptedData, $spread_code, $spread_spid, $key);
        if ($data) {
            return app('json')->success(410001, $data);
        } else
            return app('json')->fail(410019);
    }

    /**
     * 小程序手机号登录
     * @param string $key
     * @param string $phone
     * @param string $captcha
     * @param string $spread_code
     * @param string $spread_spid
     * @param string $code
     * @return \think\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/12
     */
    public function phoneLogin($key = '', $phone = '', $captcha = '', $spread_code = '', $spread_spid = '', $code = '')
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
        $data = $this->services->phoneLogin($key, $phone, $spread_code, 0, $spread_spid, $code);
        return app('json')->success($data);
    }

    /**
     * 小程序绑定手机号
     * @param string $code
     * @param string $iv
     * @param string $encryptedData
     * @return \think\Response
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/24
     */
    public function bindingPhone($code = '', $iv = '', $encryptedData = '')
    {
        if (!$code || !$iv || !$encryptedData) return app('json')->fail(100100);
        $this->services->bindingPhone($code, $iv, $encryptedData);
        return app('json')->success(410016);
    }
}
