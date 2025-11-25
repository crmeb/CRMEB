<?php

namespace app\adminapi\controller\v1\product;

use app\adminapi\controller\AuthController;
use app\services\product\product\StoreProductProtectionServices;
use think\facade\App;

class StoreProductProtection extends AuthController
{
    public function __construct(App $app, StoreProductProtectionServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function protectionList()
    {
        $where = $this->request->getMore([
            ['title', ''],
            ['status', '']
        ]);
        $where['is_del'] = 0;
        return app('json')->success($this->services->protectionList($where));
    }

    public function protectionInfo($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        $info = $this->services->protectionInfo($id);
        return app('json')->success($info);
    }

    public function protectionForm($id)
    {
        return app('json')->success($this->services->protectionForm($id));
    }

    public function protectionSave($id)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['content', ''],
            ['image', ''],
            ['sort', 0],
            ['status', 0]
        ]);
        $this->services->protectionSave($id, $data);
        return app('json')->success('保存成功');
    }

    public function protectionStatus($id, $status)
    {
        if (!$id) return app('json')->fail('参数错误');
        $this->services->protectionStatus($id, $status);
        return app('json')->success('修改成功');
    }

    public function protectionDel($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        $this->services->protectionDel($id);
        return app('json')->success('删除成功');
    }
}
