<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
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
 * 分销等级任务控制器
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
     * 显示等级任务列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['id', 0],
            ['status', ''],
            ['keyword', '']
        ]);
        if (!$where['id']) {
            return app('json')->fail(100100);
        }
        $where['level_id'] = $where['id'];
        unset($where['id']);
        return app('json')->success($this->services->getLevelTaskList($where));
    }

    /**
     * 等级任务添加表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        [$level_id] = $this->request->postMore([
            ['level_id', 0]], true);
        if (!$level_id) {
            return app('json')->fail(100100);
        }
        return app('json')->success($this->services->createForm((int)$level_id));
    }

    /**
     * 保存等级任务
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
        if (!$data['level_id']) return app('json')->fail(100100);
        if (!$data['name']) return app('json')->fail(400207);
        if (!$data['type']) return app('json')->fail(400208);
        if (!$data['number']) return app('json')->fail(400209);
        $this->services->checkTypeTask(0, $data);
        $data['add_time'] = time();
        $this->services->save($data);
        return app('json')->success(400210);
    }

    /**
     * 显示指定的资源
     * @param $id
     */
    public function read($id)
    {

    }

    /**
     * 等级任务修改表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        return app('json')->success($this->services->editForm((int)$id));
    }

    /**
     * 修改等级任务
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
        if (!$data['name']) return app('json')->fail(400207);
        if (!$data['type']) return app('json')->fail(400208);
        if (!$data['number']) return app('json')->fail(400209);
        if (!$levelTaskInfo = $this->services->getLevelTaskInfo((int)$id)) return app('json')->fail(400211);
        $this->services->checkTypeTask((int)$id, $data);
        $levelTaskInfo->name = $data['name'];
        $levelTaskInfo->type = $data['type'];
        $levelTaskInfo->number = $data['number'];
        $levelTaskInfo->desc = $data['desc'];
        $levelTaskInfo->sort = $data['sort'];
        $levelTaskInfo->status = $data['status'];
        $levelTaskInfo->save();
        return app('json')->success(100001);
    }

    /**
     * 删除等级任务
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail(100100);
        $levelTaskInfo = $this->services->getLevelTaskInfo((int)$id);
        if ($levelTaskInfo) {
            $res = $this->services->update($id, ['is_del' => 1]);
            if (!$res)
                return app('json')->fail(100008);
        }
        return app('json')->success(100002);
    }

    /**
     * 修改状态
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function set_status($id = 0, $status = '')
    {
        if ($status == '' || $id == 0) return app('json')->fail(100100);
        $this->services->update($id, ['status' => $status]);
        return app('json')->success(100014);
    }

}
