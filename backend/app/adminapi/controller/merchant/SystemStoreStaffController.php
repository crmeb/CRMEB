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
namespace app\adminapi\controller\merchant;

use app\services\system\store\SystemStoreServices;
use app\services\system\store\SystemStoreStaffServices;
use think\facade\App;
use app\adminapi\controller\AuthController;

/**
 * 店员
 * Class SystemStoreStaff
 * @package app\adminapi\controller\merchant
 */
class SystemStoreStaffController extends AuthController
{
    /**
     * @var SystemStoreStaffServices
     */
    protected $services;


    /**
     * 构造方法
     * SystemStoreStaff constructor.
     * @param App $app
     * @param SystemStoreStaffServices $services
     */
    public function __construct(App $app, SystemStoreStaffServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取店员列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            [['store_id', 'd'], 0],
        ]);
        return app('json')->success($this->services->getStoreStaffList($where));
    }

    /**
     * 门店列表
     * @param SystemStoreServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function storeList(SystemStoreServices $services)
    {
        return app('json')->success($services->getStore());
    }

    /**
     * 店员新增表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create()
    {
        return app('json')->success($this->services->createForm());
    }

    /**
     * 店员修改表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        [$id] = $this->request->getMore([
            [['id', 'd'], 0],
        ], true);
        return app('json')->success($this->services->updateForm($id));
    }

    /**
     * 保存店员信息
     * @param int $id
     * @return mixed
     */
    public function save($id = 0)
    {
        $data = $this->request->postMore([
            ['image', ''],
            ['uid', 0],
            ['avatar', ''],
            ['store_id', ''],
            ['staff_name', ''],
            ['phone', ''],
            ['verify_status', 1],
            ['status', 1],
        ]);
        if (!$id) {
            if ($data['image'] === '') {
                return app('json')->fail('请选择用户');
            }
            if ($this->services->count(['uid' => $data['image']['uid']])) {
                return app('json')->fail('添加的核销员用户已存在');
            }
            $data['uid'] = $data['image']['uid'];
            $data['avatar'] = $data['image']['image'];
        } else {
            $data['avatar'] = $data['image'];
        }
        if ($data['uid'] == 0) {
            return app('json')->fail('请选择用户');
        }
        if (empty($data['store_id'])) {
            return app('json')->fail('请选择所属门店');
        }
        if ($data['staff_name'] === ''){
            return app('json')->fail('请填写核销员名称');
        }
        if ($data['phone'] === ''){
            return app('json')->fail('请填写核销员电话');
        }
        unset($data['image']);
        if ($id) {
            $res = $this->services->update($id, $data);
            if ($res) {
                return app('json')->success('修改成功');
            } else {
                return app('json')->fail('修改失败');
            }
        } else {
            $data['add_time'] = time();
            $res = $this->services->save($data);
            if ($res) {
                return app('json')->success('核销员添加成功');
            } else {
                return app('json')->fail('核销员添加失败');
            }
        }
    }

    /**
     * 设置单个店员是否开启
     * @param string $is_show
     * @param string $id
     * @return mixed
     */
    public function setShow($id, $is_show)
    {
        if ($is_show === '' || $is_show === null || empty($id)) {
            app('json')->fail('参数错误');
        }
        $res = $this->services->update($id, ['status' => (int)$is_show]);
        if ($res) {
            return app('json')->success('设置成功');
        } else {
            return app('json')->fail('设置失败');
        }
    }

    /**
     * 删除店员
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail('参数错误');
        if (!$this->services->delete($id))
            return app('json')->fail('删除失败');
        else
            return app('json')->success('删除成功');
    }
}
