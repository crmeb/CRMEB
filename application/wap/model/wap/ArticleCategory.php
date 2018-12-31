<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02 */

namespace app\wap\model\wap;

use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;

/**
 * Class ArticleCategory
 * @package app\wap\model
 */
class ArticleCategory extends ModelBasic
{
    use ModelTrait;

    public static function cidByArticleList($cid, $first, $limit, $field = '*')
    {
        $model = Db::name('article');
        if ($cid) $model->where("CONCAT(',',cid,',')", 'LIKE', "'%,$cid,%'");
        return $model->field($field)->where('status', 1)->where('hide', 0)->order('sort DESC,add_time DESC')->limit($first, $limit)->select();
    }
}