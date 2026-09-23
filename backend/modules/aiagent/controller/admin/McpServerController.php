<?php
declare(strict_types=1);

namespace modules\aiagent\controller\admin;

use app\adminapi\controller\AuthController;
use modules\aiagent\service\McpServerServices;

/** 远程 MCP Server 管理后台接口。 */
class McpServerController extends AuthController
{
    /** @var McpServerServices 远程 MCP Server 服务 */
    private McpServerServices $servers;

    protected function initialize()
    {
        parent::initialize();
        $this->servers = app()->make(McpServerServices::class);
    }

    /**
     * Server 列表
     * @return mixed
     */
    public function index()
    {
        return app('json')->success($this->servers->getList());
    }

    /**
     * Server 详情
     * @param int $id Server ID
     * @return mixed
     */
    public function read(int $id)
    {
        return app('json')->success($this->servers->get($id));
    }

    /**
     * 保存 Server（表单或 JSON 配置两种方式）
     * @param int $id Server ID，传 0 表示新增
     * @return mixed
     */
    public function save(int $id = 0)
    {
        $data = (array)$this->request->param();
        $config = trim((string)($data['config'] ?? ''));
        if ($config !== '') {
            $ids = $this->servers->saveJson($config, $id);
            return app('json')->success('保存成功', ['id' => $id > 0 ? ($ids[0] ?? $id) : ($ids[0] ?? 0), 'ids' => $ids]);
        }
        $serverId = $this->servers->save($data, $id);
        return app('json')->success('保存成功', ['id' => $serverId]);
    }

    /**
     * 删除 Server
     * @param int $id Server ID
     * @return mixed
     */
    public function delete(int $id)
    {
        $this->servers->delete($id);
        return app('json')->success('删除成功');
    }

    /**
     * 修改启用状态
     * @param int $id Server ID
     * @return mixed
     */
    public function status(int $id)
    {
        $this->servers->setStatus($id, (int)$this->request->param('status', 0));
        return app('json')->success('修改成功');
    }

    /**
     * 同步远端工具清单
     * @param int $id Server ID
     * @return mixed
     */
    public function sync(int $id)
    {
        $tools = $this->servers->syncTools($id);
        return app('json')->success('同步成功', ['count' => count($tools), 'tools' => $tools]);
    }

    /**
     * 连通性测试
     * @param int $id Server ID
     * @return mixed
     */
    public function test(int $id)
    {
        return app('json')->success($this->servers->testConnection($id));
    }
}
