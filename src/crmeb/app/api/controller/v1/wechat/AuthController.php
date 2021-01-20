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

namespace app\api\controller\v1\wechat;


use app\Request;
use app\services\live\LiveRoomServices;
use app\services\wechat\RoutineServices;

/**
 * 小程序相关
 * Class AuthController
 * @package app\api\controller\wechat
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
    public function mp_auth(Request $request)
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
                return app('json')->successful('授权成功，请绑定手机号', $token);
            } else {
                return app('json')->successful('登录成功！', [
                    'userInfo' => $token['userInfo']
                ]);
            }
        } else
            return app('json')->fail('获取用户访问token失败!');
    }

    /**
     * 获取授权logo
     * @param Request $request
     * @return mixed
     */
    public function get_logo()
    {
        $logo = sys_config('wap_login_logo');
        if (strstr($logo, 'http') === false && $logo) $logo = sys_config('site_url') . $logo;
        return app('json')->successful(['logo_url' => str_replace('\\', '/', $logo)]);
    }

    /**
     * 小程序支付回调
     */
    public function notify()
    {
        $this->services->notify();
    }

    /**
     * 获取小程序订阅消息id
     * @return mixed
     */
    public function teml_ids()
    {
        return app('json')->success($this->services->temlIds());
    }

    /**
     * 获取小程序直播列表
     * @param Request $request
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
}
