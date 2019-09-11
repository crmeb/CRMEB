<?php
namespace app\api\controller\publics;

use app\models\article\ArticleCategory;

/**
 * 文章分类类
 * Class ArticleCategoryController
 * @package app\api\controller\publics
 */
class ArticleCategoryController
{
    /**
     * 文章分类列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
   public function lst()
   {
       $cateInfo = ArticleCategory::getArticleCategory();
       if($cateInfo) $cateInfo = $cateInfo->toArray();
       else $cateInfo = [];
       array_unshift($cateInfo,['id'=>0,'title'=>'热门']);
       return app('json')->successful($cateInfo);
   }
}