<?php

namespace app\adminapi\controller\v1\finance;

use app\adminapi\controller\AuthController;
use app\services\user\UserMoneyServices;
use think\facade\App;

class UserBalance extends AuthController
{
    /**
     * UserBalance constructor.
     * @param App $app
     * @param UserMoneyServices $services
     */
    public function __construct(App $app, UserMoneyServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 余额记录
     * @return mixed
     */
    public function balanceList()
    {
        $where = $this->request->getMore([
            ['time', ''],
            ['trading_type', 0, '', 'type']
        ]);
        $date = $this->services->balanceList($where);
        return app('json')->success($date);
    }

    /**
     * 余额记录备注
     * @return mixed
     */
    public function balanceRecordRemark($id = 0)
    {
        [$mark] = $this->request->postMore([
            ['mark', '']
        ], true);
        if (!$id) return app('json')->fail('参数错误');
        if ($mark === '') return app('json')->fail('备注不能为空');
        $this->services->recordRemark($id, $mark);
        return app('json')->success('备注成功');
    }
}
