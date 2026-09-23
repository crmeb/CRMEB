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

namespace app\adminapi\controller\driver\logistics;

use app\adminapi\controller\AuthController;
use app\services\driver\logistics\LogisticsDriverServices;
use think\facade\App;

/**
 * 物流查询驱动管理控制器
 * Class LogisticsDriverController
 * @package app\adminapi\controller\driver\logistics
 */
class LogisticsDriverController extends AuthController
{
    /**
     * @var LogisticsDriverServices
     */
    protected $services;

    /**
     * LogisticsDriverController constructor.
     * @param App $app
     * @param LogisticsDriverServices $services
     */
    public function __construct(App $app, LogisticsDriverServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取驱动列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['driver_name', ''],
            ['status', ''],
        ]);

        return app('json')->success($this->services->getList($where));
    }

    /**
     * 同步驱动信息（扫描 plugin.json 并同步到数据库）
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sync()
    {
        $result = $this->services->syncDrivers();
        return app('json')->success('同步成功', $result);
    }

    /**
     * 更新驱动状态
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function status($id)
    {
        $status = (int)$this->request->post('status', 0);

        $driver = $this->services->getById($id);
        if (!$driver) {
            return app('json')->fail('驱动不存在');
        }

        $this->services->updateStatus($driver['driver_key'], $status);
        return app('json')->success('修改成功');
    }

    /**
     * 获取驱动详情
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

        try {
            $data = $this->services->getDetail($id);
            return app('json')->success($data);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取驱动的配置字段定义
     * @param $driverKey
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function configFields($driverKey)
    {
        if (!$driverKey) {
            return app('json')->fail('参数错误');
        }

        $fields = $this->services->getConfigFields($driverKey);
        return app('json')->success($fields);
    }

    /**
     * 获取所有启用的驱动（用于下拉选择）
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function enabled()
    {
        $drivers = $this->services->getEnabledDrivers();
        $list = array_map(function ($driver) {
            return [
                'driver_key' => $driver['driver_key'],
                'driver_name' => $driver['driver_name'],
            ];
        }, $drivers);

        return app('json')->success($list);
    }

    /**
     * 保存驱动配置
     * @param $id
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveConfig($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $driver = $this->services->get($id);
        if (!$driver) {
            return app('json')->fail('驱动不存在');
        }

        $params = $this->request->post([
            'driver_name',
            'sort',
            'status',
        ]);

        // 更新驱动基本信息
        $updateData = [
            'driver_name' => $params['driver_name'] ?? $driver['driver_name'],
            'sort' => $params['sort'] ?? $driver['sort'],
            'status' => $params['status'] ?? $driver['status'],
        ];

        $this->services->update($id, $updateData);

        // 获取所有配置字段，从请求中提取对应的值
        $configFields = $this->services->getConfigFields($driver['driver_key']);
        $configValues = [];
        foreach ($configFields as $field) {
            $fieldName = $field['field'];
            if ($this->request->has($fieldName)) {
                $configValues[$fieldName] = $this->request->post($fieldName);
            }
        }
        if (!empty($configValues)) {
            $this->services->saveDriverConfig($driver['driver_key'], $configValues);
        }

        return app('json')->success('保存成功');
    }
}
