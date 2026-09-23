<?php
declare(strict_types=1);

namespace modules\aiagent\controller\admin;

use app\adminapi\controller\AuthController;
use modules\aiagent\service\AgentTaskServices;
use modules\aiagent\jobs\AgentTaskJob;

/** AI 自动化任务后台接口（任务 CRUD/启停/立即执行/运行历史/运行详情） */
class TaskController extends AuthController
{
    /** @var AgentTaskServices 自动化任务服务（复用基类属性） */
    protected $services;

    protected function initialize()
    {
        parent::initialize();
        $this->services = app()->make(AgentTaskServices::class);
    }

    /**
     * 任务列表（当前管理员，分页）
     * @return mixed
     */
    public function index()
    {
        [$page, $limit] = $this->pageParams();
        return app('json')->success($this->services->index((int)$this->adminId, $page, $limit));
    }

    /**
     * 解析分页参数（request 无 getPage 方法，此处等效 BaseService::getPageValue 的取值口径）
     * @return array{int,int}
     */
    protected function pageParams(): array
    {
        $page = max(1, (int)$this->request->param('page/d', 1));
        $limit = (int)$this->request->param('limit/d', 0);
        if ($limit < 1 || $limit > 100) {
            $limit = 20;
        }
        return [$page, $limit];
    }

    /**
     * 创建/编辑任务（编辑时表单需携带 id）
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['name', ''],
            ['instruction', ''],
            ['model_id', 0],
            ['server_ids', []],
            ['skill_keys', []],
            ['autonomy', AgentTaskServices::AUTONOMY_READONLY],
            ['schedule_type', AgentTaskServices::SCHEDULE_DAILY],
            ['schedule_value', ''],
            ['max_steps', 10],
            ['timeout', 300],
            ['notify_channels', []],
            ['notify_email', ''],
            ['status', 1],
        ]);
        // 超级管理员（level=0）才允许设置「全自动」自主级别，服务端同参数二次校验
        $isSuperAdmin = (int)($this->adminInfo['level'] ?? 1) === 0;
        $id = $this->services->save((int)$this->adminId, $data, $isSuperAdmin);
        return app('json')->success('保存成功', ['id' => $id]);
    }

    /**
     * 任务详情
     * @param int $id 任务 ID
     * @return mixed
     */
    public function read(int $id)
    {
        return app('json')->success($this->services->read($id, (int)$this->adminId));
    }

    /**
     * 删除任务（含运行历史）
     * @param int $id 任务 ID
     * @return mixed
     */
    public function delete(int $id)
    {
        $this->services->delete($id, (int)$this->adminId);
        return app('json')->success('删除成功');
    }

    /**
     * 修改任务启停状态
     * @param int $id 任务 ID
     * @return mixed
     */
    public function status(int $id)
    {
        $status = (int)$this->request->param('status', 0);
        $this->services->setStatus($id, (int)$this->adminId, $status);
        return app('json')->success($status === 1 ? '已启用' : '已停用');
    }

    /**
     * 立即执行任务（与调度执行同链路入队，兼作测试入口）
     * @param int $id 任务 ID
     * @return mixed
     */
    public function trigger(int $id)
    {
        $runId = $this->services->trigger($id, (int)$this->adminId);
        AgentTaskJob::dispatch('doJob', [$runId]);
        return app('json')->success('已加入执行队列', ['run_id' => $runId]);
    }

    /**
     * 任务运行历史（分页）
     * @param int $id 任务 ID
     * @return mixed
     */
    public function runs(int $id)
    {
        [$page, $limit] = $this->pageParams();
        return app('json')->success($this->services->runs($id, (int)$this->adminId, $page, $limit));
    }

    /**
     * 全量运行记录（当前管理员的全部任务，分页，附任务名称）
     * @return mixed
     */
    public function runIndex()
    {
        [$page, $limit] = $this->pageParams();
        return app('json')->success($this->services->runIndex((int)$this->adminId, $page, $limit));
    }

    /**
     * 运行详情（含关联会话消息，回放执行全过程）
     * @param int $id 运行记录 ID
     * @return mixed
     */
    public function runDetail(int $id)
    {
        return app('json')->success($this->services->runDetail($id, (int)$this->adminId));
    }
}
