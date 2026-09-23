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

namespace app\adminapi\controller\driver\express;

use app\adminapi\controller\AuthController;
use app\services\driver\express\ExpressCompanyServices;
use think\facade\App;

/**
 * 电子面单物流公司管理控制器
 * Class ExpressCompanyController
 * @package app\adminapi\controller\driver\express
 */
class ExpressCompanyController extends AuthController
{
    /**
     * @var ExpressCompanyServices
     */
    protected $services;

    /**
     * 构造方法
     * ExpressCompanyController constructor.
     * @param App $app
     * @param ExpressCompanyServices $services
     */
    public function __construct(App $app, ExpressCompanyServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 物流公司列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['driver_key', ''],
            ['code', ''],
            ['keyword', ''],
        ]);
        // 关键词搜索 name 或 code
        if (!empty($where['keyword'])) {
            $where['name|code'] = $where['keyword'];
        }
        unset($where['keyword']);
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 获取物流公司详情
     * @param int $id
     * @return mixed
     */
    public function read(int $id)
    {
        return app('json')->success($this->services->getDetail($id));
    }

    /**
     * 新增物流公司
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['driver_key', ''],
            ['code', ''],
            ['name', ''],
            ['account', ''],
            ['key', ''],
            ['net_name', ''],
            ['courier_name', ''],
            ['customer_name', ''],
            ['code_name', ''],
            ['siid', ''],
            ['temp_id', ''],
            ['print_open', 0],
            ['partner_id', 0],
            ['partner_key', 0],
            ['net', 0],
            ['check_man', 0],
            ['partner_name', 0],
            ['is_code', 0],
            ['sort', 0],
            ['is_show', 1],
            ['status', 1],
        ]);
        $this->services->createCompany($data);
        return app('json')->success('新增成功');
    }

    /**
     * 修改物流公司
     * @param int $id
     * @return mixed
     */
    public function update(int $id)
    {
        $data = $this->request->postMore([
            ['driver_key', ''],
            ['code', ''],
            ['name', ''],
            ['account', ''],
            ['key', ''],
            ['net_name', ''],
            ['courier_name', ''],
            ['customer_name', ''],
            ['code_name', ''],
            ['siid', ''],
            ['temp_id', ''],
            ['print_open', 0],
            ['partner_id', 0],
            ['partner_key', 0],
            ['net', 0],
            ['check_man', 0],
            ['partner_name', 0],
            ['is_code', 0],
            ['sort', 0],
            ['is_show', 1],
            ['status', 1],
        ]);
        $this->services->updateCompany($id, $data);
        return app('json')->success('修改成功');
    }

    /**
     * 删除物流公司
     * @param int $id
     * @return mixed
     */
    public function delete(int $id)
    {
        $this->services->deleteCompany($id);
        return app('json')->success('删除成功');
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
     * 修改展示
     * @param int $id
     * @param int $isShow
     * @return mixed
     */
    public function setIsShow(int $id, int $isShow)
    {
        $this->services->setIsShow($id, $isShow);
        return app('json')->success('修改成功');
    }

    /**
     * 排序
     * @return mixed
     */
    public function sort()
    {
        [$id, $sort] = $this->request->postMore([
            ['id', 0],
            ['sort', 0],
        ], true);
        $this->services->setSort((int)$id, (int)$sort);
        return app('json')->success('排序成功');
    }

    /**
     * 获取驱动列表（供下拉选择）
     * @return mixed
     */
    public function driverOptions()
    {
        return app('json')->success($this->services->getDriverOptions());
    }
}
