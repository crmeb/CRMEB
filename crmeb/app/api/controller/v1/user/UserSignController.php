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
namespace app\api\controller\v1\user;

use app\Request;
use app\services\user\UserSignServices;

/**
 * 用户签到
 * Class UserController
 * @package app\api\controller\v1\user
 */
class UserSignController
{
    protected $services = NUll;

    /**
     * UserController constructor.
     * @param UserSignServices $services
     */
    public function __construct(UserSignServices $services)
    {
        $this->services = $services;
    }

    /**
     * 签到 配置
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sign_config(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->signConfig($uid));
    }

    /**
     * 签到 列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sign_list(Request $request)
    {
        list($page, $limit) = $request->getMore([
            ['page', 0],
            ['limit', 0]
        ], true);
        if (!$limit) return app('json')->success([]);
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getUserSignList($uid));
    }

    /**
     * 签到
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sign_integral(Request $request)
    {
        if (sys_config('sign_status') == 0) {
            return app('json')->fail('签到功能未开启');
        }
        $uid = (int)$request->uid();
        $integral = $this->services->sign($uid);
        return app('json')->success(410127, ['integral' => $integral], ['integral' => $integral]);
    }

    /**
     * 签到用户信息
     * @param Request $request
     * @return mixed
     */
    public function sign_user(Request $request)
    {
        list($sign, $integral, $all) = $request->postMore([
            ['sign', 0],
            ['integral', 0],
            ['all', 0],
        ], true);
        $uid = (int)$request->uid();
        return app('json')->success($this->services->signUser($uid, $sign, $integral, $all));
    }

    /**
     * 签到列表（年月）
     * @param Request $request
     * @return mixed
     */
    public function sign_month(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getSignMonthList($uid));
    }

    /**
     * 用户设置签到提醒
     * @param Request $request
     * @param $status
     * @return \think\Response
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/9
     */
    public function sign_remind(Request $request, $status)
    {
        $uid = (int)$request->uid();
        $this->services->setSignRemind($uid, $status);
        return app('json')->success(100014);
    }

}
