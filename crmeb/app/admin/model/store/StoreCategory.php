<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * Class StoreCategory
 * @package app\admin\model\store
 */
class StoreCategory extends BaseModel
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
    protected $name = 'store_category';

    use ModelTrait;

    /**
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function CategoryList($where)
    {
        $data = ($data = self::systemPage($where, true)->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item) {
            if ($item['pid']) {
                $item['pid_name'] = self::where('id', $item['pid'])->value('cate_name');
            } else {
                $item['pid_name'] = '顶级';
            }
        }
        $count = self::systemPage($where, true)->count();
        return compact('count', 'data');
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where, $isAjax = false)
    {
        $model = new self;
        if ($where['pid'] != '') $model = $model->where('pid', $where['pid']);
        else if ($where['pid'] == '' && $where['cate_name'] == '') $model = $model->where('pid', 0);
        if ($where['is_show'] != '') $model = $model->where('is_show', $where['is_show']);
        if ($where['cate_name'] != '') $model = $model->where('cate_name', 'LIKE', "%$where[cate_name]%");
        if ($isAjax === true) {
            if (isset($where['order']) && $where['order'] != '') {
                $model = $model->order(self::setOrder($where['order']));
            } else {
                $model = $model->order('sort desc,id desc');
            }
            return $model;
        }
        return self::page($model, function ($item) {
            if ($item['pid']) {
                $item['pid_name'] = self::where('id', $item['pid'])->value('cate_name');
            } else {
                $item['pid_name'] = '顶级';
            }
        }, $where);
    }

    /**
     * 获取顶级分类
     * @return array
     */
    public static function getCategory()
    {
        return self::where('is_show', 1)->column('cate_name', 'id');
    }

    /**
     * 分级排序列表
     * @param null $model
     * @param int $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getTierList($model = null, $type = 0)
    {
        if ($model === null) $model = new self();
        if (!$type) return sort_list_tier($model->order('sort desc,id desc')->where('pid', 0)->select()->toArray());
        return sort_list_tier($model->order('sort desc,id desc')->select()->toArray());
    }

    public static function delCategory($id)
    {
        $count = self::where('pid', $id)->count();
        if ($count)
            return self::setErrorInfo('请先删除下级子分类');
        else {
            return self::del($id);
        }
    }

    /**
     * 产品分类隐藏显示
     * @param $id
     * @param $show
     * @return bool
     */
    public static function setCategoryShow($id, $show)
    {
        $count = self::where('id', $id)->count();
        if (!$count) return self::setErrorInfo('参数错误');
        $count = self::where('id', $id)->where('is_show', $show)->count();
        if ($count) return true;
        $pid = self::where('id', $id)->value('pid');
        self::beginTrans();
        $res1 = true;
        $res2 = self::where('id', $id)->update(['is_show' => $show]);
        if (!$pid) {//一级分类隐藏
            $count = self::where('pid', $id)->count();
            if ($count) {
                $count      = self::where('pid', $id)->where('is_show', $show)->count();
                $countWhole = self::where('pid', $id)->count();
                if (!$count || $countWhole > $count) {
                    $res1 = self::where('pid', $id)->update(['is_show' => $show]);
                }
            }
        }
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }
}