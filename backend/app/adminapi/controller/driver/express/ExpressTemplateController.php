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

namespace app\adminapi\controller\driver\express;

use app\adminapi\controller\AuthController;
use app\services\driver\express\ExpressTemplateServices;
use app\services\driver\express\ExpressDumpLogServices;
use app\services\driver\express\ExpressConfigServices;
use crmeb\services\logistics\Express;
use app\services\driver\express\ExpressDriverServices;
use think\facade\App;

/**
 * 电子面单管理控制器
 * Class ExpressTemplateController
 * @package app\adminapi\controller\driver\express
 */
class ExpressTemplateController extends AuthController
{
    /**
     * @var ExpressTemplateServices
     */
    protected $services;

    /**
     * @var ExpressDumpLogServices
     */
    protected $dumpLogServices;

    /**
     * @var ExpressConfigServices
     */
    protected $configServices;

    /**
     * ExpressTemplateController constructor.
     * @param App $app
     * @param ExpressTemplateServices $services
     * @param ExpressDumpLogServices $dumpLogServices
     * @param ExpressConfigServices $configServices
     */
    public function __construct(
        App $app,
        ExpressTemplateServices $services,
        ExpressDumpLogServices $dumpLogServices,
        ExpressConfigServices $configServices
    ) {
        parent::__construct($app);
        $this->services = $services;
        $this->dumpLogServices = $dumpLogServices;
        $this->configServices = $configServices;
    }

    /**
     * 模板列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['com', ''],
            ['temp_name', ''],
            ['status', ''],
        ]);
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 从一号通同步指定快递公司的模板
     * @return mixed
     */
    public function sync()
    {
        [$com, $comName] = $this->request->postMore([
            ['com', ''],
            ['com_name', ''],
        ], true);

        /** @var ExpressDriverServices $driverServices */
        $driverServices = app()->make(ExpressDriverServices::class);
        $result = $this->services->syncTemplates($driverServices->createExpressServiceByCode($com), $com, $comName);
        return app('json')->success('同步成功', $result);
    }

    /**
     * 模板详情
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $template = $this->services->getById((int)$id);
        if (!$template) {
            return app('json')->fail('模板不存在');
        }
        return app('json')->success($template->toArray());
    }

    /**
     * 修改模板状态
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function status($id)
    {
        $status = (int)$this->request->post('status', 0);
        $this->services->updateStatus((int)$id, $status);
        return app('json')->success('修改成功');
    }

    /**
     * 更新模板
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['temp_name', ''],
            ['sort', 0],
            ['status', 1],
        ]);
        $this->services->update((int)$id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 删除模板
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->services->delete((int)$id);
        return app('json')->success('删除成功');
    }

    // ==================== 电子面单配置 ====================

    /**
     * 获取电子面单配置
     * @return mixed
     */
    public function getConfig()
    {
        return app('json')->success($this->configServices->getConfig());
    }

    /**
     * 保存电子面单配置
     * @return mixed
     */
    public function saveConfig()
    {
        $data = $this->request->postMore([
            ['open', 0],
            ['siid', ''],
            ['id', ''],
            ['temp_id', ''],
            ['com', ''],
            ['to_name', ''],
            ['to_tel', ''],
            ['to_address', ''],
        ]);
        $this->configServices->saveConfig($data);
        return app('json')->success('保存成功');
    }

    /**
     * 检查电子面单是否可用
     * @return mixed
     */
    public function checkAvailable()
    {
        return app('json')->success($this->configServices->checkAvailable());
    }

    // ==================== 打印记录 ====================

    /**
     * 打印记录列表
     * @return mixed
     */
    public function dumpLogList()
    {
        $where = $this->request->getMore([
            ['order_sn', ''],
            ['com', ''],
            ['kuaidinum', ''],
            ['status', ''],
        ]);
        return app('json')->success($this->dumpLogServices->getList($where));
    }

    /**
     * 打印记录详情
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function dumpLogDetail($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $log = $this->dumpLogServices->getById((int)$id);
        if (!$log) {
            return app('json')->fail('记录不存在');
        }
        return app('json')->success($log->toArray());
    }
}
