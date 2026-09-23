<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\product;

use app\adminapi\controller\AuthController;
use app\services\product\product\StoreProductProtectionServices;
use think\facade\App;

class StoreProductProtectionController extends AuthController
{
    /**
     * @var StoreProductProtectionServices
     */
    protected $services;


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
