<?php

namespace app\admin\controller\wechat;

use app\admin\controller\AuthController;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\wechat\ArticleCategory as ArticleCategoryModel;
use app\admin\model\wechat\WechatNews as WechatNewsModel;
use app\admin\model\system\SystemAttachment;

/**
 * 图文管理
 * Class WechatNews
 * @package app\admin\controller\wechat
 */
class WechatNews extends AuthController
{
    /**
     * 显示后台管理员添加的图文
     * @return mixed
     */
    public function index($cid = 0)
    {
        $where = Util::getMore([
            ['title','']
        ],$this->request);
        if($cid)
            $where['cid'] = $cid;
        else
            $where['cid'] = '';
        $this->assign('where',$where);
        $where['merchant'] = 0;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $this->assign('cid',$cid);
        $this->assign(WechatNewsModel::getAll($where));
        return $this->fetch();
    }

    /**
     * 展示页面   添加和删除
     * @return mixed
     */
    public function create(){
        $id = input('id');
        $cid = input('cid');
        $news = array();
        $news['id'] = '';
        $news['image_input'] = '';
        $news['title'] = '';
        $news['author'] = '';
        $news['content'] = '';
        $news['synopsis'] = '';
        $news['url'] = '';
        $news['cid'] = array();
        if($id){
            $news = \app\admin\model\wechat\WechatNews::where('n.id',$id)->alias('n')->field('n.*,c.content')->join('__WECHAT_NEWS_CONTENT__ c','c.nid=n.id')->find();
            if(!$news) return $this->failedNotice('数据不存在!');
            $news['cid'] = explode(',',$news['cid']);
//            dump($news);
        }
        $all = array();
        $select =  0;
        if(!$cid)
            $cid = '';
        else {
            if($id){
                $all = ArticleCategoryModel::where('id',$cid)->where('hidden','neq',0)->column('id,title');
                $select = 1;
            }else{
                $all = ArticleCategoryModel::where('id',$cid)->column('id,title');
                $select = 1;
            }

        }
        if(empty($all)){
            $all = ArticleCategoryModel::getField('id,title');//新闻分类
            $select =  0;
        }
        $this->assign('all',$all);
        $this->assign('news',$news);
        $this->assign('cid',$cid);
        $this->assign('select',$select);
        return $this->fetch();
    }

    /**
     * 上传图文图片
     * @return \think\response\Json
     */
    public function upload_image(){
        $res = Upload::Image($_POST['file'],'wechat/image/'.date('Ymd'));
        //产品图片上传记录
        $fileInfo = $res->fileInfo->getinfo();
        SystemAttachment::attachmentAdd($res->fileInfo->getSaveName(),$fileInfo['size'],$fileInfo['type'],$res->dir,'',5);
        if(!$res->status) return Json::fail($res->error);
        return Json::successful('上传成功!',['url'=>$res->filePath]);
    }

    /**
     * 添加和修改图文
     * @param Request $request
     * @return \think\response\Json
     */
    public function add_new(Request $request){
        $post  = $request->post();
        $data = Util::postMore([
            ['id',0],
            ['cid',[]],
            'title',
            'author',
            'image_input',
            'content',
            'synopsis',
            'share_title',
            'share_synopsis',
            ['visit',0],
            ['sort',0],
            'url',
            ['status',1],],$request);
        $data['cid'] = implode(',',$data['cid']);
        $content = $data['content'];
        unset($data['content']);
        if($data['id']){
            $id = $data['id'];
            unset($data['id']);
            WechatNewsModel::beginTrans();
            $res1 = WechatNewsModel::edit($data,$id,'id');
            $res2 = WechatNewsModel::setContent($id,$content);
            if($res1 && $res2)
                $res = true;
            else
                $res =false;
//            dump($res);
//            exit();
            WechatNewsModel::checkTrans($res);
            if($res)
                return Json::successful('修改图文成功!',$id);
            else
                return Json::fail('修改图文失败!',$id);
        }else{
            $data['add_time'] = time();
            $data['admin_id'] = $this->adminId;
            WechatNewsModel::beginTrans();
            $res1 = WechatNewsModel::set($data);
            $res2 = false;
            if($res1)
                $res2 = WechatNewsModel::setContent($res1->id,$content);
            if($res1 && $res2)
                $res = true;
            else
                $res =false;
            WechatNewsModel::checkTrans($res);
            if($res)
                return Json::successful('添加图文成功!',$res1->id);
            else
                return Json::successful('添加图文失败!',$res1->id);
        }
    }

    /**
     * 删除图文
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        $res = WechatNewsModel::del($id);
        if(!$res)
            return Json::fail('删除失败,请稍候再试!');
        else
            return Json::successful('删除成功!');
    }

    public function merchantIndex(){
        $where = Util::getMore([
            ['title','']
        ],$this->request);
        $this->assign('where',$where);
        $where['cid'] = input('cid');
        $where['merchant'] = 1;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $this->assign(WechatNewsModel::getAll($where));
        return $this->fetch();
    }
}