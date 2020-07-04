<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\admin\model\article;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\admin\model\article\Article as ArticleModel;

/**
 * 文章分类model
 * Class ArticleCategory
 * @package app\admin\model\wechat
 */
class ArticleCategory extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'article_category';

    /**
     * 获取系统分页数据   分类
     * @param array $where
     * @return array
     */
    public static function systemPage($where = [])
    {
        $model = new self;
        if ($where['title'] !== '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        if ($where['status'] !== '') $model = $model->where('status', $where['status']);
        $model = $model->where('is_del', 0);
        $model = $model->where('hidden', 0);
        return self::page($model);
    }

    /**
     * 删除分类
     * @param $id
     * @return bool
     */
    public static function delArticleCategory($id)
    {
        if (count(self::getArticle($id, '*')) > 0)
            return self::setErrorInfo('请先删除改分类下的文章!');
        return self::edit(['is_del' => 1], $id, 'id');
    }

    /**
     * 获取分类名称和id
     * @return array
     */
    public static function getField()
    {
        return self::where('is_del', 0)->where('status', 1)->where('hidden', 0)->column('title', 'id');
    }

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getTierList($model = null)
    {
        if ($model === null) $model = new self();
        return sort_list_tier($model->where('is_del', 0)->where('status', 1)->select()->toArray());
    }

    /**
     * 获取分类底下的文章
     * id  分类表中的分类id
     * return array
     * */
    public static function getArticle($id, $field)
    {
        $res     = ArticleModel::where('status', 1)->where('hide', 0)->column($field, 'id');
        $new_res = array();
        foreach ($res as $k => $v) {
            $cid_arr = explode(',', $v['cid']);
            if (in_array($id, $cid_arr)) {
                $new_res[$k] = $res[$k];
            }
        }
        return $new_res;
    }

    /**
     * TODO 获取文章分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArticleCategoryList()
    {
        $list = self::where('is_del', 0)->where('status', 1)->select();
        if ($list) return $list->toArray();
        return [];
    }

    /**
     * TODO 获取文章分类信息
     * @param $id
     * @param string $field
     * @return mixed
     */
    public static function getArticleCategoryInfo($id, $field = 'title')
    {
        $model = new self;
        if ($id) $model = $model->where('id', $id);
        $model = $model->where('is_del', 0);
        $model = $model->where('status', 1);
        return $model->column($field, 'id');
    }

}