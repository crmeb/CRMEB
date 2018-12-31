<?php

namespace app\admin\controller\article;

use app\admin\controller\AuthController;
use service\UtilService as Util;
use service\PHPTreeService as Phptree;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\article\ArticleCategory as ArticleCategoryModel;
use app\admin\model\article\Article as ArticleModel;
use app\admin\model\system\SystemAttachment;

/**
 * 图文管理
 * Class WechatNews
 * @package app\admin\controller\wechat
 */
class Article extends AuthController
{
    /**
     * 显示后台管理员添加的图文
     * @return mixed
     */
    public function index($pid = 0)
    {
        $where = Util::getMore([
            ['title',''],
            ['cid','']
        ],$this->request);
        $pid = $this->request->param('pid');
        $this->assign('where',$where);
        $where['merchant'] = 0;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $catlist = ArticleCategoryModel::where('is_del',0)->select()->toArray();
        //获取分类列表
        if($catlist){
            $tree = Phptree::makeTreeForHtml($catlist);
            $this->assign(compact('tree'));
            if($pid){
                $pids = Util::getChildrenPid($tree,$pid);
                $where['cid'] = ltrim($pid.$pids);
            }
        }else{
            $tree = [];
            $this->assign(compact('tree'));
        }


        $this->assign('cate',ArticleCategoryModel::getTierList());
        $this->assign(ArticleModel::getAll($where));
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
        $news['is_banner'] = '';
        $news['is_hot'] = '';
        $news['content'] = '';
        $news['synopsis'] = '';
        $news['url'] = '';
        $news['cid'] = array();
        if($id){
            $news = ArticleModel::where('n.id',$id)->alias('n')->field('n.*,c.content')->join('ArticleContent c','c.nid=n.id')->find();
            if(!$news) return $this->failedNotice('数据不存在!');
            $news['cid'] = explode(',',$news['cid']);
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
            $select =  0;
            $list = ArticleCategoryModel::getTierList();
            $all = [];
            foreach ($list as $menu){
                $all[$menu['id']] = $menu['html'].$menu['title'];
            }
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
            ['is_banner',0],
            ['is_hot',0],
            ['status',1],],$request);
        $data['cid'] = implode(',',$data['cid']);
        $content = $data['content'];
        unset($data['content']);
        if($data['id']){
            $id = $data['id'];
            unset($data['id']);
            ArticleModel::beginTrans();
            $res1 = ArticleModel::edit($data,$id,'id');
            $res2 = ArticleModel::setContent($id,$content);
            if($res1 && $res2)
                $res = true;
            else
                $res =false;
//            dump($res);
//            exit();
            ArticleModel::checkTrans($res);
            if($res)
                return Json::successful('修改图文成功!',$id);
            else
                return Json::fail('修改图文失败!',$id);
        }else{
            $data['add_time'] = time();
            $data['admin_id'] = $this->adminId;
            ArticleModel::beginTrans();
            $res1 = ArticleModel::set($data);
            $res2 = false;
            if($res1)
                $res2 = ArticleModel::setContent($res1->id,$content);
            if($res1 && $res2)
                $res = true;
            else
                $res =false;
            ArticleModel::checkTrans($res);
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
        $res = ArticleModel::del($id);
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
        $this->assign(ArticleModel::getAll($where));
        return $this->fetch();
    }
}