<?php

namespace app\wap\controller;

use app\admin\model\article\Article as ArticleModel;
use app\wap\model\wap\ArticleCategory;
use basic\WapBasic;
use think\Db;

/**
 * 文章分类控制器
 * Class Article
 * @package app\wap\controller
 */
class Article extends WapBasic {

    public function index($cid = ''){
        $title = '新闻列表';
        if($cid){
            $cateInfo = ArticleCategory::where('status',1)->where('is_del',0)->where('id',$cid)->find()->toArray();
            if(!$cateInfo) return $this->failed('文章分类不存在!');
            $title = $cateInfo['title'];
        }
        $this->assign(compact('title','cid'));
       return $this->fetch();
    }

    public function video_school()
    {
        return $this->fetch();
    }

    public function guide()
    {
        return $this->fetch();
    }

    public function visit($id = '')
    {
        $content = ArticleModel::where('status',1)->where('hide',0)->where('id',$id)->order('id desc')->find();

        if(!$content || !$content["status"]) return $this->failed('此文章已经不存在!');
        $content["content"] = Db::name('articleContent')->where('nid',$content["id"])->value('content');
        //增加浏览次数
        $content["visit"] = $content["visit"] + 1;
        ArticleModel::where('id',$id)->update(["visit"=>$content["visit"]]);
        $this->assign(compact('content'));
        return $this->fetch();
    }
}