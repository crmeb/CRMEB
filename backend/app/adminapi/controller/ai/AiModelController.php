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
declare(strict_types=1);

namespace app\adminapi\controller\ai;

use app\adminapi\controller\AuthController;
use app\services\ai\AiModelServices;

/**
 * AI 模型管理控制器（系统内置 AI 能力）
 *
 * 模型登记即「协议 + 凭证 + 模型标识」的一条记录，新增同协议厂商无需再写驱动代码。
 * Class AiModelController
 * @package app\adminapi\controller\ai
 */
class AiModelController extends AuthController
{
    /**
     * @var AiModelServices
     */
    protected $services;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->services = app()->make(AiModelServices::class);
    }

    /**
     * 模型列表
     * @return mixed
     */
    public function index()
    {
        $params = $this->request->getMore([
            ['status', ''],
            ['protocol', ''],
            ['keyword', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        $where = array_intersect_key($params, array_flip(['status', 'protocol', 'keyword']));
        return app('json')->success($this->services->getList($where, max(1, (int) $params['page']), max(1, (int) $params['limit'])));
    }

    /**
     * 模型详情
     * @param int $id 模型ID
     * @return mixed
     */
    public function read(int $id)
    {
        return app('json')->success($this->services->getDetail($id));
    }

    /**
     * 新增模型
     * @return mixed
     */
    public function save()
    {
        $id = $this->services->save($this->request->postMore([
            ['name', ''],
            ['protocol', 'openai_compatible'],
            ['provider', ''],
            ['model', ''],
            ['base_url', ''],
            ['api_key', ''],
            ['full_url', 0],
            ['supports_tools', 0],
            ['supports_vision', 0],
            ['supports_embedding', 0],
            ['context_window', 0],
            ['max_tokens', 2048],
            ['temperature', 0.7],
            ['is_default', 0],
            ['sort', 0],
            ['status', 1],
        ]));
        return app('json')->success('添加成功', ['id' => $id]);
    }

    /**
     * 修改模型
     * @param int $id 模型ID
     * @return mixed
     */
    public function update(int $id)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['protocol', 'openai_compatible'],
            ['provider', ''],
            ['model', ''],
            ['base_url', ''],
            ['api_key', ''],
            ['full_url', 0],
            ['supports_tools', 0],
            ['supports_vision', 0],
            ['supports_embedding', 0],
            ['context_window', 0],
            ['max_tokens', 2048],
            ['temperature', 0.7],
            ['is_default', 0],
            ['sort', 0],
            ['status', 1],
        ]);
        $this->services->update($id, $data);
        return app('json')->success('修改成功');
    }

    /**
     * 删除模型
     * @param int $id 模型ID
     * @return mixed
     */
    public function delete(int $id)
    {
        $this->services->delete($id);
        return app('json')->success('删除成功');
    }

    /**
     * 修改模型状态
     * @param int $id 模型ID
     * @return mixed
     */
    public function setStatus(int $id)
    {
        $this->services->setStatus($id, (int) $this->request->param('status', 0));
        return app('json')->success('修改成功');
    }

    /**
     * 设为系统默认模型
     * @param int $id 模型ID
     * @return mixed
     */
    public function setDefault(int $id)
    {
        $this->services->setDefault($id);
        return app('json')->success('设置成功');
    }

    /**
     * 内置协议族清单（表单渲染用）
     * @return mixed
     */
    public function protocols()
    {
        return app('json')->success($this->services->getProtocols());
    }

    /**
     * 可选模型清单（下拉选择用）
     * @return mixed
     */
    public function options()
    {
        $all = (int) $this->request->param('all', 0) === 1;
        return app('json')->success($this->services->getAvailableModels(!$all));
    }

    /**
     * 批量排序
     * @return mixed
     */
    public function sort()
    {
        $this->services->setSort((array) $this->request->post('sort_data', []));
        return app('json')->success('修改成功');
    }

    /**
     * 模型连通性测试（按表单配置发起一次真实请求，不落库）
     * @return mixed
     */
    public function test()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['name', ''],
            ['protocol', 'openai_compatible'],
            ['provider', ''],
            ['model', ''],
            ['base_url', ''],
            ['api_key', ''],
            ['full_url', 0],
            ['supports_tools', 0],
            ['supports_vision', 0],
            ['supports_embedding', 0],
            ['context_window', 0],
            ['max_tokens', 2048],
            ['temperature', 0.7],
            ['is_default', 0],
            ['sort', 0],
            ['status', 1],
        ]);
        return app('json')->success($this->services->testModel($data));
    }

    /**
     * 拉取厂商可用模型列表（按表单配置请求 /models 端点，不落库）
     * @return mixed
     */
    public function remoteModels()
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['base_url', ''],
            ['api_key', ''],
            ['full_url', 0],
        ]);
        return app('json')->success($this->services->fetchRemoteModels($data));
    }
}
