<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService;

/**
 * Class StoreCategory
 * @package app\admin\model\store
 */
class StoreCategory extends ModelBasic
{
    use ModelTrait;

    /*
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function CategoryList($where){
        $data=($data=self::systemPage($where,true)->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() :[];
        foreach ($data as &$item){
            if($item['pid']){
                $item['pid_name'] = self::where('id',$item['pid'])->value('cate_name');
            }else{
                $item['pid_name'] = '顶级';
            }
        }
        $count=self::systemPage($where,true)->count();
        return compact('count','data');
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where,$isAjax=false){
        $model = new self;
        if($where['pid'] != '')  $model = $model->where('pid',$where['pid']);
        else if($where['pid']=='' && $where['cate_name']=='') $model = $model->where('pid',0);
        if($where['is_show'] != '')  $model = $model->where('is_show',$where['is_show']);
        if($where['cate_name'] != '')  $model = $model->where('cate_name','LIKE',"%$where[cate_name]%");
        if($isAjax===true){
            if(isset($where['order']) && $where['order']!=''){
                $model=$model->order(self::setOrder($where['order']));
            }
            return $model;
        }
        return self::page($model,function ($item){
            if($item['pid']){
                $item['pid_name'] = self::where('id',$item['pid'])->value('cate_name');
            }else{
                $item['pid_name'] = '顶级';
            }
        },$where);
    }

    /**
     * 获取顶级分类
     * @return array
     */
    public static function getCategory($field = 'id,cate_name')
    {
        return self::where('is_show',1)->column($field);
    }

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getTierList($model = null)
    {
        if($model === null) $model = new self();
        return UtilService::sortListTier($model->select()->toArray());
    }

    public static function delCategory($id){
        $count = self::where('pid',$id)->count();
        if($count)
            return false;
        else{
            return self::del($id);
        }
    }
}