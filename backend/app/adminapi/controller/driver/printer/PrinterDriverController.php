<?php
declare(strict_types=1);

namespace app\adminapi\controller\driver\printer;

use app\adminapi\controller\AuthController;
use app\services\driver\printer\PrinterDriverServices;

/**
 * 打印机服务商驱动配置控制器。
 */
class PrinterDriverController extends AuthController
{
    /** @var PrinterDriverServices 打印机驱动服务 */
    protected $services;

    /**
     * 初始化驱动配置服务。
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();
        $this->services = app()->make(PrinterDriverServices::class);
    }

    /**
     * 获取已安装的打印机驱动列表。
     * @return mixed
     */
    public function index()
    {
        return app('json')->success($this->services->getList());
    }

    /**
     * 刷新已安装打印机驱动的元数据。
     * @return mixed
     */
    public function sync()
    {
        return app('json')->success('同步成功', $this->services->syncDrivers());
    }

    /**
     * 获取脱敏的服务商配置。
     * @param mixed $id
     * @return mixed
     */
    public function detail($id)
    {
        return app('json')->success($this->services->getDetail($this->resourceId($id)));
    }

    /**
     * 切换驱动状态，保留原始输入交由服务验证。
     * @param mixed $id
     * @return mixed
     */
    public function status($id)
    {
        $status = $this->request->getMore([['status', null]], false, false)['status'];
        $this->services->updateStatus($this->resourceId($id), $status);
        return app('json')->success('修改成功');
    }

    /**
     * 保存声明的服务商配置字段。
     * @param mixed $id
     * @return mixed
     */
    public function saveConfig($id)
    {
        $fields = array_values(array_diff(array_keys($this->request->param()), ['id']));
        $this->services->saveConfig($this->resourceId($id), $this->request->getMore($fields, false, false));
        return app('json')->success('保存成功');
    }

    /**
     * 校验路由驱动编号，拒绝非法类型、小数和字符串前缀。
     * @param mixed $id
     * @return int
     */
    protected function resourceId($id): int
    {
        $integer = (is_int($id) || (is_string($id) && preg_match('/^\d+$/D', $id))) ? filter_var($id, FILTER_VALIDATE_INT) : false;
        if ($integer === false || $integer <= 0) {
            throw new \think\exception\ValidateException('打印机驱动ID无效');
        }
        return (int)$integer;
    }
}
