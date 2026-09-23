<?php

namespace app\adminapi\controller\driver\copyproduct;

use app\adminapi\controller\AuthController;
use app\services\driver\copyproduct\CopyDriverServices;
use think\facade\App;

class CopyDriverController extends AuthController
{
    protected $services;

    public function __construct(App $app, CopyDriverServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function index()
    {
        $where = $this->request->getMore([
            ['driver_name', ''],
            ['status', ''],
        ]);

        return app('json')->success($this->services->getList($where));
    }

    public function sync()
    {
        $result = $this->services->syncDrivers();
        return app('json')->success('同步成功', $result);
    }

    public function status($id)
    {
        $status = (int)$this->request->post('status', 0);

        $driver = $this->services->getById($id);
        if (!$driver) {
            return app('json')->fail('驱动不存在');
        }

        $this->services->updateStatus($driver->driver_key, $status);
        return app('json')->success('修改成功');
    }

    public function detail($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        return app('json')->success($this->services->getDetail($id));
    }

    public function saveConfig($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $driver = $this->services->getById($id);
        if (!$driver) {
            return app('json')->fail('驱动不存在');
        }

        $params = $this->request->post([
            'driver_name',
            'sort',
            'status',
        ]);

        $updateData = [
            'driver_name' => $params['driver_name'] ?? $driver['driver_name'],
            'sort' => $params['sort'] ?? $driver['sort'],
            'status' => $params['status'] ?? $driver['status'],
        ];

        $this->services->update($id, $updateData);

        $configFields = is_array($driver->config_fields) ? $driver->config_fields : [];
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