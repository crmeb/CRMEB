<?php

namespace app\adminapi\controller\v1\agent;

use app\adminapi\controller\AuthController;
use app\services\agent\SpreadApplyServices;
use think\facade\App;

class SpreadApply extends AuthController
{
    public function __construct(App $app, SpreadApplyServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function applyList()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['keyword', ''],
        ]);
        return app('json')->success($this->services->applyList($where));
    }

    public function applyExamine($id, $uid, $status)
    {
        [$refusal_reason] = $this->request->postMore([
            ['refusal_reason', ''],
        ], true);
        $this->services->applyExamine($id, $uid, $status, $refusal_reason);
        return app('json')->success($status == 1 ? '审核通过' : '拒绝成功');
    }

    public function applyDelete($id)
    {
        $this->services->applyDelete($id);
        return app('json')->success('删除成功');
    }
}
