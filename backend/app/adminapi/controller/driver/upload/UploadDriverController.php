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

namespace app\adminapi\controller\driver\upload;

use app\adminapi\controller\AuthController;
use app\services\driver\upload\UploadDriverServices;
use app\services\driver\upload\UploadStorageServices;
use think\App;

/**
 * 存储驱动管理控制器
 * Class UploadDriverController
 * @package app\adminapi\controller\driver\upload
 */
class UploadDriverController extends AuthController
{
    /**
     * @var UploadDriverServices
     */
    protected $services;

    /**
     * @var UploadStorageServices
     */
    protected $storageServices;

    /**
     * @param App $app
     * @param UploadDriverServices $services
     * @param UploadStorageServices $storageServices
     */
    public function __construct(App $app, UploadDriverServices $services, UploadStorageServices $storageServices)
    {
        parent::__construct($app);
        $this->services = $services;
        $this->storageServices = $storageServices;
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
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function status($id)
    {
        $status = filter_var($this->request->post('status', 0), FILTER_VALIDATE_INT);
        if ($status === false || !in_array($status, [0, 1], true)) {
            return app('json')->fail('无效的驱动状态');
        }

        $driver = $this->services->getById($id);
        if (!$driver) {
            return app('json')->fail('驱动不存在');
        }

        $this->services->updateStatus($driver->driver_key, $status);
        return app('json')->success('修改成功');
    }

    /**
     * 获取驱动详情
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

        $driver = $this->services->getById($id);
        if (!$driver) {
            return app('json')->fail('驱动不存在');
        }

        $data = $this->services->redactDriver($driver->toArray());
        $data['bucket_fields'] = $this->services->getBucketFields($data['driver_key']);

        return app('json')->success($data);
    }

    /**
     * 获取驱动的配置字段定义
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

        $fields = $this->services->getPublicConfigFields($driverKey);
        return app('json')->success($fields);
    }

    /**
     * 获取驱动能力声明
     * @param string $driverKey 驱动标识
     * @return mixed
     */
    public function capabilities($driverKey)
    {
        if (!$driverKey) {
            return app('json')->fail('参数错误');
        }
        return app('json')->success($this->services->getCapabilities($driverKey));
    }

    /**
     * 获取驱动的存储空间配置字段
     * @return mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bucketFields($driverKey)
    {

        if (!$driverKey) {
            return app('json')->fail('参数错误');
        }

        $fields = $this->services->getBucketFields($driverKey);
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
     * 获取上传公共配置（缩略图和水印）
     * @param string $driver_key
     * @return mixed
     */
    public function commonConfig($driver_key)
    {
        $config = $this->services->getCommonConfig($driver_key);
        return app('json')->success($config);
    }

    /**
     * 保存上传公共配置（缩略图和水印）
     * @param string $driver_key
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveCommonConfig($driver_key)
    {
        $params = $this->request->post([
            // 缩略图配置
            'image_thumb_status',
            'thumb_big_width',
            'thumb_big_height',
            'thumb_mid_width',
            'thumb_mid_height',
            'thumb_small_width',
            'thumb_small_height',
            // 水印配置
            'image_watermark_status',
            'watermark_type',
            'watermark_opacity',
            'watermark_rotate',
            'watermark_position',
            'watermark_x',
            'watermark_y',
            'watermark_image',
            'watermark_text',
            'watermark_text_size',
            'watermark_text_color',
            'watermark_text_angle',
        ]);
        $params = array_filter($params, fn($value, $name) => $this->request->has($name, 'post'), ARRAY_FILTER_USE_BOTH);

        $this->services->saveCommonConfig($driver_key, $params);

        return app('json')->success('保存成功');
    }

    /**
     * 保存驱动配置
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveConfig($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $driver = $this->services->getById((int)$id);
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
        if ($this->request->has('thumb_watermark_config', 'post')) {
            $updateData['thumb_watermark_config'] = $this->request->post('thumb_watermark_config');
        }

        // 获取所有配置字段，从请求中提取对应的值
        $configFields = $this->services->getConfigFields($driver['driver_key']);
        $configValues = [];
        foreach ($configFields as $field) {
            $fieldName = $field['field'];
            if ($this->request->has($fieldName, 'post')) {
                $configValues[$fieldName] = $this->request->post($fieldName);
            }
        }
        // 基本信息、启用状态、凭据及图片处理配置在同一事务中提交。
        $this->services->update((int)$id, $updateData, $configValues ?: null);

        return app('json')->success('保存成功');
    }
}
