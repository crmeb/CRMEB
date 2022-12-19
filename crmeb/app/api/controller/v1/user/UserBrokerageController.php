<?php

namespace app\api\controller\v1\user;

use app\Request;
use app\services\user\UserBrokerageServices;

class UserBrokerageController
{
    /**
     * UserBrokerageController constructor.
     * @param UserBrokerageServices $services
     */
    public function __construct(UserBrokerageServices $services)
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
        return app('json')->success($this->services->commission($uid));
    }

    /**
     * 佣金排行
     * @param Request $request
     * @return mixed
     */
    public function brokerageRank(Request $request)
    {
        $data = $request->getMore([
            ['page', ''],
            ['limit'],
            ['type']
        ]);
        $uid = (int)$request->uid();
        return app('json')->success($this->services->brokerageRank($uid, $data['type']));
    }
}
