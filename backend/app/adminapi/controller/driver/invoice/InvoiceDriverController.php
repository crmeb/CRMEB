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

namespace app\adminapi\controller\driver\invoice;

use app\adminapi\controller\AuthController;
use app\services\driver\invoice\InvoiceDriverServices;

/**
 * 发票驱动管理控制器
 * Class InvoiceDriverController
 * @package app\adminapi\controller\driver\invoice
 */
class InvoiceDriverController extends AuthController
{
    /**
     * @var InvoiceDriverServices
     */
    protected $services;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->services = app()->make(InvoiceDriverServices::class);
    }

    /**
     * 获取发票驱动列表
     * @return mixed
     */
    public function index()
    {
        try {
            $where = [];
            if ($this->request->has('status', 'param')) {
                $where['status'] = $this->request->param('status');
            }
            $list = $this->services->getList($where);
            return app('json')->success($list);
        } catch (\Throwable $e) {
            return app('json')->fail('获取列表失败：' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    /**
     * 同步驱动信息（扫描 driver/invoice/ 目录写入数据库）
     * @return mixed
     */
    public function sync()
    {
        try {
            $result = $this->services->syncDrivers();
            return app('json')->success('同步成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('同步失败：' . $e->getMessage());
        }
    }

    /**
     * 修改驱动状态
     * @param int $id
     * @return mixed
     */
    public function status(int $id)
    {
        $status = (int)$this->request->param('status', 0);
        $this->services->updateStatus($id, $status);
        return app('json')->success('修改成功');
    }

    /**
     * 获取驱动详情（含配置字段和当前配置值）
     * @param int $id
     * @return mixed
     */
    public function detail(int $id)
    {
        $data = $this->services->getDetail($id);
        return app('json')->success($data);
    }

    /**
     * 获取驱动配置字段定义
     * @param string $driverKey
     * @return mixed
     */
    public function configFields(string $driverKey)
    {
        $fields = $this->services->getConfigFields($driverKey);
        return app('json')->success($fields);
    }

    /**
     * 获取所有已扫描的驱动列表
     * @return mixed
     */
    public function drivers()
    {
        $drivers = $this->services->getDrivers();
        return app('json')->success($drivers);
    }

    /**
     * 获取所有启用的驱动列表
     * @return mixed
     */
    public function enabled()
    {
        $drivers = $this->services->getAvailableDrivers();
        return app('json')->success($drivers);
    }

    /**
     * 保存驱动配置
     * @param int $id
     * @return mixed
     */
    public function saveConfig(int $id)
    {
        $data = $this->request->param();
        $this->services->saveConfig($id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 批量排序
     * @return mixed
     */
    public function sort()
    {
        $sortData = $this->request->param('sort_data', []);
        $this->services->setSort($sortData);
        return app('json')->success('修改成功');
    }
}
