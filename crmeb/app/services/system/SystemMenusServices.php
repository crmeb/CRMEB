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

namespace app\services\system;

use app\dao\system\SystemMenusDao;
use app\services\BaseServices;
use app\services\system\admin\SystemRoleServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use crmeb\utils\Arr;

/**
 * 权限菜单
 * Class SystemMenusServices
 * @package app\services\system
 * @method save(array $data) 保存数据
 * @method get(int $id, ?array $field = []) 获取数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 * @method getSearchList() 主页搜索
 * @method getColumn(array $where, string $field, ?string $key = '') 主页搜索
 * @method getVisitName(string $rule) 根据访问地址获得菜单名
 */
class SystemMenusServices extends BaseServices
{

    /**
     * 初始化
     * SystemMenusServices constructor.
     * @param SystemMenusDao $dao
     */
    public function __construct(SystemMenusDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取菜单没有被修改器修改的数据
     * @param $menusList
     * @return array
     */
    public function getMenusData($menusList)
    {
        $data = [];
        foreach ($menusList as $item) {
            $data[] = $item->getData();
        }
        return $data;
    }

    /**
     * 获取后台权限菜单和权限
     * @param $rouleId
     * @param int $level
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenusList($rouleId, int $level)
    {
        /** @var SystemRoleServices $systemRoleServices */
        $systemRoleServices = app()->make(SystemRoleServices::class);
        $rules = $systemRoleServices->getRoleArray(['status' => 1, 'id' => $rouleId], 'rules');
        $rulesStr = Arr::unique($rules);
        $menusList = $this->dao->getMenusRoule(['route' => $level ? $rulesStr : '']);
        $unique = $this->dao->getMenusUnique(['unique' => $level ? $rulesStr : '']);
        return [Arr::getMenuIviewList($this->getMenusData($menusList)), $unique];
    }

    /**
     * 获取后台菜单树型结构列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where)
    {
        $menusList = $this->dao->getMenusList($where);
        $menusList = $this->getMenusData($menusList);
        return get_tree_children($menusList);
    }

    /**
     * 获取form表单所需要的所要的菜单列表
     * @return array[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getFormSelectMenus()
    {
        $menuList = $this->dao->getMenusRoule(['is_del' => 0], ['id', 'pid', 'menu_name']);
        $list = sort_list_tier($this->getMenusData($menuList), '0', 'pid', 'id');
        $menus = [['value' => 0, 'label' => '顶级按钮']];
        foreach ($list as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['menu_name']];
        }
        return $menus;
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getFormCascaderMenus(int $value = 0)
    {
        $menuList = $this->dao->getMenusRoule(['is_del' => 0], ['id as value', 'pid', 'menu_name as label']);
        $menuList = $this->getMenusData($menuList);
        if ($value) {
            $data = get_tree_value($menuList, $value);
        } else {
            $data = [];
        }
        return [get_tree_children($menuList, 'children', 'value'), array_reverse($data)];
    }

    /**
     * 创建权限规格生表单
     * @param array $formData
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createMenusForm(array $formData = [])
    {
        $field[] = Form::input('menu_name', '按钮名称', $formData['menu_name'] ?? '')->required('按钮名称必填');
//        $field[] = Form::select('pid', '父级id', $formData['pid'] ?? 0)->setOptions($this->getFormSelectMenus())->filterable(1);
        $field[] = Form::input('menu_path', '路由名称', $formData['menu_path'] ?? '')->placeholder('请输入前台跳转路由地址')->required('请填写前台路由地址');
        $field[] = Form::input('unique_auth', '权限标识', $formData['unique_auth'] ?? '')->placeholder('不填写则后台自动生成');
        $params = $formData['params'] ?? '';
//        $field[] = Form::input('params', '参数', is_array($params) ? '' : $params)->placeholder('举例:a/123/b/234');
        $field[] = Form::frameInput('icon', '图标', $this->url('admin/widget.widgets/icon', ['fodder' => 'icon']), $formData['icon'] ?? '')->icon('md-add')->height('505px')->modal(['footer-hide' => true]);
        $field[] = Form::number('sort', '排序', (int)($formData['sort'] ?? 0))->precision(0);
        $field[] = Form::radio('auth_type', '类型', $formData['auth_type'] ?? 1)->options([['value' => 2, 'label' => '接口'], ['value' => 1, 'label' => '菜单(包含页面按钮)']]);
        $field[] = Form::radio('is_show', '状态', $formData['is_show'] ?? 1)->options([['value' => 0, 'label' => '关闭'], ['value' => 1, 'label' => '开启']]);
        $field[] = Form::radio('is_show_path', '是否为前端隐藏菜单', $formData['is_show_path'] ?? 0)->options([['value' => 1, 'label' => '是'], ['value' => 0, 'label' => '否']]);
        [$menuList, $data] = $this->getFormCascaderMenus((int)($formData['pid'] ?? 0));
        $field[] = Form::cascader('menu_list', '父级id', $data)->data($menuList)->filterable(true);
        return $field;
    }

    /**
     * 新增权限表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createMenus()
    {
        return create_form('添加权限', $this->createMenusForm(), $this->url('/setting/save'));
    }

    /**
     * 修改权限菜单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateMenus(int $id)
    {
        $menusInfo = $this->dao->get($id);
        if (!$menusInfo) {
            throw new AdminException(100026);
        }
        return create_form('修改权限', $this->createMenusForm($menusInfo->getData()), $this->url('/setting/update/' . $id), 'PUT');
    }

    /**
     * 获取一条数据
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        $menusInfo = $this->dao->get($id);
        if (!$menusInfo) {
            throw new AdminException(100026);
        }
        $menu = $menusInfo->getData();
        $menu['pid'] = (int)$menu['pid'];
        $menu['auth_type'] = (int)$menu['auth_type'];
        $menu['is_header'] = (int)$menu['is_header'];
        $menu['is_show'] = (int)$menu['is_show'];
        $menu['is_show_path'] = (int)$menu['is_show_path'];
        if (!$menu['path']) {
            [$menuList, $data] = $this->getFormCascaderMenus($menu['pid']);
            $menu['path'] = $data;
        } else {
            $menu['path'] = explode('/', $menu['path']);
            if (is_array($menu['path'])) {
                $menu['path'] = array_map(function ($item) {
                    return (int)$item;
                }, $menu['path']);
            }
        }
        return $menu;
    }

    /**
     * 删除菜单
     * @param int $id
     * @return mixed
     */
    public function delete(int $id)
    {
        if ($this->dao->count(['pid' => $id])) {
            throw new AdminException(400613);
        }
        return $this->dao->delete($id);
    }

    /**
     * 获取添加身份规格
     * @param $roles
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenus($roles): array
    {
        $field = ['menu_name', 'pid', 'id'];
        $where = ['is_del' => 0];
        if (!$roles) {
            $menus = $this->dao->getMenusRoule($where, $field);
        } else {
            /** @var SystemRoleServices $service */
            $service = app()->make(SystemRoleServices::class);
            $roles = is_string($roles) ? explode(',', $roles) : $roles;
            $ids = $service->getRoleIds($roles);
            $menus = $this->dao->getMenusRoule(['rule' => $ids] + $where, $field);
        }
        return $this->tidyMenuTier(false, $menus);
    }

    /**
     * 组合菜单数据
     * @param bool $adminFilter
     * @param $menusList
     * @param int $pid
     * @param array $navList
     * @return array
     */
    public function tidyMenuTier(bool $adminFilter = false, $menusList, int $pid = 0, array $navList = []): array
    {
        foreach ($menusList as $k => $menu) {
            $menu = $menu->getData();
            $menu['title'] = $menu['menu_name'];
            unset($menu['menu_name']);
            if ($menu['pid'] == $pid) {
                unset($menusList[$k]);
                $menu['children'] = $this->tidyMenuTier($adminFilter, $menusList, $menu['id']);
                if ($pid == 0 && !count($menu['children'])) continue;
                if ($menu['children']) $menu['expand'] = true;
                $navList[] = $menu;
            }
        }
        return $navList;
    }
}
