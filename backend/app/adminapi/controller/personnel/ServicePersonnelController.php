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
namespace app\adminapi\controller\personnel;

use app\adminapi\controller\AuthController;
use app\services\personnel\ServicePersonnelServices;
use think\facade\App;

/**
 * 服务人员管理
 * 负责服务人员的增删改查及状态管理
 */
class ServicePersonnelController extends AuthController
{
    /**
     * @var ServicePersonnelServices
     */
    protected $services;


    /**
     * ServicePersonnelController constructor.
     * @param App $app
     * @param ServicePersonnelServices $services
     */
    public function __construct(App $app, ServicePersonnelServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 服务人员列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        return app('json')->success($this->services->getServiceList([]));
    }

    /**
     * 添加服务人员表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function add()
    {
        return app('json')->success($this->services->create());
    }

    /**
     * 保存服务人员
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['image', ''],
            ['uid', 0],
            ['avatar', ''],
            ['phone', ''],
            ['nickname', ''],
            ['status', 1],
        ]);

        $this->services->saveDeliveryService($data);
        return app('json')->success('保存成功');
    }

    /**
     * 编辑表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        return app('json')->success($this->services->edit((int)$id));
    }

    /**
     * 修改服务人员
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['avatar', ''],
            ['nickname', ''],
            ['phone', ''],
            ['status', 1],
        ]);

        $this->services->updateDeliveryService((int)$id, $data);
        return app('json')->success('修改成功');
    }

    /**
     * 删除服务人员
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$this->services->delete($id))
            return app('json')->fail('删除失败');
        else
            return app('json')->success('删除成功');
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function setStatus($id, $status)
    {
        if ($status === '' || $status === null || empty($id)) return app('json')->fail('参数错误');
        $this->services->update($id, ['status' => $status]);
        return app('json')->success('设置成功');
    }

    /**
     * 获取所有服务人员列表
     * @return mixed
     */
    public function getDeliveryList()
    {
        $data = $this->services->getDeliveryList();
        return app('json')->success($data);
    }

}
