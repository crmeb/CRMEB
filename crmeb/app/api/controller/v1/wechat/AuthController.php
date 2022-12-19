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
use app\services\activity\live\LiveRoomServices;
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
                return app('json')->success(410022, $token);
            } else {
                return app('json')->success(410001, [
                    'userInfo' => $token['userInfo']
                ]);
            }
        } else
            return app('json')->fail(410019);
    }

    /**
     * 获取授权logo
     * @return mixed
     */
    public function get_logo()
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
        $this->services->notify();
    }

    /**
     * 获取小程序订阅消息id
     * @return mixed
     */
    public function temp_ids()
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
}
