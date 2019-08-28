<?php

namespace app\admin\controller\widget;

use crmeb\services\storage\COS;
use crmeb\services\storage\OSS;
use crmeb\services\storage\Qiniu;
use think\facade\Route as Url;
use app\admin\model\system\SystemAttachment as SystemAttachmentModel;
use app\admin\model\system\SystemAttachmentCategory as Category;
use app\admin\controller\AuthController;
use crmeb\services\UploadService as Upload;
use crmeb\services\JsonService as Json;
use crmeb\services\UtilService as Util;
use crmeb\services\FormBuilder as Form;

/**
 * TODO 附件控制器
 * Class Images
 * @package app\admin\controller\widget
 */
class Images extends AuthController
{
    /**
     * 附件列表
     * @return \think\response\Json
     */
   public function index()
   {
       $pid = request()->param('pid');
       if($pid === NULL)
       {
           $pid = session('pid') ? session('pid') : 0;
       }
       session('pid',$pid);
       $this->assign('pid',$pid);
////       //TODO 分类标题
//       $typearray = Category::getAll();
//       $this->assign(compact('typearray'));
//       $this->assign(SystemAttachmentModel::getAll($pid));
       return $this->fetch('widget/images');
   }

    /**获取图片列表
     *
     */
    public function get_image_list()
    {
        $where = Util::getMore([
            ['page',1],
            ['limit',18],
            ['pid',0]
        ]);
        return Json::successful(SystemAttachmentModel::getImageList($where));
    }
    /**获取分类
     * @param string $name
     */
    public function get_image_cate($name = '')
    {
        return Json::successful(Category::getAll($name));
    }

    /**
     * 图片管理上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $pid = input('pid')!= NULL ?input('pid'):session('pid');
        $upload_type = $this->request->get('upload_type',0);
        try{
            $path = make_path('attach');
            $res = Upload::image('file',$path,true,true,null,'uniqid',$upload_type);
            if(is_object($res) && $res->status === false){
                $info = array(
                    'code' =>400,
                    'msg'  =>'上传失败：'.$res->error,
                    'src'  =>''
                );
            }else if(is_string($res)){
                $info = array(
                    'code' =>400,
                    'msg'  =>'上传失败：'.$res,
                    'src'  =>''
                );
            }else if(is_array($res)){
                $res['dir'] = str_replace('\\','/',$res['dir']);
                $res['thumb_path'] = str_replace('\\','/',$res['thumb_path']);
                SystemAttachmentModel::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],$pid,$res['image_type'],$res['time']);
                $info = array(
                    'code' =>200,
                    'msg'  =>'上传成功',
                    'src'  =>$res['dir']
                );
            }
        }catch (\Exception $e){
            $info = [
                'code' =>400,
                'msg'  =>'上传失败：'.$e->getMessage(),
                'src'  =>''
            ];
        }
        echo json_encode($info);
    }

    /**
     * ajax 提交删除
     */
    public function delete(){
        $request = app('request');
        $post = $request->post();
        if(empty($post['imageid'] ))
        Json::fail('还没选择要删除的图片呢？');
        foreach ($post['imageid'] as $v){
            self::deleteimganddata($v);
        }
        Json::successful('删除成功');
    }

    /**删除图片和数据记录
     * @param $att_id
     */
    public function deleteimganddata($att_id){
        $attinfo = SystemAttachmentModel::get($att_id)->toArray();
        if($attinfo){
            if($attinfo['image_type'] == 1){
                @unlink(app()->getRootPath().ltrim($attinfo['att_dir'],'.'));
                @unlink(app()->getRootPath().ltrim($attinfo['satt_dir'],'.'));
            }else if($attinfo['image_type'] == 2){
                Qiniu::delete($attinfo['name']);
            }else if($attinfo['image_type'] == 3){
                OSS::delete($attinfo['name']);
            }else if($attinfo['image_type'] == 4){
                COS::delete($attinfo['name']);
            }
            SystemAttachmentModel::where('att_id', $att_id)->delete();
        }
    }
    /**
     * 移动图片分类显示
     */
    public function moveimg($imgaes){

        $formbuider = [];
        $formbuider[] = Form::hidden('imgaes',$imgaes);
        $formbuider[] = Form::select('pid','选择分类')->setOptions(function (){
            $list = Category::getCateList();
            $options =  [['value'=>0,'label'=>'所有分类']];
            foreach ($list as $id=>$cateName){
                $options[] = ['label'=>$cateName['html'].$cateName['name'],'value'=>$cateName['id']];
            }
            return $options;
        })->filterable(1);
        $form = Form::make_post_form('编辑分类',$formbuider,Url::buildUrl('moveImgCecate'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 移动图片分类操作
     */
    public function moveImgCecate()
    {
        $data = Util::postMore([
            'pid',
            'imgaes'
        ]);
        if($data['imgaes'] == '') return Json::fail('请选择图片');
        if(!$data['pid']) return Json::fail('请选择分类');
        $res = SystemAttachmentModel::where('att_id','in',$data['imgaes'])->update(['pid'=>$data['pid']]);
        if($res)
            Json::successful('移动成功');
        else
            Json::fail('移动失败！');
    }
    /**
     * ajax 添加分类
     */
    public function addcate($id = 0){
        $formbuider = [];
        $formbuider[] = Form::select('pid','上级分类',(string)$id)->setOptions(function (){
            $list = Category::getCateList(0);
            $options =  [['value'=>0,'label'=>'所有分类']];
            foreach ($list as $id=>$cateName){
                $options[] = ['label'=>$cateName['html'].$cateName['name'],'value'=>$cateName['id']];
            }
            return $options;
        })->filterable(1);
        $formbuider[] = Form::input('name','分类名称');
        $jsContent = <<<SCRIPT
parent.SuccessCateg();
parent.layer.close(parent.layer.getFrameIndex(window.name));
SCRIPT;
        $form = Form::make_post_form('添加分类',$formbuider,Url::buildUrl('saveCate'),$jsContent);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 添加分类
     */
    public function saveCate(){
        $request = app('request');
        $post = $request->post();
        $data['pid'] = $post['pid'];
        $data['name'] = $post['name'];
        if(empty($post['name'] ))
            Json::fail('分类名称不能为空！');
        $res = Category::create($data);
        if($res)
            Json::successful('添加成功');
        else
            Json::fail('添加失败！');

    }
    /**
     * 编辑分类
     */
    public function editcate($id){
        $Category = Category::get($id);
        if(!$Category) return Json::fail('数据不存在!');
        $formbuider = [];
        $formbuider[] = Form::hidden('id',$id);
        $formbuider[] = Form::select('pid','上级分类',(string)$Category->getData('pid'))->setOptions(function ()use($id){
            $list = Category::getCateList();
            $options =  [['value'=>0,'label'=>'所有分类']];
            foreach ($list as $id=>$cateName){
                $options[] = ['label'=>$cateName['html'].$cateName['name'],'value'=>$cateName['id']];
            }
            return $options;
        })->filterable(1);
        $formbuider[] = Form::input('name','分类名称',$Category->getData('name'));
        $jsContent = <<<SCRIPT
parent.SuccessCateg();
parent.layer.close(parent.layer.getFrameIndex(window.name));
SCRIPT;
        $form = Form::make_post_form('编辑分类',$formbuider,Url::buildUrl('updateCate'),$jsContent);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 更新分类
     * @param $id
     */
    public function updateCate($id)
    {
        $data = Util::postMore([
            'pid',
            'name'
        ]);
        if($data['pid'] == '') return Json::fail('请选择父类');
        if(!$data['name']) return Json::fail('请输入分类名称');
        Category::edit($data,$id);
        return Json::successful('分类编辑成功!');
    }
    /**
     * 删除分类
     */
    public function deletecate($id){
        $chdcount = Category::where('pid',$id)->count();
        if($chdcount) return Json::fail('有子栏目不能删除');
        $chdcount = SystemAttachmentModel::where('pid',$id)->count();
        if($chdcount) return Json::fail('栏目内有图片不能删除');
        if(Category::del($id))
            return Json::successful('删除成功!');
        else
            return Json::fail('删除失败');


    }



}
