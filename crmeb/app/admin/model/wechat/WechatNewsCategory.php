<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\admin\model\wechat;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\admin\model\article\Article as ArticleModel;

/**
 * 图文消息 model
 * Class WechatNewsCategory
 * @package app\admin\model\wechat
 */
class WechatNewsCategory extends BaseModel
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
    protected $name = 'wechat_news_category';

    use ModelTrait;

    /**
     * 获取配置分类
     * @param array $where
     * @return array
     */
    public static function getAll($where = array())
    {
        $model = new self;
//        if($where['status'] !== '') $model = $model->where('status',$where['status']);
//        if($where['access'] !== '') $model = $model->where('access',$where['access']);
        if ($where['cate_name'] !== '') $model = $model->where('cate_name', 'LIKE', "%$where[cate_name]%");
        $model = $model->where('status', 1);
        return self::page($model, function ($item) use ($where) {
            $new = ArticleModel::where('id', 'in', $item['new_id'])->where('status', 1)->where('hide', 0)->select();
            if ($new) $new = $new->toArray();
            $item['new'] = $new;
        });
    }

    /**
     * 获取一条图文
     * @param int $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getWechatNewsItem($id = 0, $new_id = 0)
    {
        if (!$id) return [];
        $list = self::where('id', $id)->where('status', 1)->field('cate_name as title,new_id')->find();
        if ($list) {
            $list = $list->toArray();
            $new = ArticleModel::where('id', $new_id)->where('status', 1)->where('hide', 0)->select();
            $new = $new->toArray();
            if ($new) {
                $temp = [];
                $temp[] = $new[0];
                $list['new'] = $temp;
            } else {
                $list['new'] = $new;
            }

        }
        return $list;
    }

    /**
     * 获取发送图文
     * @param array $where
     * @return array
     */
    public static function getSendAll($where = array())
    {
        $model = new self();
        if ($where['cate_name'] !== '') $model = $model->where('cate_name', 'LIKE', "%$where[cate_name]%");
        $new_ids = $model->where('status', 1)->column('new_id');
        $new_ids = array_values($new_ids);
        $new_ids = implode(',', $new_ids);

        $model = ArticleModel::where('status', 1)->where('hide', 0)->where('id', 'in', $new_ids)->field('id,title,image_input,add_time');
        return ArticleModel::page($model, function ($item) use ($where) {
            $new = self::where('status', 1)->whereFindInSet('new_id', $item['id'])->find();
            $item['cate_name'] = $new['cate_name'];
            $temp[0]['title'] = $item['title'];
            $temp[0]['image_input'] = $item['image_input'];
            $temp[0]['id'] = $item['id'];
            $temp[0]['add_time'] = $item['add_time'];
            $item['id'] = $new['id'];
            $item['new'] = $temp;
        });
    }


}