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

namespace modules\channel\wechat_mini\controller;


use app\Request;
use modules\channel\wechat_mini\service\LiveRoomServices;
use modules\channel\wechat_mini\service\RoutineServices;
use crmeb\services\CacheService;

/**
 * 小程序相关（旧入口）
 *
 * @deprecated 该控制器为小程序渠道历史遗留的认证入口，仅保留向后兼容。
 *             新业务请统一使用 app/api/controller/v1/AuthController（POST /api/auth/login）：
 *               - 两步式授权（第一步）：    channel=wechat_mini, login_type=wechat_code_init
 *               - 两步式授权（第二步）：    channel=wechat_mini, login_type=wechat_code
 *               - 授权+绑定手机号一步到位：channel=wechat_mini, login_type=wechat_code_with_phone
 *               - 手机号登录：              channel=wechat_mini, login_type=mobile
 *             旧入口内部已转发到统一逻辑：RoutineServices -> LoginServices::createToken 时触发 UserLogin 事件
 *             非认证相关方法（getLogo/notify/tempIds/live/livePlaybacks）保持原状，由对应模块维护
 * Class AuthController
 * @package modules\channel\wechat_mini\controller
 */
class AuthController
{
    /**
     * @var RoutineServices
     */
    protected $services;

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
     *
     * @deprecated v1 旧版授权入口，请使用 v2 接口或统一认证接口 POST /api/auth/login
     *             （channel=wechat_mini, login_type=wechat_code 或 wechat_code_with_phone）
     *             旧入口内部已转发到统一逻辑：RoutineServices -> LoginServices::createToken 时触发 UserLogin 事件
     * @param Request $request
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/24
     */
    public function mpAuth(Request $request)
    {
        [$code, $cache_key, $login_type, $spread_spid, $spread_code, $iv, $encryptedData] = $request->postMore([
            ['code', ''],
            ['cache_key', ''],
            ['login_type', ''],
            ['spread_spid', 0],
            ['spread_code', ''],
            ['iv', ''],
            ['encryptedData', ''],
        ], true);
        $token = $this->services->mp_auth($code, $cache_key, $login_type, $spread_spid, $spread_code, $iv, $encryptedData);
        if ($token) {
            if (isset($token['key']) && $token['key']) {
                return app('json')->success('授权成功，请绑定手机号', $token);
            } else {
                return app('json')->success('登录成功', [
                    'userInfo' => $token['userInfo']
                ]);
            }
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 获取授权logo
     * @return mixed
     */
    public function getLogo()
    {
        $logo = sys_config('wap_login_logo');
        if (strstr($logo, 'http') === false && $logo) $logo = sys_config('site_url') . $logo;
        return app('json')->success(['logo_url' => str_replace('\\', '/', $logo)]);
    }

    /**
     * 小程序支付回调
     */
    public function notify()
    {
        return $this->services->notify();
    }

    /**
     * 获取小程序订阅消息id
     * @return mixed
     */
    public function tempIds()
    {
        return app('json')->success($this->services->tempIds());
    }

    /**
     * 获取小程序直播列表
     * @param Request $request
     * @param LiveRoomServices $liveRoom
     * @return mixed
     */
    public function live(Request $request, LiveRoomServices $liveRoom)
    {
        return app('json')->success($liveRoom->userList([]));
    }

    /**
     * 获取直播回放
     * @param $id
     * @param LiveRoomServices $lvieRoom
     * @return mixed
     */
    public function livePlaybacks($id, LiveRoomServices $lvieRoom)
    {
        return app('json')->success($lvieRoom->getPlaybacks((int)$id));
    }

    /**
     * 返回用户信息的缓存key，返回是否强制绑定手机号(v2)
     *
     * @deprecated 请使用 POST /api/auth/login（channel=wechat_mini, login_type=wechat_code_init）
     *             旧入口内部已转发到统一逻辑：RoutineServices::authType 返回 bind_key 给前端
     * @param $code
     * @param string $spread_code
     * @param string $spread_spid
     * @return \think\Response
     */
    public function authType($code, $spread_code = '', $spread_spid = '')
    {
        $data = $this->services->authType($code, $spread_code, $spread_spid);
        return app('json')->success($data);
    }

    /**
     * 根据缓存获取token(v2)
     *
     * @deprecated 请使用 POST /api/auth/login（channel=wechat_mini, login_type=wechat_code）
     *             旧入口内部已转发到统一逻辑：RoutineServices::authLogin -> LoginServices::createToken 时触发 UserLogin 事件
     * @param $key
     * @return \think\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function authLogin($key)
    {
        $data = $this->services->authLogin($key);
        return app('json')->success($data);
    }

    /**
     * 授权获取小程序用户手机号 直接绑定(v2)
     *
     * @deprecated 请使用 POST /api/auth/login（channel=wechat_mini, login_type=wechat_code_with_phone）
     *             旧入口内部已转发到统一逻辑：RoutineServices::authBindingPhone -> LoginServices::createToken 时触发 UserLogin 事件
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
            return app('json')->fail('参数错误');
        $data = $this->services->authBindingPhone($code, $iv, $encryptedData, $spread_code, $spread_spid, $key);
        if ($data) {
            return app('json')->success('登录成功', $data);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 小程序手机号登录(v2)
     *
     * @deprecated 请使用 POST /api/auth/login（channel=wechat_mini, login_type=mobile）
     *             旧入口内部已转发到统一逻辑：RoutineServices::phoneLogin -> LoginServices::createToken 时触发 UserLogin 事件
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
     */
    public function phoneLogin($key = '', $phone = '', $captcha = '', $spread_code = '', $spread_spid = '', $code = '')
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
        $data = $this->services->phoneLogin($key, $phone, $spread_code, 0, $spread_spid, $code);
        return app('json')->success($data);
    }

    /**
     * 小程序绑定手机号(v2)
     *
     * @deprecated 已登录用户绑定手机号场景，后续将统一到用户中心绑定接口
     *             旧入口内部已转发到统一逻辑：RoutineServices::bindingPhone 使用核心微信身份服务
     * @param string $code
     * @param string $iv
     * @param string $encryptedData
     * @return \think\Response
     */
    public function bindingPhone($code = '', $iv = '', $encryptedData = '')
    {
        if (!$code || !$iv || !$encryptedData) return app('json')->fail('参数错误');
        $this->services->bindingPhone($code, $iv, $encryptedData);
        return app('json')->success('绑定成功');
    }
}
