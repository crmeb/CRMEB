<?php

namespace app\admin\controller\widget;
use think\Request;
use think\Url;
use app\admin\model\system\SystemAttachment as SystemAttachmentModel;
use app\admin\model\system\SystemAttachmentCategory as Category;
use app\admin\controller\AuthController;
use service\UploadService as Upload;
use service\JsonService as Json;
use service\UtilService as Util;
use service\FormBuilder as Form;

/**
 * 文件校验控制器
 * Class SystemFile
 * @package app\admin\controller\system
 *
 */
class Images extends AuthController
{
    /**
     * 附件列表
     * @return \think\response\Json
     */
   public function index()
   {
       $pid = input('pid') != NULL ?input('pid'):session('pid');
       if($pid != NULL)session('pid',$pid);
       if(!empty(session('pid')))$pid = session('pid');
       $this->assign('pid',$pid);
       //分类标题
       $typearray = Category::getAll();
       $this->assign(compact('typearray'));
//       $typearray = self::dir;
//       $this->assign(compact('typearray'));
       $this->assign(SystemAttachmentModel::getAll($pid));
       return $this->fetch('widget/images');
   }
    /**
     * 图片管理上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $pid = input('pid')!= NULL ?input('pid'):session('pid');
        $res = Upload::image('file','attach'.DS.date('Y').DS.date('m').DS.date('d'));
        $thumbPath = Upload::thumb($res->dir);
        //产品图片上传记录
        $fileInfo = $res->fileInfo->getinfo();
        //入口是public需要替换图片路径
        if(strpos(PUBILC_PATH,'public') == false){
            $res->dir = str_replace('public/','',$res->dir);
        }
        SystemAttachmentModel::attachmentAdd($res->fileInfo->getSaveName(),$fileInfo['size'],$fileInfo['type'],$res->dir,$thumbPath,$pid);
        $info = array(
//            "originalName" => $fileInfo['name'],
//            "name" => $res->fileInfo->getSaveName(),
//            "url" => '.'.$res->dir,
//            "size" => $fileInfo['size'],
//            "type" => $fileInfo['type'],
//            "state" => "SUCCESS"
            'code' =>200,
            'msg'  =>'上传成功',
            'src'  =>$res->dir
        );
        echo json_encode($info);
    }

    /**
     * ajax 提交删除
     */
    public function delete(){
        $request = Request::instance();
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
            @unlink(ROOT_PATH.ltrim($attinfo['att_dir'],'.'));
            @unlink(ROOT_PATH.ltrim($attinfo['satt_dir'],'.'));
            SystemAttachmentModel::where(['att_id'=>$att_id])->delete();
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
        $form = Form::make_post_form('编辑分类',$formbuider,Url::build('moveImgCecate'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**移动图片分类操作
     * @param Request $request
     * @param $id
     */
    public function moveImgCecate(Request $request)
    {
        $data = Util::postMore([
            'pid',
            'imgaes'
        ],$request);
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
    public function addcate($id){
        $id = $id || 0;
        $formbuider = [];
        $formbuider[] = Form::select('pid','上级分类','0')->setOptions(function (){
            $list = Category::getCateList(0);
            $options =  [['value'=>0,'label'=>'所有分类']];
            foreach ($list as $id=>$cateName){
                $options[] = ['label'=>$cateName['html'].$cateName['name'],'value'=>$cateName['id']];
            }
            return $options;
        })->filterable(1);
        $formbuider[] = Form::input('name','分类名称');
        $form = Form::make_post_form('添加分类',$formbuider,Url::build('saveCate'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 添加分类
     */
    public function saveCate(){
        $request = Request::instance();
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
        $form = Form::make_post_form('编辑分类',$formbuider,Url::build('updateCate'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 更新分类
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function updateCate(Request $request,$id)
    {
        $data = Util::postMore([
            'pid',
            'name'
        ],$request);
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
