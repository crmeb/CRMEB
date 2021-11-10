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
namespace app\adminapi\controller\v1\agent;


use app\adminapi\controller\AuthController;
use app\services\agent\AgentLevelTaskServices;
use think\facade\App;


/**
 * Class AgentLevelTask
 * @package app\controller\admin\v1\agent
 */
class AgentLevelTask extends AuthController
{
    /**
     * AgentLevelTask constructor.
     * @param App $app
     * @param AgentLevelTaskServices $services
     */
    public function __construct(App $app, AgentLevelTaskServices $services)
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
            ['id', 0],
            ['status', ''],
            ['keyword', '']
        ]);
        if (!$where['id']) {
            return app('json')->fail('缺少参数ID');
        }
        $where['level_id'] = $where['id'];
        unset($where['id']);
        return app('json')->success($this->services->getLevelTaskList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        [$level_id] = $this->request->postMore([
            ['level_id', 0]], true);
        if (!$level_id) {
            return app('json')->fail('缺少等级ID');
        }
        return app('json')->success($this->services->createForm((int)$level_id));
    }

    /**
     * 保存新建的资源
     *
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['level_id', 0],
            ['name', ''],
            ['type', ''],
            ['number', 0],
            ['desc', 0],
            ['sort', 0],
            ['status', 0]]);
        if (!$data['level_id']) return app('json')->fail('缺少等级ID');
        if (!$data['name']) return app('json')->fail('请输入等级名称');
        if (!$data['type']) return app('json')->fail('请选择任务类型');
        if (!$data['number']) return app('json')->fail('请输入限定数量');
        $this->services->checkTypeTask(0, $data);
        $data['add_time'] = time();
        $this->services->save($data);
        return app('json')->success('添加等级成功!');
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
        return app('json')->success($this->services->editForm((int)$id));
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
            ['name', ''],
            ['type', ''],
            ['number', 0],
            ['desc', 0],
            ['sort', 0],
            ['status', 0]]);
        if (!$data['name']) return app('json')->fail('请输入等级名称');
        if (!$data['type']) return app('json')->fail('请选择任务类型');
        if (!$data['number']) return app('json')->fail('请输入限定数量');
        if (!$levelTaskInfo = $this->services->getLevelTaskInfo((int)$id)) return app('json')->fail('编辑的任务不存在!');
        $this->services->checkTypeTask((int)$id, $data);
        $levelTaskInfo->name = $data['name'];
        $levelTaskInfo->type = $data['type'];
        $levelTaskInfo->number = $data['number'];
        $levelTaskInfo->desc = $data['desc'];
        $levelTaskInfo->sort = $data['sort'];
        $levelTaskInfo->status = $data['status'];
        $levelTaskInfo->save();
        return app('json')->success('修改成功!');
    }

    /**
     * 删除指定资源
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail('参数错误，请重新打开');
        $levelTaskInfo = $this->services->getLevelTaskInfo((int)$id);
        if ($levelTaskInfo) {
            $res = $this->services->update($id, ['is_del' => 1]);
            if (!$res)
                return app('json')->fail('删除失败,请稍候再试!');
        }
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
        $this->services->update($id, ['status' => $status]);
        return app('json')->success($status == 0 ? '隐藏成功' : '显示成功');
    }

}
