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
namespace app\adminapi\controller\driver\printer;

use app\adminapi\controller\AuthController;
use app\services\driver\printer\SystemTicketServices;
use app\services\driver\printer\PrinterChannelServices;
use think\facade\App;

/**
 * 系统票据控制器
 * Class SystemTicketController
 * @package app\adminapi\controller\driver\printer
 */
class SystemTicketController extends AuthController
{
    /**
     * @var SystemTicketServices
     */
    protected $services;

    public function __construct(App $app, SystemTicketServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function ticketList()
    {
        $where = $this->request->getMore([
            ['type', 0],
            ['keyword', ''],
        ]);
        return app('json')->success($this->services->ticketList($where));
    }

    public function ticketForm($id)
    {
        return app('json')->success($this->services->ticketForm($id));
    }

    public function ticketSave($id)
    {
        $data = $this->request->postMore([
            ['print_name', ''],
            ['type', 0],
            ['yly_user_id', ''],
            ['yly_app_id', ''],
            ['yly_app_secret', ''],
            ['yly_sn', ''],
            ['fey_user', ''],
            ['fey_ukey', ''],
            ['fey_sn', ''],
            ['times', 1],
            ['print_type', 1],
            ['status', 1],
        ]);
        $this->services->ticketSave($id, $data);
        return app('json')->success('保存成功');
    }

    public function ticketSetStatus($id, $status)
    {
        $this->services->ticketSetStatus($id, $status);
        return app('json')->success('修改成功');
    }

    public function ticketDel($id)
    {
        $this->services->ticketDel($id);
        return app('json')->success('删除成功');
    }

    /**
     * 读取设备表中的小票配置，旧路由只作为内容配置别名。
     * @param mixed $id
     * @return mixed
     */
    public function ticketContent($id)
    {
        return app('json')->success(app()->make(PrinterChannelServices::class)->printContent($this->printerId($id)));
    }

    /**
     * 保存经过统一校验的小票配置。
     * @param mixed $id
     * @return mixed
     */
    public function ticketContentSave($id)
    {
        $fields = array_keys(PrinterChannelServices::defaultPrintContent());
        $data = $this->request->postMore(array_map(static fn($field) => [$field, null], $fields), false, false);
        $data = array_filter($data, fn($value, $field) => $this->request->has($field, 'param'), ARRAY_FILTER_USE_BOTH);
        app()->make(PrinterChannelServices::class)->savePrintContent($this->printerId($id), $data);
        return app('json')->success('保存成功');
    }

    /**
     * 校验旧版内容路由中的设备ID，内容配置统一读取设备表。
     * @param mixed $id
     * @return int
     */
    protected function printerId($id): int
    {
        $integer = (is_int($id) || (is_string($id) && preg_match('/^\d+$/D', $id))) ? filter_var($id, FILTER_VALIDATE_INT) : false;
        if ($integer === false || $integer <= 0) {
            throw new \think\exception\ValidateException('打印机ID无效');
        }
        return (int)$integer;
    }
}
