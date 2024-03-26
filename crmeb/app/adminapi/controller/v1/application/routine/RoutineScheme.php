<?php

namespace app\adminapi\controller\v1\application\routine;

use app\adminapi\controller\AuthController;
use app\services\wechat\RoutineSchemeServices;
use think\facade\App;

class RoutineScheme extends AuthController
{
    public function __construct(App $app, RoutineSchemeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function schemeList()
    {
        $where = $this->request->postMore([
            ['title', ''],
        ]);
        return app('json')->success($this->services->schemeList($where));
    }

    public function schemeForm($id)
    {
        return app('json')->success($this->services->schemeForm($id));
    }

    public function schemeSave($id)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['path', ''],
            ['expire_type', -1],
            ['expire_num', 0],
        ]);
        $this->services->schemeSave($id, $data);
        return app('json')->success(100000);
    }

    public function schemeDel($id)
    {
        $res = $this->services->delete($id);
        if (!$res) return app('json')->fail(100008);
        return app('json')->success(100002);
    }
}