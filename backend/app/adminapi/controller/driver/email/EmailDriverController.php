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

namespace app\adminapi\controller\driver\email;

use app\adminapi\controller\AuthController;
use app\services\driver\email\EmailDriverServices;

/**
 * 邮件驱动管理控制器
 * Class EmailDriverController
 * @package app\adminapi\controller\driver\email
 */
class EmailDriverController extends AuthController
{
    protected $services;

    protected function initialize()
    {
        parent::initialize();
        $this->services = app()->make(EmailDriverServices::class);
    }

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

    public function sync()
    {
        try {
            $result = $this->services->syncDrivers();
            return app('json')->success('同步成功', $result);
        } catch (\Throwable $e) {
            return app('json')->fail('同步失败：' . $e->getMessage());
        }
    }

    public function status(int $id)
    {
        $status = (int)$this->request->param('status', 0);
        $this->services->updateStatus($id, $status);
        return app('json')->success('修改成功');
    }

    public function detail(int $id)
    {
        $data = $this->services->getDetail($id);
        return app('json')->success($data);
    }

    public function configFields(string $driverKey)
    {
        $fields = $this->services->getConfigFields($driverKey);
        return app('json')->success($fields);
    }

    public function drivers()
    {
        $drivers = $this->services->getDrivers();
        return app('json')->success($drivers);
    }

    public function enabled()
    {
        $drivers = $this->services->getAvailableDrivers();
        return app('json')->success($drivers);
    }

    public function saveConfig(int $id)
    {
        $data = $this->request->param();
        $this->services->saveConfig($id, $data);
        return app('json')->success('保存成功');
    }

    public function sort()
    {
        $sortData = $this->request->param('sort_data', []);
        $this->services->setSort($sortData);
        return app('json')->success('修改成功');
    }
}