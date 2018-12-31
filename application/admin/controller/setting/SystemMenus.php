<?php

namespace app\admin\controller\setting;

use service\FormBuilder as Form;
use traits\CurdControllerTrait;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use app\admin\model\system\SystemMenus as MenusModel;
use app\admin\controller\AuthController;

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
        $pid = $this->request->param('pid')?$this->request->param('pid'):0;
        $params = Util::getMore([
            ['is_show',''],
//            ['access',''],
            ['keyword',''],
            ['pid',$pid]
        ],$this->request);
        $this->assign(MenusModel::getAdminPage($params));
        $this->assign(compact('params'));
        return $this->fetch();
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create($cid = 0)
    {
        $form = Form::create(Url::build('save'),[
            Form::input('menu_name','按钮名称')->required('按钮名称必填'),
            Form::select('pid','父级id',$cid)->setOptions(function(){
                $list = (Util::sortListTier(MenusModel::all()->toArray(),'顶级','pid','menu_name'));
                $menus = [['value'=>0,'label'=>'顶级按钮']];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['menu_name']];
                }
                return $menus;
            })->filterable(1),
            Form::select('module','模块名')->options([['label'=>'总后台','value'=>'admin']]),
            Form::input('controller','控制器名'),
            Form::input('action','方法名'),
            Form::input('params','参数')->placeholder('举例:a/123/b/234'),
            Form::frameInputOne('icon','图标',Url::build('admin/widget.widgets/icon',array('fodder'=>'icon')))->icon('ionic'),
            Form::number('sort','排序',0),
            Form::radio('is_show','是否菜单',1)->options([['value'=>0,'label'=>'隐藏'],['value'=>1,'label'=>'显示(菜单只显示三级)']]),
        ]);
        $form->setMethod('post')->setTitle('添加权限');
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'menu_name',
            'controller',
            ['module','admin'],
            'action',
            'icon',
            'params',
            ['pid',0],
            ['sort',0],
            ['is_show',0],
            ['access',1]],$request);
        if(!$data['menu_name']) return Json::fail('请输入按钮名称');
        MenusModel::set($data);
        return Json::successful('添加菜单成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $menu = MenusModel::get($id);
        if(!$menu) return Json::fail('数据不存在!');
        $form = Form::create(Url::build('update',array('id'=>$id)),[
            Form::input('menu_name','按钮名称',$menu['menu_name']),
            Form::select('pid','父级id',(string)$menu->getData('pid'))->setOptions(function()use($id){
                $list = (Util::sortListTier(MenusModel::where('id','<>',$id)->select()->toArray(),'顶级','pid','menu_name'));
                $menus = [['value'=>0,'label'=>'顶级按钮']];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['menu_name']];
                }
                return $menus;
            })->filterable(1),
            Form::select('module','模块名',$menu['module'])->options([['label'=>'总后台','value'=>'admin'],['label'=>'总后台1','value'=>'admin1']]),
            Form::input('controller','控制器名',$menu['controller']),
            Form::input('action','方法名',$menu['action']),
            Form::input('params','参数',MenusModel::paramStr($menu['params']))->placeholder('举例:a/123/b/234'),
            Form::frameInputOne('icon','图标',Url::build('admin/widget.widgets/icon',array('fodder'=>'icon')),$menu['icon'])->icon('ionic'),
            Form::number('sort','排序',$menu['sort']),
            Form::radio('is_show','是否菜单',$menu['is_show'])->options([['value'=>0,'label'=>'隐藏'],['value'=>1,'label'=>'显示(菜单只显示三级)']])
        ]);
        $form->setMethod('post')->setTitle('编辑权限');
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'menu_name',
            'controller',
            ['module','admin'],
            'action',
            'params',
            'icon',
            ['sort',0],
            ['pid',0],
            ['is_show',0],
            ['access',1]],$request);
        if(!$data['menu_name']) return Json::fail('请输入按钮名称');
        if(!MenusModel::get($id)) return Json::fail('编辑的记录不存在!');
        MenusModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!$id) return $this->failed('参数错误，请重新打开');
        $res = MenusModel::delMenu($id);
        if(!$res)
            return Json::fail(MenusModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

}
