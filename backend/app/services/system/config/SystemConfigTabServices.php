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

namespace app\services\system\config;


use app\dao\system\config\SystemConfigTabDao;
use crmeb\basic\BaseService;
use app\services\system\SystemMenusServices;
use crmeb\exceptions\AdminException;
use crmeb\utils\FormBuilder as Form;

/**
 * 系统配置分类
 * Class SystemConfigTabServices
 * @package app\services\system\config
 */
class SystemConfigTabServices extends BaseService
{
    /**
     * @var SystemConfigTabDao
     */
    protected $dao;

    /**
     * form表单句柄
     * @var FormBuilder
     */
    protected $builder;

    /**
     * SystemConfigTabServices constructor.
     * @param SystemConfigTabDao $dao
     */
    public function __construct(SystemConfigTabDao $dao, Form $builder)
    {
        $this->dao = $dao;
        $this->builder = $builder;  
    }

    /**
     * 系统设置头部分类读取
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigTab(int $pid)
    {
        $list = $this->dao->getConfigTabAll(['status' => 1, 'pid' => $pid], ['id', 'id as value', 'title as label', 'pid', 'icon', 'type'], $pid ? [] : [['type', '=', '0']]);
        return get_tree_children($list);
    }

    /**
     * 获取配置分类列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfgTabList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getConfgTabList($where, $page, $limit);
        $count = $this->dao->count($where);
        $menusValue = [];
        foreach ($list as $item) {
            $menusValue[] = $item->getData();
        }
        $list = get_tree_children($menusValue);
        $count = 0;
        return compact('list', 'count');
    }

    /**
     * 获取配置分类选择下拉树
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSelectForm()
    {
        $menuList = $this->dao->getConfigTabAll([], ['id', 'pid', 'title']);
        $list = sort_list_tier($menuList, 0, 'pid', 'id');
        $menus = [['value' => 0, 'label' => '顶级按钮']];
        foreach ($list as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['title']];
        }
        return $menus;
    }

    /**
     * 配置分类树形数据
     * @param $value
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/9/12
     */
    public function getConfigTabListForm($value)
    {
        $configTabList = $this->dao->getConfigTabAll([], ['id as value', 'pid', 'title as label']);
        if ($value) {
            $data = get_tree_value($configTabList, $value);
        } else {
            $data = [0];
        }
        $configTabList = get_tree_children($configTabList, 'children', 'value');
        array_unshift($configTabList, ['value' => 0, 'pid' => 0, 'label' => '顶级分类']);
        return [$configTabList, array_reverse($data)];
    }

    /**
     * 创建form表单
     * @param array $formData
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function createConfigTabForm(array $formData = [])
    {
        [$configTabList, $data1] = $this->getConfigTabListForm((int)($formData['pid'] ?? 0));
        $form[] = $this->builder->cascader('pid', '父级分类', $data1)->options($configTabList)->filterable(true)->props(['props' => ['multiple' => false, 'checkStrictly' => true, 'emitPath' => false]])->style(['width'=>'100%']);
        $form[] = $this->builder->input('title', '分类名称', $formData['title'] ?? '');
        $form[] = $this->builder->input('eng_title', '分类字段英文', $formData['eng_title'] ?? '');
        $form[] = $this->builder->frameInput('icon', '图标', $this->url(config('app.admin_prefix', 'admin') . '/widget.widgets/icon', ['fodder' => 'icon'], true), $formData['icon'] ?? '')->icon('el-icon-picture-outline')->height('560px')->props(['footer' => false]);
        $form[] = $this->builder->radio('type', '类型', $formData['type'] ?? 0)->options([
            ['value' => 0, 'label' => '系统'],
            ['value' => 3, 'label' => '其它']
        ]);
        // 编辑时：按 menu_path 或 stored menus_id 检查菜单是否存在，存在即关联
        $tabId = (int)($formData['id'] ?? 0);
        $menusId = 0;
        $selectedId = 0;
        if ($tabId > 0) {
            // 优先按 menu_path 查找（自动创建的菜单使用此格式）
            $found = app()->make(SystemMenusServices::class)->getOne(
                ['menu_path' => '/setting/system_config/' . $tabId],
                'id,pid'
            );
            // 未找到时，使用数据库中已存储的 menus_id 回退查找
            if (!$found && !empty($formData['menus_id'])) {
                $found = app()->make(SystemMenusServices::class)->getOne(
                    ['id' => (int)$formData['menus_id']],
                    'id,pid'
                );
            }
            if ($found) {
                $menusId = (int)$found['id'];
                $selectedId = (int)$found['pid'];
            }
        }
        [$menusList, $data2] = app()->make(SystemMenusServices::class)->getFormCascaderMenus($selectedId);

        // 创建菜单开关：0=不创建（默认），1=创建菜单；选择创建时联动显示父级菜单选择器
        $isCreateMenu = $menusId > 0 ? 1 : 0;
        $form[] = $this->builder->radio('is_create_menu', '创建菜单', $isCreateMenu)->options([
            ['value' => 0, 'label' => '不关联'],
            ['value' => 1, 'label' => '关联菜单'],
        ])->appendControl(1, [
            $this->builder->cascader('menus_id', '父级菜单', $data2)->options($menusList)->filterable(true)->props(['props' => ['multiple' => false, 'checkStrictly' => true, 'emitPath' => false]])->style(['width' => '100%']),
        ]);
        $form[] = $this->builder->radio('status', '状态', $formData['status'] ?? 1)->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        $form[] = $this->builder->number('sort', '排序', (int)($formData['sort'] ?? 0))->precision(0)->controls(false);
        return $form;
    }

    /**
     * 保存配置分类，自动创建关联菜单
     * @param array $data
     * @return mixed
     * @throws \Throwable
     */
    public function save(array $data)
    {
        // 是否创建关联菜单：0=不创建，1=创建
        $isCreateMenu = (int)($data['is_create_menu'] ?? 0);
        $raw = $data['menus_id'] ?? 0;
        if (is_array($raw)) {
            $raw = array_filter($raw, fn($v) => (int)$v > 0);
            $raw = $raw ? array_pop($raw) : 0;
        }
        $parentMenuId = (int)$raw;
        // 勾选了创建菜单，或实际选择了父级菜单（兼容开关未正确提交的情况），都执行创建
        $shouldCreateMenu = $isCreateMenu || $parentMenuId > 0;
        unset($data['is_create_menu']);
        $data['menus_id'] = 0;
        $tab = $this->dao->save($data);

        if ($shouldCreateMenu) {
            $tabData = $tab->toArray();
            $menuId = $this->syncMenu($tabData, $parentMenuId);
            $this->dao->update($tab->id, ['menus_id' => $menuId]);
        }

        return $tab;
    }

    /**
     * 更新配置分类，同步更新或重建关联菜单
     * @param $id
     * @param array $data
     * @param string|null $key
     * @return mixed
     * @throws \Throwable
     */
    public function update($id, array $data, ?string $key = null)
    {
        $tab = $this->dao->get($id);
        if (!$tab) {
            throw new AdminException('数据不存在');
        }
        $tabData = $tab->toArray();
        $oldMenuId = (int)($tabData['menus_id'] ?? 0);
        $isCreateMenu = (int)($data['is_create_menu'] ?? 0);
        $raw = $data['menus_id'] ?? 0;
        if (is_array($raw)) {
            $raw = array_filter($raw, fn($v) => (int)$v > 0);
            $raw = $raw ? array_pop($raw) : 0;
        }
        $newParentMenuId = (int)$raw;
        // 勾选了创建菜单，或实际选择了父级菜单（兼容开关未正确提交的情况），都执行创建/更新
        $shouldCreateMenu = $isCreateMenu || $newParentMenuId > 0;

        // 先更新配置分类本身（排除 menus_id、is_create_menu，后面单独处理）
        $saveData = $data;
        unset($saveData['menus_id'], $saveData['is_create_menu']);
        $result = $this->dao->update($id, $saveData);

        /** @var SystemMenusServices $menusServices */
        $menusServices = app()->make(SystemMenusServices::class);

        // 查询旧菜单的父级，用于判断用户是否切换了目录
        $oldParentMenuId = 0;
        if ($oldMenuId > 0) {
            $oldMenu = $menusServices->get($oldMenuId, ['id', 'pid']);
            if ($oldMenu) {
                $oldParentMenuId = (int)$oldMenu->getData('pid');
            }
        }

        if ($shouldCreateMenu) {
            if ($oldMenuId > 0) {
                // 关联菜单 → 更新现有菜单（名称/排序/父级）
                $updateData = [
                    'menu_name' => $data['title'] ?? $tabData['title'],
                    'sort'      => (int)($data['sort'] ?? $tabData['sort'] ?? 0),
                ];
                if ($newParentMenuId !== $oldParentMenuId) {
                    $updateData['pid'] = $newParentMenuId;
                }
                $menusServices->update($oldMenuId, $updateData);
            } else {
                // 没有旧菜单 ID → 利用 syncMenu 按 menu_path 全局查找并更新/新建
                $mergedData = array_merge($tabData, $saveData);
                $mergedData['id'] = $id;
                $menuId = $this->syncMenu($mergedData, $newParentMenuId);
                $this->dao->update($id, ['menus_id' => $menuId]);
            }
        } elseif ($oldMenuId > 0) {
            // 不关联 → 删除旧菜单并解除关联
            $menusServices->delete($oldMenuId);
            $this->dao->update($id, ['menus_id' => 0]);
        }

        return $result;
    }

    /**
     * 删除配置分类，同时删除关联菜单（仅自动创建的菜单）
     * @param $id
     * @param string|null $key
     * @return mixed
     */
    public function delete($id, ?string $key = null)
    {
        $tab = $this->dao->get($id);
        if ($tab) {
            $tabData = $tab->toArray();
            $menuId = (int)($tabData['menus_id'] ?? 0);
            if ($menuId > 0) {
                /** @var SystemMenusServices $menusServices */
                $menusServices = app()->make(SystemMenusServices::class);
                $menu = $menusServices->get($menuId, ['id', 'mark']);
                // 仅自动创建的菜单（mark 有标记）才随配置分类删除
                if ($menu && strpos($menu->getData('mark'), 'auto_config_tab_menu:') === 0) {
                    $menusServices->delete($menuId);
                }
            }
        }
        return $this->dao->delete($id);
    }

    /**
     * 构建自动创建的菜单数据
     * @param array $tabData
     * @param int $parentMenuId
     * @return array
     */
    protected function buildMenuData(array $tabData, int $parentMenuId): array
    {
        $title = $tabData['title'] ?? '';
        $tabId = (int)($tabData['id'] ?? 0);
        return [
            'pid'           => $parentMenuId,
            'icon'          => $tabData['icon'] ?? '',
            'menu_name'     => $title,
            'module'        => 'admin',
            'controller'    => '',
            'action'        => '',
            'api_url'       => '',
            'methods'       => '',
            'params'        => '[]',
            'sort'          => (int)($tabData['sort'] ?? 0),
            'is_show'       => 1,
            'is_show_path'  => 1,
            'access'        => 1,
            'menu_path'     => '/setting/system_config/' . $tabId,
            'path'          => '',
            'auth_type'     => 1,
            'header'        => '',
            'is_header'     => 0,
            'unique_auth'   => '',
            'is_del'        => 0,
            'mark'          => 'auto_config_tab_menu:' . $tabId,
        ];
    }

    /**
     * 同步（创建或更新）关联菜单
     * 按 menu_path 全局查找（含跨父级迁移），有则更新，无则新增
     * @param array $tabData 配置分类数据（需含 id/title/icon/sort）
     * @param int $parentMenuId 目标父级菜单 ID
     * @return int 菜单 ID
     */
    protected function syncMenu(array $tabData, int $parentMenuId): int
    {
        $menuData = $this->buildMenuData($tabData, $parentMenuId);
        /** @var SystemMenusServices $menusServices */
        $menusServices = app()->make(SystemMenusServices::class);
        $existing = $menusServices->getOne(['menu_path' => $menuData['menu_path']]);
        if ($existing) {
            $menusServices->update($existing->id, $menuData);
            return $existing->id;
        }
        return $menusServices->save($menuData)->id;
    }

    /**
     * 添加配置分类表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm()
    {
        return create_form('添加配置分类', $this->createConfigTabForm(), $this->url('/setting/config_class'));
    }

    /**
     * 修改配置分类表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function updateForm(int $id)
    {
        $configTabInfo = $this->dao->get($id);
        if (!$configTabInfo) {
            throw new AdminException('数据不存在');
        }
        return create_form('编辑配置分类', $this->createConfigTabForm($configTabInfo->toArray()), $this->url('/setting/config_class/' . $id), 'PUT');
    }
}
