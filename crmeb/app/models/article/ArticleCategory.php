<?php
namespace app\models\article;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;


/**
 * TODO 文章分类Model
 * Class ArticleCategory
 * @package app\models\article
 */
class ArticleCategory extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'article_category';

    use ModelTrait;

    /**
     * TODO 获取文章分类
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArticleCategory(){
        return self::where('hidden',0)->where('is_del',0)->where('status',1)->where('pid',0)->order('sort DESC')->field('id,title')->select();
    }

    /**
     * TODO  获取分类字段
     * @param $id $id 编号
     * @param string $field $field 字段
     * @return mixed|string
     */
    public static function getArticleCategoryField($id,$field = 'title'){
        if(!$id) return '';
        return self::where('id',$id)->value($field);
    }

    /**
     * @param $cid
     * @param $first
     * @param $limit
     * @param string $field
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function cidByArticleList($cid, $first, $limit, $field = '*')
    {
        $model = new Article();
        if ($cid) $model->where('cid',$cid);
        return  $model->field($field)->where('status', 1)->where('hide', 0)->order('sort DESC,add_time DESC')->limit($first, $limit)->select();
    }
}