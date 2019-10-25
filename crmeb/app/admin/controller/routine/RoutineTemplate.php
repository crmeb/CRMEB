<?php

namespace app\admin\controller\routine;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use think\facade\Route as Url;
use app\admin\model\routine\RoutineTemplate as RoutineTemplateModel;

/**
 * 小程序模板消息控制器
 * Class RoutineTemplate
 * @package app\admin\controller\routine
 */
class RoutineTemplate extends AuthController
{
    public function index()
    {
        $where = Util::getMore([
            ['name',''],
            ['status','']
        ],$this->request);
        $this->assign('where',$where);
        $this->assign(RoutineTemplateModel::SystemPage($where));
        return $this->fetch();
    }

    /**
     * 添加模板消息
     * @return mixed
     */
    public function create()
    {
        $f = array();
        $f[] = Form::input('tempkey','模板编号');
        $f[] = Form::input('tempid','模板ID');
        $f[] = Form::input('name','模板名');
        $f[] = Form::input('content','回复内容')->type('textarea');
        $f[] = Form::radio('status','状态',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]]);
        $form = Form::make_post_form('添加模板消息',$f,Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    public function save()
    {
        $data = Util::postMore([
            'tempkey',
            'tempid',
            'name',
            'content',
            ['status',0]
        ]);
        if($data['tempkey'] == '') return Json::fail('请输入模板编号');
        if($data['tempkey'] != '' && RoutineTemplateModel::be($data['tempkey'],'tempkey'))
            return Json::fail('请输入模板编号已存在,请重新输入');
        if($data['tempid'] == '') return Json::fail('请输入模板ID');
        if($data['name'] == '') return Json::fail('请输入模板名');
        if($data['content'] == '') return Json::fail('请输入回复内容');
        $data['add_time'] = time();
        RoutineTemplateModel::create($data);
        return Json::successful('添加模板消息成功!');
    }

    /**
     * 编辑模板消息
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $product = RoutineTemplateModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('tempkey','模板编号',$product->getData('tempkey'))->disabled(1);
        $f[] = Form::input('name','模板名',$product->getData('name'))->disabled(1);
        $f[] = Form::input('tempid','模板ID',$product->getData('tempid'));
        $f[] = Form::radio('status','状态',$product->getData('status'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]]);
        $form = Form::make_post_form('编辑模板消息',$f,Url::buildUrl('update',compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function update($id)
    {
        $data = Util::postMore([
            'tempid',
            ['status',0]
        ]);
        if($data['tempid'] == '') return Json::fail('请输入模板ID');
        if(!$id) return $this->failed('数据不存在');
        $product = RoutineTemplateModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        RoutineTemplateModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除模板消息
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        if(!$id) return Json::fail('数据不存在!');
        if(!RoutineTemplateModel::del($id))
            return Json::fail(RoutineTemplateModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }


}