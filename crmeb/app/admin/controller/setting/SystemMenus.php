<?php

namespace app\admin\controller\setting;

use app\admin\controller\AuthController;
use crmeb\traits\CurdControllerTrait;
use think\facade\Route as Url;
use app\admin\model\system\SystemMenus as MenusModel;
use crmeb\services\{FormBuilder as Form, UtilService as Util, JsonService as Json};

/**
 * 菜单管理控制器
 * Class SystemMenus
 * @package app\admin\controller\system
 */
class SystemMenus extends AuthController
{
    use CurdControllerTrait;

    public $bindModel = MenusModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $pid = $this->request->param('pid') ? $this->request->param('pid') : 0;
        $params = Util::getMore([
            ['is_show', ''],
//            ['access',''],
            ['keyword', ''],
            ['pid', $pid]
        ], $this->request);
        $this->assign(MenusModel::getAdminPage($params));
        $addurl = Url::buildUrl('create', ['cid' => input('pid')]);
        $this->assign(compact('params', 'addurl'));
        return $this->fetch();
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create($cid = 0)
    {
        $field = [];
        $field[] = Form::input('menu_name', '按钮名称')->required('按钮名称必填');
        $field[] = Form::select('pid', '父级id', $cid)->setOptions(function () {
            $menuList = MenusModel::field(['id', 'pid', 'menu_name'])->order('sort DESC,id ASC')->select()->toArray();
            $list = sort_list_tier($menuList, '顶级', 'pid', 'menu_name');
            $menus = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['menu_name']];
            }
            return $menus;
        })->filterable(1);
        $field[] = Form::select('module', '模块名')->options([['label' => '总后台', 'value' => 'admin']]);
        if ($cid) $controller = MenusModel::where('id', $cid)->value('controller') ?: '';
        else $controller = '';
        $field[] = Form::input('controller', '控制器名', $controller);
        if (!empty($controller)) {
            $controller = preg_replace_callback('/([.]+([a-z]{1}))/i', function ($matches) {
                return '\\' . strtoupper($matches[2]);
            }, $controller);
            if (class_exists('\app\admin\controller\\' . $controller)) {
                $list = get_this_class_methods('\app\admin\controller\\' . $controller);

                $field[] = Form::select('action', '方法名')->setOptions(function () use ($list) {
                    $menus = [['value' => 0, 'label' => '默认函数']];
                    foreach ($list as $menu) {
                        $menus[] = ['value' => $menu, 'label' => $menu];
                    }
                    return $menus;
                })->filterable(1);
            } else {
                $field[] = Form::input('action', '方法名');
            }
        } else {
            $field[] = Form::input('action', '方法名');
        }
        $field[] = Form::input('params', '参数')->placeholder('举例:a/123/b/234');
        $field[] = Form::frameInputOne('icon', '图标', Url::buildUrl('admin/widget.widgets/icon', array('fodder' => 'icon')))->icon('ionic')->height('500px');
        $field[] = Form::number('sort', '排序', 0);
        $field[] = Form::radio('is_show', '是否菜单', 0)->options([['value' => 0, 'label' => '隐藏'], ['value' => 1, 'label' => '显示(菜单只显示三级)']]);
        $form = Form::make_post_form('添加权限', $field, Url::buildUrl('save'), 3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        $data = Util::postMore([
            'menu_name',
            'controller',
            ['module', 'admin'],
            'action',
            'icon',
            'params',
            ['pid', 0],
            ['sort', 0],
            ['is_show', 0],
            ['access', 1]]);
        if (!$data['menu_name']) return Json::fail('请输入按钮名称');
        MenusModel::create($data);
        return Json::successful('添加菜单成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $menu = MenusModel::get($id);
        if (!$menu) return Json::fail('数据不存在!');
        $field = [];
        $field[] = Form::input('menu_name', '按钮名称', $menu['menu_name']);
        $field[] = Form::select('pid', '父级id', (string)$menu->getData('pid'))->setOptions(function () use ($id) {
            $menuList = MenusModel::field(['id', 'pid', 'menu_name'])->order('sort DESC,id ASC')->select()->toArray();
            $list = sort_list_tier($menuList, '顶级', 'pid', 'menu_name');
            $menus = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['menu_name']];
            }
            return $menus;
        })->filterable(1);
        $field[] = Form::select('module', '模块名', $menu['module'])->options([['label' => '总后台', 'value' => 'admin']]);
        $field[] = Form::input('controller', '控制器名', $menu['controller']);
        if (!empty($menu['controller'])) {
            $controller = preg_replace_callback('/([.]+([a-z]{1}))/i', function ($matches) {
                return '\\' . strtoupper($matches[2]);
            }, $menu['controller']);
            if (class_exists('\app\admin\controller\\' . $controller)) {
                $list = get_this_class_methods('\app\admin\controller\\' . $controller);

                $field[] = Form::select('action', '方法名', (string)$menu->getData('action'))->setOptions(function () use ($list) {
                    $menus = [['value' => 0, 'label' => '默认函数']];
                    foreach ($list as $menu) {
                        $menus[] = ['value' => $menu, 'label' => $menu];
                    }
                    return $menus;
                })->filterable(1);
            } else {
                $field[] = Form::input('action', '方法名', $menu['action']);
            }
        } else {
            $field[] = Form::input('action', '方法名');
        }
        $field[] = Form::input('params', '参数', MenusModel::paramStr($menu['params']))->placeholder('举例:a/123/b/234');
        $field[] = Form::frameInputOne('icon', '图标', Url::buildUrl('admin/widget.widgets/icon', array('fodder' => 'icon')), $menu['icon'])->icon('ionic')->height('500px');
        $field[] = Form::number('sort', '排序', $menu['sort']);
        $field[] = Form::radio('is_show', '是否菜单', $menu['is_show'])->options([['value' => 0, 'label' => '隐藏'], ['value' => 1, 'label' => '显示(菜单只显示三级)']]);
        $form = Form::make_post_form('添加权限', $field, Url::buildUrl('update', array('id' => $id)), 3);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     *
     * @param $id
     */
    public function update($id)
    {
//        $this->request->filter('htmlspecialchars');
        $data = Util::postMore([
            'menu_name',
            ['controller', '', 'htmlspecialchars'],
            ['module', 'admin'],
            'action',
            'params',
            'icon',
            ['sort', 0],
            ['pid', 0],
            ['is_show', 0],
            ['access', 1]]);
        if (!$data['menu_name']) return Json::fail('请输入按钮名称');
        if (!MenusModel::get($id)) return Json::fail('编辑的记录不存在!');
        MenusModel::edit($data, $id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return $this->failed('参数错误，请重新打开');
        $res = MenusModel::delMenu($id);
        if (!$res)
            return Json::fail(MenusModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

}
