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

namespace app\adminapi\controller\driver\pay;

use app\adminapi\controller\AuthController;
use app\services\driver\pay\PayChannelServices;

/**
 * 支付方式管理控制器
 * Class PayChannelController
 * @package app\adminapi\controller\driver\pay
 */
class PayChannelController extends AuthController
{
    /**
     * @var PayChannelServices
     */
    protected $services;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->services = app()->make(PayChannelServices::class);
    }

    /**
     * 获取支付方式列表
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
     * 获取单个支付方式详情
     * @param int $id
     * @return mixed
     */
    public function read(int $id)
    {
        $data = $this->services->getDetail($id);
        return app('json')->success($data);
    }

    /**
     * 更新支付方式
     * @param int $id
     * @return mixed
     */
    public function update(int $id)
    {
        $data = $this->request->param();
        $this->services->updateChannel($id, $data);
        return app('json')->success('修改成功');
    }

    /**
     * 修改状态
     * @param int $id
     * @param int $status
     * @return mixed
     */
    public function setStatus(int $id, int $status)
    {
        $this->services->setStatus($id, $status);
        return app('json')->success('修改成功');
    }

    /**
     * 获取已扫描的支付驱动列表
     * @return mixed
     */
    public function drivers()
    {
        try {
            $drivers = $this->services->getDrivers();
            return app('json')->success($drivers);
        } catch (\Throwable $e) {
            return app('json')->fail('获取驱动列表失败：' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
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
