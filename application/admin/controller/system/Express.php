<?php

namespace app\admin\controller\system;

use service\FormBuilder as Form;
use service\UtilService as Util;
use service\JsonService as Json;
use think\Request;
use think\Url;
use app\admin\model\system\Express as ExpressModel;
use app\admin\controller\AuthController;

/**
 * 物流公司管理控制器
 * Class SystemMenus
 * @package app\admin\controller\system
 */
class Express extends AuthController
{


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $params = Util::getMore([
            ['keyword','']
        ],$this->request);
        $this->assign(ExpressModel::systemPage($params));
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
        $formbuider = [
            Form::input('name','公司名称')->required('公司名称名称必填'),
            Form::input('code','编码'),
            Form::number('sort','排序',0),
            Form::radio('is_show','是否启用',1)->options([['value'=>0,'label'=>'隐藏'],['value'=>1,'label'=>'启用']]),
        ];
        $form = Form::make_post_form('添加物流公司',$formbuider,Url::build('save'),2);
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
            'name',
            'code',
            ['sort',0],
            ['is_show',0]],$request);
        if(!$data['name']) return Json::fail('请输入公司名称');
        ExpressModel::set($data);
        return Json::successful('添加公司成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $menu = ExpressModel::get($id);
        if(!$menu) return Json::fail('数据不存在!');
        $formbuider = [
            Form::input('name','公司名称',$menu['name']),
            Form::input('code','编码',$menu['code']),
            Form::number('sort','排序',$menu['sort']),
            Form::radio('is_show','是否启用',$menu['is_show'])->options([['value'=>0,'label'=>'隐藏'],['value'=>1,'label'=>'启用']])
        ];
        $form = Form::make_post_form('添加物流公司',$formbuider,Url::build('update',array('id'=>$id)),2);
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
            'name',
            'code',
            ['sort',0],
            ['is_show',0]],$request);
        if(!$data['name']) return Json::fail('请输入公司名称');
        if(!ExpressModel::get($id)) return Json::fail('编辑的记录不存在!');
        ExpressModel::edit($data,$id);
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
        $res = ExpressModel::destroy($id);
        if(!$res)
            return Json::fail(ExpressModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

}
