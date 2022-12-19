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
namespace app\api\controller\v1\user;

use app\Request;
use app\services\user\member\MemberCardServices;
use app\services\user\UserServices;
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
     * @param Request $request
     * @param UserServices $userServices
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sign_config(Request $request, UserServices $userServices)
    {
        $signConfig = sys_data('sign_day_num') ?? [];
//        $uid = (int)$request->uid();
//        $user = $userServices->getUserInfo($uid);
//        //是否是付费会员
//        if ($user['is_money_level'] > 0) {
//            //看是否开启签到积分翻倍奖励
//            /** @var MemberCardServices $memberCardService */
//            $memberCardService = app()->make(MemberCardServices::class);
//            $sign_rule_number = $memberCardService->isOpenMemberCard('sign');
//            if ($sign_rule_number) {
//                foreach ($signConfig as &$value) {
//                    $value['sign_num'] = (int)$sign_rule_number * $value['sign_num'];
//                }
//            }
//        }
        return app('json')->success($signConfig);
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
        $uid = (int)$request->uid();
        if ($integral = $this->services->sign($uid)) {
            return app('json')->success(410127, ['integral' => $integral], ['integral' => $integral]);
        }
        return app('json')->fail(410128);
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

}
