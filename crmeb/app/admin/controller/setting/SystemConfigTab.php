<?php

namespace app\admin\controller\setting;

use think\facade\Route as Url;
use app\admin\controller\AuthController;
use crmeb\services\{
    FormBuilder as Form, UtilService as Util, JsonService as Json
};
use app\admin\model\system\{
    SystemConfig as ConfigModel, SystemConfigTab as ConfigTabModel
};

/**
 * 配置分类控制器
 * Class SystemConfigTab
 * @package app\admin\controller\system
 */
class SystemConfigTab extends AuthController
{
    /** 定义配置分类,需要添加分类可以手动添加
     * @return array
     */
    public function getConfigType()
    {
        return [
            ['value' => 0, 'label' => '系统']
            , ['value' => 1, 'label' => '应用']
            , ['value' => 2, 'label' => '支付']
            , ['value' => 3, 'label' => '其它']
        ];
    }

    /**
     * 子子段
     * @return mixed|\think\response\Json
     */
    public function sonconfigtab()
    {
        $tab_id = input('tab_id');
        if (!$tab_id) return Json::fail('参数错误');
        $this->assign('tab_id', $tab_id);
        $list = ConfigModel::getAll($tab_id, 2);
        foreach ($list as $k => $v) {
            $list[$k]['value'] = json_decode($v['value'], true) ?: '';
            if ($v['type'] == 'radio' || $v['type'] == 'checkbox') {
                $list[$k]['value'] = ConfigTabModel::getRadioOrCheckboxValueInfo($v['menu_name'], $v['value']);
            }
            if ($v['type'] == 'upload' && !empty($v['value'])) {
                if ($v['upload_type'] == 1 || $v['upload_type'] == 3) $list[$k]['value'] = explode(',', $v['value']);
            }
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 基础配置
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['status', ''],
            ['title', ''],
            ['pid', 0],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(ConfigTabModel::getSystemConfigTabPage($where));
        return $this->fetch();
    }


    /**
     * 添加配置分类
     * @return mixed
     */
    public function create()
    {
        $field = [];
        $field[] = Form::select('pid', '父级分类', 0)->setOptions(function () {
            $menuList = ConfigTabModel::field(['id', 'pid', 'title'])->select()->toArray();//var_dump($menuList);
            $list = sort_list_tier($menuList, '顶级', 'pid', 'id');//var_dump($list);
            $menus = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['title']];
            }
            return $menus;
        })->filterable(1);
        $field[] = Form::input('title', '分类名称');
        $field[] = Form::input('eng_title', '分类字段英文');
        $field[] = Form::frameInputOne('icon', '图标', Url::buildUrl('admin/widget.widgets/icon', array('fodder' => 'icon')))->icon('ionic')->height('500px');
        $field[] = Form::radio('type', '类型', 0)->options(self::getConfigType());
        $field[] = Form::radio('status', '状态', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);
        $field[] = Form::radio('type', '类型', 0)->options(self::getConfigType());
        $field[] = Form::number('sort', '排序', 0);
        $form = Form::make_post_form('添加分类配置', $field, Url::buildUrl('save'), 3);
        $form->setMethod('post')->setTitle('添加分类配置');
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存分类名称
     */
    public function save()
    {
        $data = Util::postMore([
            'eng_title',
            'status',
            'title',
            'pid',
            'icon',
            'type',
            ['sort', 0]
        ]);
        if (!$data['title']) return Json::fail('请输入按钮名称');
        ConfigTabModel::create($data);
        return Json::successful('添加菜单成功!');
    }

    /**
     * 修改分类
     * @param $id
     * @return string|void
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit($id)
    {
        $menu = ConfigTabModel::get($id)->getData();
        if (!$menu) return Json::fail('数据不存在!');
        $form = Form::create(Url::buildUrl('update', array('id' => $id)), [
            Form::select('pid', '父级分类', (string)$menu['pid'])->setOptions(function () {
                $menuList = ConfigTabModel::field(['id', 'pid', 'title'])->select()->toArray();
                $list = sort_list_tier($menuList, '顶级', 'pid', 'id');
                $options = [['value' => 0, 'label' => '顶级按钮']];
                foreach ($list as $option) {
                    $options[] = ['value' => $option['id'], 'label' => $option['html'] . $option['title']];
                }
                return $options;
            })->filterable(1),
            Form::input('title', '分类名称', $menu['title']),
            Form::input('eng_title', '分类字段英文', $menu['eng_title']),
            Form::frameInputOne('icon', '图标', Url::buildUrl('admin/widget.widgets/icon', array('fodder' => 'icon')), $menu['icon'])->icon('ionic')->height('500px'),
            Form::radio('type', '类型', $menu['type'])->options(self::getConfigType()),
            Form::radio('status', '状态', $menu['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]),
            Form::number('sort', '排序', $menu['sort'] ?? 0),
        ]);
        $form->setMethod('post')->setTitle('添加分类配置');
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * @param $id
     */
    public function update($id)
    {
        $data = Util::postMore(['title', 'pid', 'status', 'eng_title', 'icon', 'type','sort']);
        if (!$data['title']) return Json::fail('请输入分类昵称');
        if (!$data['eng_title']) return Json::fail('请输入分类字段');
        if (!ConfigTabModel::get($id)) return Json::fail('编辑的记录不存在!');
        ConfigTabModel::edit($data, $id);
        return Json::successful('修改成功!');
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        if (!$id) return Json::fail('参数有误！');
        if (ConfigTabModel::be(['pid' => $id])) return Json::fail('有子分类，不能直接删除');
        if (!ConfigTabModel::del($id))
            return Json::fail(ConfigTabModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}
