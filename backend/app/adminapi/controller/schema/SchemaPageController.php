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

namespace app\adminapi\controller\schema;

use app\adminapi\controller\AuthController;
use app\services\system\admin\SchemaPageAuthServices;
use crmeb\services\schema\SchemaConfigValidator;
use crmeb\services\schema\SchemaPageProvider;
use crmeb\services\schema\SchemaPageRegistry;
use think\facade\App;

/**
 * 通用页面网关控制器
 *
 * 插件管理页面的统一数据入口，按"插件目录名 + 页面标识"定位提供者并转发调用。
 * 前端通用页面容器（SchemaPage）的全部请求都经由本控制器，插件无需自行注册路由。
 */
class SchemaPageController extends AuthController
{
    /**
     * 页面提供者注册中心
     * @var SchemaPageRegistry
     */
    protected $services;

    /** @var SchemaPageAuthServices 页面与操作授权服务 */
    protected SchemaPageAuthServices $authServices;

    /**
     * 构造函数
     * @param App $app 应用实例
     * @param SchemaPageRegistry $services 页面提供者注册中心
     * @param SchemaPageAuthServices $authServices 页面授权服务
     */
    public function __construct(App $app, SchemaPageRegistry $services, SchemaPageAuthServices $authServices)
    {
        parent::__construct($app);
        $this->services = $services;
        $this->authServices = $authServices;
    }

    /**
     * 获取页面结构配置
     * @return mixed
     */
    public function config()
    {
        $provider = $this->resolveProvider('config');
        return app('json')->success('ok', SchemaConfigValidator::validate($provider->config()));
    }

    /**
     * 获取列表数据（分页 + 搜索条件 + 排序）
     *
     * order 为空时由页面提供者使用 config.default_order 声明的默认排序（前端透传）
     *
     * @return mixed
     */
    public function list()
    {
        $provider = $this->resolveProvider('list');
        $where = (array)$this->request->param('where/a', []);
        $page = max(1, (int)$this->request->param('page', 1));
        $limit = max(1, (int)$this->request->param('limit', 15));
        $order = (array)$this->request->param('order/a', []);
        return app('json')->success('ok', $provider->list($where, $page, $limit, $order));
    }

    /**
     * 获取表单规则（新增/编辑）
     * @return mixed
     */
    public function form()
    {
        $id = (int)$this->request->param('id', 0);
        $provider = $this->resolveProvider('form', '', $id);
        return app('json')->success('ok', $provider->form($id));
    }

    /**
     * 获取详情数据
     * @return mixed
     */
    public function detail()
    {
        $provider = $this->resolveProvider('detail');
        $id = (int)$this->request->param('id', 0);
        return app('json')->success('ok', $provider->detail($id));
    }

    /**
     * 保存表单（新增/编辑统一入口）
     * @return mixed
     */
    public function save()
    {
        $data = (array)$this->request->post();
        $id = (int)($data['id'] ?? 0);
        $provider = $this->resolveProvider('save', '', $id);
        unset($data['id']);
        $provider->save($id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 执行通用动作（删除/启停等）
     * @return mixed
     */
    public function action()
    {
        $action = (string)$this->request->param('action', '');
        if ($action === '') {
            return app('json')->fail('缺少动作标识');
        }
        $payload = (array)$this->request->param('payload/a', []);
        $provider = $this->resolveProvider('action', $action);
        $result = $provider->action($action, $payload);
        $msg = is_array($result) ? (string)($result['msg'] ?? '操作成功') : '操作成功';
        return app('json')->success($msg, is_array($result) ? $result : []);
    }

    /**
     * 解析路由参数并定位页面提供者
     *
     * @param string $endpoint 网关端点
     * @param string $action 动作标识
     * @param int $id 表单记录编号
     * @return SchemaPageProvider
     */
    protected function resolveProvider(string $endpoint, string $action = '', int $id = 0): SchemaPageProvider
    {
        $pluginKey = (string)$this->request->param('pluginKey', '');
        $pageKey = (string)$this->request->param('pageKey', '');
        if ($pluginKey === '' || $pageKey === '') {
            throw new \crmeb\exceptions\AdminException('缺少页面定位参数');
        }
        $provider = $this->services->get($pluginKey, $pageKey);
        if ($provider === null) {
            throw new \crmeb\exceptions\AdminException('页面不存在或插件未启用');
        }
        $this->authServices->verify($this->request, $pluginKey, $pageKey, $provider->config(), $endpoint, $action, $id);
        return $provider;
    }
}
