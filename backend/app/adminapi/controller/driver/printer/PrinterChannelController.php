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

namespace app\adminapi\controller\driver\printer;

use app\adminapi\controller\AuthController;
use app\services\driver\printer\PrinterChannelServices;

/**
 * 打印机渠道管理控制器
 * Class PrinterChannelController
 * @package app\adminapi\controller\driver\printer
 */
class PrinterChannelController extends AuthController
{
    /**
     * @var PrinterChannelServices
     */
    protected $services;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->services = app()->make(PrinterChannelServices::class);
    }

    /**
     * 获取打印机列表
     * @return mixed
     */
    public function index()
    {
        $where = [];
        if ($this->request->has('status', 'param')) {
            $where = $this->request->getMore(['status']);
        }
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 新增打印机
     * @return mixed
     */
    public function save()
    {
        $data = $this->channelData();
        $this->services->saveChannel($data);
        return app('json')->success('添加成功');
    }

    /**
     * 获取单个打印机详情
     * @param mixed $id
     * @return mixed
     */
    public function read($id)
    {
        $data = $this->services->getDetail($this->resourceId($id));
        return app('json')->success($data);
    }

    /**
     * 更新打印机
     * @param mixed $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->channelData();
        $this->services->updateChannel($this->resourceId($id), $data);
        return app('json')->success('修改成功');
    }

    /**
     * 修改状态
     * @param mixed $id
     * @param mixed $status
     * @return mixed
     */
    public function setStatus($id, $status)
    {
        $this->services->setStatus($this->resourceId($id), $status);
        return app('json')->success('修改成功');
    }

    /**
     * 删除打印机
     * @param mixed $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->services->deleteChannel($this->resourceId($id));
        return app('json')->success('删除成功');
    }

    /**
     * 获取已扫描的打印机驱动列表
     * @return mixed
     */
    public function drivers()
    {
        return app('json')->success($this->services->getDrivers());
    }

    /**
     * 批量排序
     * @return mixed
     */
    public function sort()
    {
        $sortData = $this->request->getMore([['sort_data', null]], false, false)['sort_data'];
        if (!is_array($sortData) || !array_is_list($sortData)) {
            throw new \think\exception\ValidateException('打印机排序数据无效');
        }
        $this->services->setSort($sortData);
        return app('json')->success('修改成功');
    }

    /**
     * 获取小票打印内容配置
     * @param mixed $id
     * @return mixed
     */
    public function content($id)
    {
        return app('json')->success($this->services->printContent($this->resourceId($id)));
    }

    /**
     * 保存小票打印内容配置
     * @param mixed $id
     * @return mixed
     */
    public function saveContent($id)
    {
        $data = $this->request->getMore(array_map(static fn($field) => [$field, null], array_keys(PrinterChannelServices::defaultPrintContent())), false, false);
        $data = array_filter($data, fn($value, $field) => $this->request->has($field, 'param'), ARRAY_FILTER_USE_BOTH);
        $this->services->savePrintContent($this->resourceId($id), $data);
        return app('json')->success('保存成功');
    }

    /**
     * 获取允许修改的设备字段，保留显式非法值交由服务校验。
     * @return array
     */
    protected function channelData(): array
    {
        $fields = ['print_name', 'driver', 'printer_id', 'icon', 'sort', 'status', 'times', 'print_type', 'print_content'];
        $data = $this->request->getMore(array_map(static fn($field) => [$field, null], $fields), false, false);
        return array_filter($data, fn($value, $field) => $this->request->has($field, 'param'), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * 严格校验路由设备编号，禁止小数或字符串前缀被转换成其他设备。
     * @param mixed $id
     * @return int
     */
    protected function resourceId($id): int
    {
        $integer = (is_int($id) || (is_string($id) && preg_match('/^\d+$/D', $id))) ? filter_var($id, FILTER_VALIDATE_INT) : false;
        if ($integer === false || $integer <= 0) {
            throw new \think\exception\ValidateException('打印机ID无效');
        }
        return (int)$integer;
    }
}
