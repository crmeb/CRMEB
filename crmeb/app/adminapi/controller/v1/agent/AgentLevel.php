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
use app\services\agent\AgentLevelServices;
use app\services\agent\AgentLevelTaskServices;
use think\facade\App;

/**
 * 分销等级控制器
 * Class AgentLevel
 * @package app\controller\admin\v1\agent
 */
class AgentLevel extends AuthController
{
    /**
     * AgentLevel constructor.
     * @param App $app
     * @param AgentLevelServices $services
     */
    public function __construct(App $app, AgentLevelServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 后台分销等级列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['keyword', '']
        ]);
        return app('json')->success($this->services->getLevelList($where));
    }

    /**
     * 添加分销等级表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create()
    {
        return app('json')->success($this->services->createForm());
    }

    /**
     * 保存分销等级
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['grade', 0],
            ['image', ''],
            ['one_brokerage', 0],
            ['two_brokerage', 0],
            ['status', 0]]);
        if (!$data['name']) return app('json')->fail(400200);
        if (!$data['grade']) return app('json')->fail(400201);
        if (!$data['image']) return app('json')->fail(400202);
        if ($data['two_brokerage'] > $data['one_brokerage']) {
            return app('json')->fail(400203);
        }
        $grade = $this->services->get(['grade' => $data['grade'], 'is_del' => 0]);
        if ($grade) {
            return app('json')->fail(400204);
        }
        $data['add_time'] = time();
        $this->services->save($data);
        return app('json')->success(400205);
    }

    /**
     * 显示指定的资源
     * @param $id
     */
    public function read($id)
    {

    }

    /**
     * 编辑分销等级表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        return app('json')->success($this->services->editForm((int)$id));
    }

    /**
     * 修改分销等级
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
            ['grade', 0],
            ['image', ''],
            ['one_brokerage', 0],
            ['two_brokerage', 0],
            ['status', 0]]);
        if (!$data['name']) return app('json')->fail(400200);
        if (!$data['grade']) return app('json')->fail(400201);
        if (!$data['image']) return app('json')->fail(400202);
        if ($data['two_brokerage'] > $data['one_brokerage']) {
            return app('json')->fail(400203);
        }
        if (!$levelInfo = $this->services->getLevelInfo((int)$id)) return app('json')->fail(400206);
        $grade = $this->services->get(['grade' => $data['grade'], 'is_del' => 0]);
        if ($grade && $grade['id'] != $id) {
            return app('json')->fail(400204);
        }

        $levelInfo->name = $data['name'];
        $levelInfo->grade = $data['grade'];
        $levelInfo->image = $data['image'];
        $levelInfo->one_brokerage = $data['one_brokerage'];
        $levelInfo->two_brokerage = $data['two_brokerage'];
        $levelInfo->status = $data['status'];
        $levelInfo->save();
        return app('json')->success(100001);
    }

    /**
     * 删除分销等级
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail(100100);
        //检查分销等级数据是否存在
        $levelInfo = $this->services->getLevelInfo((int)$id);
        if ($levelInfo) {
            //更新数据为已删除
            $res = $this->services->update($id, ['is_del' => 1]);
            if (!$res)
                return app('json')->fail(100008);
            //删除该等级的任务为已删除
            /** @var AgentLevelTaskServices $agentLevelTaskServices */
            $agentLevelTaskServices = app()->make(AgentLevelTaskServices::class);
            $agentLevelTaskServices->update(['level_id' => $id], ['is_del' => 1]);
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
