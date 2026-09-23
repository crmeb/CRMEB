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
namespace app\adminapi\controller\freight;

use app\adminapi\controller\AuthController;
use app\services\shipping\ExpressServices;
use think\facade\App;

/**
 * 物流
 * Class Express
 * @package app\adminapi\controller\freight
 */
class ExpressController extends AuthController
{
    /**
     * @var ExpressServices
     */
    protected $services;


    /**
     * 构造方法
     * Express constructor.
     * @param App $app
     * @param ExpressServices $services
     */
    public function __construct(App $app, ExpressServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取物流列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['keyword', ''],
            ['is_show', '']
        ]);
        return app('json')->success($this->services->getExpressList($where));
    }

    /**
     * 显示创建资源表单页
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return app('json')->success($this->services->createForm());
    }

    /**
     * 保存新建的资源
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            'name',
            'code',
            ['sort', 0],
            ['is_show', 0]]);
        if (!$data['name']) return app('json')->fail('请输入公司名称');
        $this->services->save($data);
        return app('json')->success('添加公司成功');
    }

    /**
     * 显示编辑资源表单页
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        return app('json')->success($this->services->updateForm((int)$id));
    }

    /**
     * 保存更新的资源
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['key', ''],
            ['net_name', ''],
            ['courier_name', ''],
            ['customer_name', ''],
            ['code_name', ''],
            ['sort', 0],
            ['is_show', 0]]);
        if (!$expressInfo = $this->services->get($id)) return app('json')->fail('数据不存在');
        if ($expressInfo['net'] == 1 && !$data['net_name']) {
            return app('json')->fail('请输入取件网点');
        }
        if ($expressInfo['check_man'] == 1 && !$data['courier_name']) {
            return app('json')->fail('请输入承载快递员名');
        }
        if ($expressInfo['partner_name'] == 1 && !$data['customer_name']) {
            return app('json')->fail('请输入客户账户名称');
        }
        if ($expressInfo['is_code'] == 1 && !$data['code_name']) {
            return app('json')->fail('请输入电子面单承载编号');
        }
        $expressInfo->account = $data['account'];
        $expressInfo->key = $data['key'];
        $expressInfo->net_name = $data['net_name'];
        $expressInfo->courier_name = $data['courier_name'];
        $expressInfo->customer_name = $data['customer_name'];
        $expressInfo->code_name = $data['code_name'];
        $expressInfo->sort = $data['sort'];
        $expressInfo->is_show = $data['is_show'];
        $expressInfo->status = 1;
        $expressInfo->save();
        return app('json')->success('修改成功');
    }

    /**
     * 删除指定资源
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        $res = $this->services->delete($id);
        if (!$res)
            return app('json')->fail('删除失败');
        else
            return app('json')->success('删除成功');
    }

    /**
     * 修改状态
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function setStatus($id = 0, $status = '')
    {
        if ($status === '' || $status === null || empty($id)) return app('json')->fail('参数错误');
        $this->services->update($id, ['is_show' => $status]);
        return app('json')->success('设置成功');
    }

    /**
     * 同步平台快递公司
     * @return mixed
     */
    public function syncExpress()
    {
        $this->services->syncExpress();
        return app('json')->success('同步成功');
    }
}
