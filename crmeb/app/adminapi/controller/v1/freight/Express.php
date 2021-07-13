<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\freight;

use app\adminapi\controller\AuthController;
use app\services\shipping\ExpressServices;
use think\facade\App;

/**
 * 物流
 * Class Express
 * @package app\adminapi\controller\v1\freight
 */
class Express extends AuthController
{
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
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['keyword', '']
        ]);
        return app('json')->success($this->services->getExpressList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return app('json')->success($this->services->createForm());
    }

    /**
     * 保存新建的资源
     *
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
        return app('json')->success('添加公司成功!');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        return app('json')->success($this->services->updateForm((int)$id));
    }

    /**
     * 保存更新的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['account', ''],
            ['key', ''],
            ['net_name', ''],
            ['sort', 0],
            ['is_show', 0]]);
        if (!$expressInfo = $this->services->get($id)) return app('json')->fail('编辑的记录不存在!');
        if ($expressInfo['partner_id'] == 1 && !$data['account']) {
            return app('json')->fail('请输入月结账号');
        }
        if ($expressInfo['partner_key'] == 1 && !$data['key']) {
            return app('json')->fail('请输入月结密码');
        }
        if ($expressInfo['net'] == 1 && !$data['net_name']) {
            return app('json')->fail('请输入取件网点');
        }
        $expressInfo->account = $data['account'];
        $expressInfo->key = $data['key'];
        $expressInfo->net_name = $data['net_name'];
        $expressInfo->sort = $data['sort'];
        $expressInfo->is_show = $data['is_show'];
        $expressInfo->status = 1;
        $expressInfo->save();
        return app('json')->success('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail('参数错误，请重新打开');
        $res = $this->services->delete($id);
        if (!$res)
            return app('json')->fail('删除失败,请稍候再试!');
        else
            return app('json')->success('删除成功!');
    }

    /**
     * 修改状态
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function set_status($id = 0, $status = '')
    {
        if ($status == '' || $id == 0) return app('json')->fail('参数错误');
        $this->services->update($id, ['is_show' => $status]);
        return app('json')->success($status == 0 ? '隐藏成功' : '显示成功');
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
