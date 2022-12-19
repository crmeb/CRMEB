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

namespace app\api\controller\pc;


use app\Request;
use app\services\pc\UserServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserMoneyServices;

class UserController
{
    protected $services;

    public function __construct(UserServices $services)
    {
        $this->services = $services;
    }

    /**
     * 用户资金记录明细
     * @param Request $request
     * @param $type 0 全部  1 消费  2 充值  3 返佣  4 提现
     * @return mixed
     */
    public function getBalanceRecord(Request $request, $type)
    {
        $uid = (int)$request->uid();
        $data = [];
        switch ($type) {
            case 0:
            case 1:
            case 2:
                /** @var UserMoneyServices $moneyService */
                $moneyService = app()->make(UserMoneyServices::class);
                $data = $moneyService->getMoneyList($uid, $type);
                break;
            case 3:
            case 4:
                /** @var UserBrokerageServices $brokerageService */
                $brokerageService = app()->make(UserBrokerageServices::class);
                $data = $brokerageService->getBrokerageList($uid, $type);
                break;
        }
        return app('json')->success($data);
    }

    /**
     * 获取收藏列表
     * @param Request $request
     * @return mixed
     */
    public function getCollectList(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getCollectList($uid));
    }
}
