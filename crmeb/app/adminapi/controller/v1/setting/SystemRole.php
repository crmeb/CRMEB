<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\setting;

use app\adminapi\controller\AuthController;
use app\services\system\admin\SystemAdminServices;
use app\services\system\admin\SystemRoleServices;
use app\services\system\SystemMenusServices;
use think\facade\App;

/**
 * Class SystemRole
 * @package app\adminapi\controller\v1\setting
 */
class SystemRole extends AuthController
{
    /**
     * SystemRole constructor.
     * @param App $app
     * @param SystemRoleServices $services
     */
    public function __construct(App $app, SystemRoleServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['role_name', ''],
        ]);
        $where['level'] = $this->adminInfo['level'] + 1;
        return app('json')->success($this->services->getRoleList($where));
    }

    /**
     * 显示创建资源表单页
     * @param SystemMenusServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create(SystemMenusServices $services)
    {
        $menus = $services->getmenus($this->adminInfo['level'] == 0 ? [] : $this->adminInfo['roles']);
        return app('json')->success(compact('menus'));
    }

    /**
     * 保存新建的资源
     *
     * @return \think\Response
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            'role_name',
            ['status', 0],
            ['checked_menus', [], '', 'rules']
        ]);
        if (!$data['role_name']) return app('json')->fail(400220);
        if (!is_array($data['rules']) || !count($data['rules']))
            return app('json')->fail(400221);
        $data['rules'] = implode(',', $data['rules']);
        if ($id) {
            if (!$this->services->update($id, $data)) return app('json')->fail(100007);
            \think\facade\Cache::clear();
            return app('json')->success(100001);
        } else {
            $data['level'] = $this->adminInfo['level'] + 1;
            if (!$this->services->save($data)) return app('json')->fail(400223);
            \think\facade\Cache::clear();
            return app('json')->success(400222);
        }
    }

    /**
     * 显示编辑资源表单页
     * @param SystemMenusServices $services
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(SystemMenusServices $services, $id)
    {
        $role = $this->services->get($id);
        if (!$role) {
            return app('json')->fail(100100);
        }
        $menus = $services->getMenus($this->adminInfo['level'] == 0 ? [] : $this->adminInfo['roles']);
        return app('json')->success(['role' => $role->toArray(), 'menus' => $menus]);
    }

    /**
     * 删除指定资源
     * @param SystemAdminServices $adminServices
     * @param $id
     * @return mixed
     */
    public function delete(SystemAdminServices $adminServices, $id)
    {
        if ($adminServices->checkRoleUse($id)) {
            return app('json')->fail(400754);
        }
        if (!$this->services->delete($id))
            return app('json')->fail(100008);
        else {
            \think\facade\Cache::clear();
            return app('json')->success(100002);
        }
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        $role = $this->services->get($id);
        if (!$role) {
            return app('json')->fail(400199);
        }
        $role->status = $status;
        if ($role->save()) {
            \think\facade\Cache::clear();
            return app('json')->success(100001);
        } else {
            return app('json')->fail(100007);
        }
    }
}
