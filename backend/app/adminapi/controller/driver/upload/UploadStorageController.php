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
use app\services\driver\upload\UploadStorageServices;
use app\services\driver\upload\UploadDriverServices;
use think\App;

/**
 * 存储空间管理控制器
 * Class UploadStorageController
 * @package app\adminapi\controller\driver\upload
 */
class UploadStorageController extends AuthController
{
    /**
     * @var UploadStorageServices
     */
    protected $services;



    /**
     * UploadStorageController constructor.
     * @param App $app
     * @param UploadStorageServices $services
     */
    public function __construct(App $app, UploadStorageServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['type', ''],
            ['driver_key', ''],
        ]);
        $where = array_filter($where, function ($v) { return $v !== ''; });
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 获取创建数据表单
     * @param $type
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create($type)
    {
        if (!$type) {
            return app('json')->fail('参数错误');
        }
        return app('json')->success($this->services->getFormStorage($type));
    }

    /**
     * 获取配置表单
     * @param $type
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getConfigForm($type)
    {
        return app('json')->success($this->services->getFormStorageConfig($type));
    }

    /**
     * 获取配置类型
     * @return mixed
     */
    public function getConfig()
    {
        /** @var UploadDriverServices $driverServices */
        $driverServices = app()->make(UploadDriverServices::class);
        $enabledDrivers = $driverServices->getEnabledDrivers();
        $driverKey = !empty($enabledDrivers) ? $enabledDrivers[0]['driver_key'] : 'local';
        return app('json')->success(['type' => $driverServices->getTypeByDriverKey($driverKey)]);
    }

    /**
     * @return mixed
     */
    public function saveConfig()
    {
        // 配置字段由驱动声明，服务端按声明白名单保存，不能硬编码字段名。
        $data = $this->request->post();
        $typeOrKey = $data['driver_key'] ?? $data['type'] ?? null;
        $config = array_diff_key($data, ['driver_key' => true, 'type' => true]);
        if ($config) $this->services->saveConfig($typeOrKey, $config);
        else $this->services->switchDriver($typeOrKey);

        return app('json')->success('保存成功');
    }

    /**
     * @param $type
     * @return mixed
     */
    public function synch($type)
    {
        $data = $this->services->synchronization($type);
        return app('json')->success($data);
    }

    /**
     * 保存类型
     * @param $type
     * @return mixed
     */
    public function save($type)
    {
        $data = $this->request->post();
        $this->services->saveStorage($type, $data);

        return app('json')->success('添加成功');
    }

    /**
     * 修改状态
     * @param $id
     * @return mixed
     */
    public function status($id)
    {
        if (!ctype_digit((string)$id) || (int)$id <= 0) {
            return app('json')->fail('参数错误');
        }
        $status = $this->request->param('status', null);
        if ($status !== null && ((!is_int($status) && !is_string($status)) || !in_array((string)$status, ['0', '1'], true))) return app('json')->fail('状态参数错误');
        $this->services->setStorageStatus((int)$id, $status === null ? 1 : (int)$status);
        return app('json')->success('修改成功');
    }

    /**
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getUpdateDomainForm($id)
    {
        return app('json')->success($this->services->getUpdateDomainForm((int)$id));
    }

    /**
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateDomain($id)
    {
        $data = $this->request->post();
        $domain = $data['domain'] ?? '';
        if (!ctype_digit((string)$id) || (int)$id <= 0 || !is_string($domain) || $domain === '') {
            return app('json')->fail('参数错误');
        }
        $this->services->updateDomain((int)$id, $domain, array_intersect_key($data, ['cdn' => true, 'cname' => true]));

        return app('json')->success('修改成功');
    }

    /**
     * 删除
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($id)
    {
        if (!ctype_digit((string)$id) || (int)$id <= 0) {
            return app('json')->fail('参数错误');
        }

        if ($this->services->deleteStorage((int)$id)) {
            return app('json')->success('删除成功');
        } else {
            return app('json')->fail('删除失败');
        }
    }

    /**
     * 切换存储类型
     * @param $type
     * @return mixed
     */
    public function uploadType($type)
    {
        $this->services->switchDriver($type);
        return app('json')->success('切换存储成功');
    }
}
