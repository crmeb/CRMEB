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
use app\services\driver\express\ExpressDriverServices;
use think\facade\App;

/**
 * 电子面单驱动管理控制器
 * Class ExpressDriverController
 * @package app\adminapi\controller\driver\express
 */
class ExpressDriverController extends AuthController
{
    /**
     * @var ExpressDriverServices
     */
    protected $services;

    /**
     * ExpressDriverController constructor.
     * @param App $app
     * @param ExpressDriverServices $services
     */
    public function __construct(App $app, ExpressDriverServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 驱动列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['driver_name', ''],
            ['status', ''],
        ]);
        $data = $this->services->getList($where);
        return app('json')->success($data);
    }

    /**
     * 获取驱动列表（已扫描的所有驱动）
     * @return mixed
     */
    public function drivers()
    {
        $drivers = $this->services->scanDrivers();
        return app('json')->success(array_values($drivers));
    }

    /**
     * 同步驱动信息
     * @return mixed
     */
    public function sync()
    {
        try {
            $result = $this->services->syncDrivers();
            if (!empty($result['errors'])) {
                $msg = '同步完成，但有 ' . count($result['errors']) . ' 个错误：' . implode('; ', $result['errors']);
                return app('json')->success($msg, $result);
            }
            return app('json')->success('同步成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('同步失败：' . $e->getMessage());
        }
    }

    /**
     * 驱动详情
     * @param $id
     * @return mixed
     */
    public function detail($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        return app('json')->success($this->services->getDetail((int)$id));
    }

    /**
     * 获取驱动配置字段
     * @param $driverKey
     * @return mixed
     */
    public function configFields($driverKey)
    {
        $info = $this->services->getInfoByDriverKey($driverKey);
        if (!$info) {
            return app('json')->fail('驱动不存在');
        }
        return app('json')->success($info['config_fields'] ?? []);
    }

    /**
     * 保存驱动配置
     * @param $id
     * @return mixed
     */
    public function saveConfig($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $data = $this->request->param();
        $this->services->saveConfig((int)$id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 修改状态
     * @param $id
     * @return mixed
     */
    public function status($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $status = (int)$this->request->post('status', 0);
        $this->services->setStatus((int)$id, $status);
        return app('json')->success('修改成功');
    }

    /**
     * 获取所有启用的驱动
     * @return mixed
     */
    public function enabled()
    {
        $drivers = $this->services->getEnabledDrivers();
        return app('json')->success($drivers);
    }

    /**
     * 排序
     * @return mixed
     */
    public function sort()
    {
        $data = $this->request->postMore([
            ['data', []],
        ]);
        if (empty($data['data'])) {
            return app('json')->fail('排序数据不能为空');
        }
        $this->services->sort($data['data']);
        return app('json')->success('排序成功');
    }
}
