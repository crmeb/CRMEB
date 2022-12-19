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
use app\services\system\SystemMenusServices;
use think\facade\App;
use think\facade\Route;

/**
 * 菜单权限
 * Class SystemMenus
 * @package app\adminapi\controller\v1\setting
 */
class SystemMenus extends AuthController
{
    /**
     * SystemMenus constructor.
     * @param App $app
     * @param SystemMenusServices $services
     */
    public function __construct(App $app, SystemMenusServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
        $this->request->filter(['addslashes', 'trim']);
    }

    /**
     * 菜单展示列表
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['is_show', ''],
            ['keyword', ''],
        ]);
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {

        return app('json')->success($this->services->createMenus());
    }

    /**
     * 保存菜单权限
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->getMore([
            ['menu_name', ''],
            ['controller', ''],
            ['module', 'admin'],
            ['action', ''],
            ['icon', ''],
            ['params', ''],
            ['path', []],
            ['menu_path', ''],
            ['api_url', ''],
            ['methods', ''],
            ['unique_auth', ''],
            ['header', ''],
            ['is_header', 0],
            ['pid', 0],
            ['sort', 0],
            ['auth_type', 0],
            ['access', 1],
            ['is_show', 0],
            ['is_show_path', 0],
        ]);

        if (!$data['menu_name'])
            return app('json')->fail(400198);
        $data['path'] = implode('/', $data['path']);
        if ($this->services->save($data)) {
            return app('json')->success(100021);
        } else {
            return app('json')->fail(100022);
        }
    }

    /**
     * 获取一条菜单权限信息
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {

        if (!$id) {
            return app('json')->fail(100026);
        }
        return app('json')->success($this->services->find((int)$id));
    }

    /**
     * 修改菜单权限表单获取
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        return app('json')->success($this->services->updateMenus((int)$id));
    }

    /**
     * 修改菜单
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        if (!$id || !($menu = $this->services->get($id)))
            return app('json')->fail(100026);
        $data = $this->request->postMore([
            'menu_name',
            'controller',
            ['module', 'admin'],
            'action',
            'params',
            ['icon', ''],
            ['menu_path', ''],
            ['api_url', ''],
            ['methods', ''],
            ['unique_auth', ''],
            ['path', []],
            ['sort', 0],
            ['pid', 0],
            ['is_header', 0],
            ['header', ''],
            ['auth_type', 0],
            ['access', 1],
            ['is_show', 0],
            ['is_show_path', 0],
        ]);
        if (!$data['menu_name'])
            return app('json')->fail(400198);
        $data['path'] = implode('/', $data['path']);
        if ($this->services->update($id, $data))
            return app('json')->success(100001);
        else
            return app('json')->fail(100007);
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }

        if (!$this->services->delete((int)$id)) {
            return app('json')->fail(100008);
        } else {
            return app('json')->success(100002);
        }
    }

    /**
     * 显示和隐藏
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }

        [$show] = $this->request->postMore([['is_show', 0]], true);

        if ($this->services->update($id, ['is_show' => $show])) {
            return app('json')->success(100001);
        } else {
            return app('json')->fail(100007);
        }
    }

    /**
     * 获取菜单数据
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function menus()
    {
        [$menus, $unique] = $this->services->getMenusList($this->adminInfo['roles'], (int)$this->adminInfo['level']);
        return app('json')->success(['menus' => $menus, 'unique' => $unique]);
    }

    /**
     * 获取接口列表
     * @return array
     */
    public function ruleList()
    {
        //获取所有的路由
        $ruleList = Route::getRuleList();
        $menuApiList = $this->services->getColumn(['auth_type' => 2, 'is_del' => 0], "concat(`api_url`,'_',lower(`methods`)) as rule");
        if ($menuApiList) $menuApiList = array_column($menuApiList, 'rule');
        $list = [];
        foreach ($ruleList as $item) {
            $item['rule'] = str_replace('adminapi/', '', $item['rule']);
            if (!in_array($item['rule'] . '_' . $item['method'], $menuApiList)) {
                $item['real_name'] = $item['option']['real_name'] ?? '';
                unset($item['option']);
                $item['method'] = strtoupper($item['method']);
                $list[] = $item;
            }
        }
        return app('json')->success($list);
    }
}
