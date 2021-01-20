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
namespace app\api\controller\v1\user;


use app\Request;
use app\services\other\QrcodeServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\config\SystemConfigServices;
use app\services\user\UserBillServices;
use crmeb\services\MiniProgramService;
use crmeb\services\UploadService;
use crmeb\services\UtilService;

/**
 * 账单类
 * Class UserBillController
 * @package app\api\controller\user
 */
class UserBillController
{
    protected $services = NUll;

    /**
     * UserBillController constructor.
     * @param UserBillServices $services
     */
    public function __construct(UserBillServices $services)
    {
        $this->services = $services;
    }

    /**
     * 推广数据    昨天的佣金   累计提现金额  当前佣金
     * @param Request $request
     * @return mixed
     */
    public function commission(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->successful($this->services->commission($uid));
    }


    /**
     * 推广佣金明细
     * @param Request $request
     * @param $type 0 全部  1 消费
     * @return mixed
     */
    public function spread_commission(Request $request, $type)
    {
        $uid = (int)$request->uid();
        return app('json')->successful($this->services->getUserBillList($uid, $type));
    }




    /**
     * 积分记录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function integral_list(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $this->services->getIntegralList($uid);
        return app('json')->successful($data['list'] ?? []);
    }

    /**
     * 签到用户信息
     * @param Request $request
     * @return mixed
     */
    public function point_user(Request $request)
    {
        list($sign, $integral, $all) = $request->postMore([
            ['sign', 0],
            ['integral', 0],
            ['all', 0],
        ], true);
        $uid = (int)$request->uid();
        return app('json')->success($this->services->pointUser($uid, $sign, $integral, $all));
    }

}
