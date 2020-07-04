<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\admin\model\article;

use app\admin\model\store\StoreProduct;
use app\admin\model\system\SystemAdmin;
use app\models\article\ArticleContent;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 图文管理 Model
 * Class WechatNews
 * @package app\admin\model\wechat
 */
class Article extends BaseModel
{

    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'article';

    public function profile()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id')->field('store_name');
    }

    /**
     * 获取配置分类
     * @param array $where
     * @return array
     */
    public static function getAll($where = [])
    {
        $model = new self;
//        if($where['status'] !== '') $model = $model->where('status',$where['status']);
//        if($where['access'] !== '') $model = $model->where('access',$where['access']);
        if ($where['title'] !== '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        if ($where['cid'] !== '')
            $model = $model->where('cid', 'in', $where['cid']);
        else
            if ($where['merchant'])
                $model = $model->where('mer_id', '>', 0);
            else
                $model = $model->where('mer_id', 0);
        $model = $model->where('status', 1)->where('hide', 0)->order('id desc');
        return self::page($model, function ($item) {
            if (!$item['mer_id']) $item['admin_name'] = '总后台管理员---》' . SystemAdmin::where('id', $item['admin_id'])->value('real_name');
            else $item['admin_name'] = Merchant::where('id', $item['mer_id'])->value('mer_name') . '---》' . MerchantAdmin::where('id', $item['admin_id'])->value('real_name');
            $item['content'] = ArticleContent::where('nid', $item['id'])->value('content');
            $item['catename'] = ArticleCategory::where('id', $item['cid'])->value('title');
            $item['store_name'] = $item->profile->store_name ?? '';
        }, $where);
    }

    /**
     * 删除图文
     * @param $id
     * @return bool
     */
    public static function del($id)
    {
        return self::edit(['status' => 0], $id, 'id');
    }

    /**
     * 获取指定字段的值
     * @return array
     */
    public static function getNews()
    {
        return self::where('status', 1)->where('hide', 0)->order('id desc')->column('title', 'id');
    }

    /**
     * 给表中的字符串类型追加值
     * 删除所有有当前分类的id之后重新添加
     * @param $cid
     * @param $id
     * @return bool
     */
    public static function saveBatchCid($cid, $id)
    {
        $res_all = self::where('cid', 'LIKE', "%$cid%")->select();//获取所有有当前分类的图文
        foreach ($res_all as $k => $v) {
            $cid_arr = explode(',', $v['cid']);
            if (in_array($cid, $cid_arr)) {
                $key = array_search($cid, $cid_arr);
                array_splice($cid_arr, $key, 1);
            }
            if (empty($cid_arr)) {
                $data['cid'] = 0;
                self::edit($data, $v['id']);
            } else {
                $data['cid'] = implode(',', $cid_arr);
                self::edit($data, $v['id']);
            }
        }
        $res = self::where('id', 'IN', $id)->select();
        foreach ($res as $k => $v) {
            if (!in_array($cid, explode(',', $v['cid']))) {
                if (!$v['cid']) {
                    $data['cid'] = $cid;
                } else {
                    $data['cid'] = $v['cid'] . ',' . $cid;
                }
                self::edit($data, $v['id']);
            }
        }
        return true;
    }

    public static function setContent($id, $content)
    {
        $count = ArticleContent::where('nid', $id)->count();
        $data['nid'] = $id;
        $data['content'] = $content;
        if ($count) {
            $contentSql = ArticleContent::where('nid', $id)->value('content');
            if ($contentSql == $content) $res = true;
            else $res = ArticleContent::where('nid', $id)->update(['content' => $content]);
            if ($res !== false) $res = true;
        } else {
            $res = ArticleContent::insert($data);
        }
        return $res;
    }

    public static function merchantPage($where = [])
    {
        $model = new self;
        if ($where['title'] !== '') $model = $model->where('title', 'LIKE', "%$where[title]%");
        if ($where['cid'] !== '') $model = $model->where('cid', 'LIKE', "%$where[cid]%");
        $model = $model
            ->where('status', 1)
            ->where('hide', 0)
            ->where('admin_id', $where['admin_id'])
            ->where('mer_id', $where['mer_id']);
        return self::page($model, function ($item) {
            $item['content'] = ArticleContent::where('nid', $item['id'])->value('content');
        }, $where);
    }

    /**
     * 获取指定文章列表  图文管理使用
     * @param string $id
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getArticleList($id = '', $field = 'title,author,image_input,synopsis,id')
    {
        $list = self::where('id', 'IN', $id)->field($field)->select();
        foreach ($list as $k => $v) {
            $list[$k]['content'] = ArticleContent::where('nid', $v['id'])->value('content');
        }
        return $list;
    }
}