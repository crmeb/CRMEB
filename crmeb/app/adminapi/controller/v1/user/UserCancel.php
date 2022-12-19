<?php

namespace app\adminapi\controller\v1\user;

use app\adminapi\controller\AuthController;
use app\services\user\UserCancelServices;
use think\facade\App;

class UserCancel extends AuthController
{
    /**
     * UserCancel constructor.
     * @param App $app
     * @param UserCancelServices $services
     */
    public function __construct(App $app, UserCancelServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取注销列表
     * @return mixed
     */
    public function getCancelList()
    {
        $where = $this->request->postMore([
            ['status', 0],
            ['keywords', ''],
        ]);
        $data = $this->services->getCancelList($where);
        return app('json')->success($data);
    }

    /**
     * 备注
     * @return mixed
     */
    public function setMark()
    {
        [$id, $mark] = $this->request->postMore([
            ['id', 0],
            ['mark', ''],
        ], true);
        $this->services->serMark($id, $mark);
        return app('json')->success(100024);
    }

    public function agreeCancel($id)
    {
        return app('json')->success(400319);
    }

    public function refuseCancel($id)
    {
        return app('json')->success(400320);
    }
}
