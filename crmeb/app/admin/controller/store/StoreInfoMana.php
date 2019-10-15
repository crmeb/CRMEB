<?php

namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemAttachment;
use crmeb\services\FormBuilder;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use crmeb\services\UploadService as Upload;
use think\facade\Route as Url;
use app\admin\model\article\ArticleCategory as ArticleCategoryModel;
use app\admin\model\wechat\WechatNews as WechatNewsModel;


/**
 * 资讯管理  控制器
 * Class StoreInfoMana
 * @package app\admin\controller\store
 */
class StoreInfoMana extends AuthController

{

    /**
     * 新闻管理
     * */
     public function index($is_list=0){
         if(!$is_list) return $this->failed('数据不存在');
         echo $is_list;
         exit();
         $where = Util::getMore([
             ['status',''],
             ['title',''],
         ],$this->request);
         $this->assign('where',$where);
         $this->assign(ArticleCategoryModel::systemPage($where));
         return $this->fetch();
     }

    /**

     * 添加分类管理

     * */

    public function create(){
        FormBuilder::text('title','分类昵称');
        FormBuilder::textarea('intr','分类简介');
        FormBuilder::select('new_id','图文列表',function(){
            $list = \app\admin\model\wechat\WechatNews::getNews();
            $options = [];
            foreach ($list as $id=>$roleName){
                $options[] = ['label'=>$roleName,'value'=>$id];
            }
            return $options;
        })->multiple()->filterable();
        FormBuilder::upload('image','分类图片');
        FormBuilder::number('sort','排序',0);
        FormBuilder::radio('status','状态',[['value'=>1,'label'=>'显示'],['value'=>0,'label'=>'隐藏']],1);
        $rules =  FormBuilder::builder()->getContent();
        $this->assign(['title'=>'编辑菜单','rules'=>$rules,'save'=>Url::buildUrl('save')]);
        return $this->fetch();
    }

    /**
     * TODO 上传图片
     * */
    public function upload()
    {
        $res = Upload::image('file','article/'.date('Ymd'));
        SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],2,$res['image_type'],$res['time']);
        if(is_array($res))
            return Json::successful('图片上传成功!',['name'=>$res['name'],'url'=>Upload::pathToUrl($res['thumb_path'])]);
        else
            return Json::fail($res);
    }

    /**

     * 保存分类管理

     * */

    public function save(){
        $data = Util::postMore([
            'title',
            'intr',
            ['new_id',[]],
            ['image',[]],
            ['sort',0],
            'status',]);
        if(!$data['title']) return Json::fail('请输入分类名称');
        if(count($data['image']) != 1) return Json::fail('请选择分类图片，并且只能上传一张');
        if($data['sort'] < 0) return Json::fail('排序不能是负数');
        $data['add_time'] = time();
        $data['image'] = $data['image'][0];
        $new_id = $data['new_id'];
        unset($data['new_id']);
        $res = ArticleCategoryModel::create($data);
        if(!WechatNewsModel::saveBatchCid($res['id'],implode(',',$new_id))) return Json::fail('文章列表添加失败');
        return Json::successful('添加分类成功!');
    }

    /**

     * 修改分类

     * */

    public function edit($id){
        $this->assign(['title'=>'编辑菜单','read'=>Url::buildUrl('read',array('id'=>$id)),'update'=>Url::buildUrl('update',array('id'=>$id))]);
        return $this->fetch();
    }

    public function read($id)
    {
        $article = ArticleCategoryModel::get($id)->getData();
        if(!$article) return Json::fail('数据不存在!');
        FormBuilder::text('title','分类昵称',$article['title']);
        FormBuilder::textarea('intr','分类简介',$article['intr']);
        $arr = ArticleCategoryModel::getArticle($id,'id,title,cid');//子文章
        $new_id = array();
        foreach ($arr as $k=>$v){
            $new_id[$k] = $k;
        }
        FormBuilder::select('new_id','文章列表',function(){
            $list = \app\admin\model\wechat\WechatNews::getNews();
            $options = [];
            foreach ($list as $id=>$roleName){
                $options[] = ['label'=>$roleName,'value'=>$id];
            }
            return $options;
        },$new_id)->multiple();
        FormBuilder::upload('image','分类图片')->defaultFileList($article['image']);
        FormBuilder::number('sort','排序',$article['sort']);
        FormBuilder::radio('status','状态',[['value'=>1,'label'=>'显示'],['value'=>0,'label'=>'隐藏']],$article['status']);
        return FormBuilder::builder();
    }

    public function update($id)
    {
        $data = Util::postMore([
            'title',
            'intr',
            ['new_id',[]],
            ['image',[]],
            ['sort',0],
            'status',]);
        if(!$data['title']) return Json::fail('请输入分类名称');
        if(count($data['image']) != 1) return Json::fail('请选择分类图片，并且只能上传一张');
        if($data['sort'] < 0) return Json::fail('排序不能是负数');
        $data['image'] = $data['image'][0];
//        dump($data);
//        exit;
        if(!ArticleCategoryModel::get($id)) return Json::fail('编辑的记录不存在!');
        if(!WechatNewsModel::saveBatchCid($id,implode(',',$data['new_id']))) return Json::fail('文章列表添加失败');
        unset($data['new_id']);
        ArticleCategoryModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除分类
     * */
    public function delete($id)
    {
        $res = ArticleCategoryModel::delArticleCategory($id);
        if(!$res)
            return Json::fail(ArticleCategoryModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}

