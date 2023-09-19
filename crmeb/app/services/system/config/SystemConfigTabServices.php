<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\system\config;


use app\dao\system\config\SystemConfigTabDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;

/**
 * 系统配置分类
 * Class SystemConfigTabServices
 * @package app\services\system\config
 * @method save(array $data) 写入数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 * @method delete($id, ?string $key = null) 删除数据
 */
class SystemConfigTabServices extends BaseServices
{
    /**
     * SystemConfigTabServices constructor.
     * @param SystemConfigTabDao $dao
     */
    public function __construct(SystemConfigTabDao $dao)
    {
        $this->dao = $dao;
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
        [$configTabList, $data] = $this->getConfigTabListForm((int)($formData['pid'] ?? 0), 3);
        $form[] = Form::cascader('pid', '父级分类', $data)->options($configTabList)->filterable(true)->props(['props' => ['multiple' => false, 'checkStrictly' => true, 'emitPath' => false]])->style(['width'=>'100%']);
        $form[] = Form::input('title', '分类名称', $formData['title'] ?? '');
        $form[] = Form::input('eng_title', '分类字段英文', $formData['eng_title'] ?? '');
        $form[] = Form::frameInput('icon', '图标', $this->url(config('app.admin_prefix', 'admin') . '/widget.widgets/icon', ['fodder' => 'icon'], true), $formData['icon'] ?? '')->icon('el-icon-picture-outline')->height('560px')->props(['footer' => false]);
        $form[] = Form::radio('type', '类型', $formData['type'] ?? 0)->options([
            ['value' => 0, 'label' => '系统'],
            ['value' => 3, 'label' => '其它']
        ]);
        $form[] = Form::radio('status', '状态', $formData['status'] ?? 1)->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        $form[] = Form::number('sort', '排序', (int)($formData['sort'] ?? 0))->precision(0)->controls(false);
        return $form;
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
            throw new AdminException(100026);
        }
        return create_form('编辑配置分类', $this->createConfigTabForm($configTabInfo->toArray()), $this->url('/setting/config_class/' . $id), 'PUT');
    }
}
