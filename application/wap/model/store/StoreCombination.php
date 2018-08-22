<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\wap\model\store;


use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 拼团model
 * Class StoreCombination
 * @package app\admin\model\store
 */
class StoreCombination extends ModelBasic
{
    use ModelTrait;

    /**
     * @param $where
     * @return array
     */
    public static function get_list($length=10){
        if($post=input('post.')){
            $where=$post['where'];
            $model = new self();
            $model = $model->alias('c');
            $model = $model->join('StoreProduct s','s.id=c.product_id');
            $model = $model->where('c.is_show',1)->where('c.is_del',0)->where('c.start_time','LT',time())->where('c.stop_time','GT',time());
            if(!empty($where['search'])){
                $model = $model->where('c.title','like',"%{$where['search']}%");
                $model = $model->whereOr('s.keyword','like',"{$where['search']}%");
            }
            $model = $model->field('c.*,s.price as product_price');
            if($where['key']){
                if($where['sales']==1){
                    $model = $model->order('c.sales desc');
                }else if($where['sales']==2){
                    $model = $model->order('c.sales asc');
                }
                if($where['price']==1){
                    $model = $model->order('c.price desc');
                }else if($where['price']==2){
                    $model = $model->order('c.price asc');
                }
                if($where['people']==1){
                    $model = $model->order('c.people asc');
                }
                if($where['default']==1){
                    $model = $model->order('c.sort desc,c.id desc');
                }
            }else{
                $model = $model->order('c.sort desc,c.id desc');
            }
            $page=is_string($where['page'])?(int)$where['page']+1:$where['page']+1;
            $list = $model->page($page,$length)->select()->toArray();   
            return ['list'=>$list,'page'=>$page];
        }
    }
    /**
     * 获取所有拼团数据
     * @param int $limit
     * @param int $length
     * @return mixed
     */
    public static function getAll($limit = 0,$length = 0){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price');
        $model = $model->order('c.sort desc,c.id desc');
        $model = $model->where('c.is_show',1);
        $model = $model->where('c.is_del',0);
        $model = $model->where('c.start_time','LT',time());
        $model = $model->where('c.stop_time','GT',time());
        if($limit && $length) $model = $model->limit($limit,$length);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }
    /**
     * 获取一条拼团数据
     * @param $id
     * @return mixed
     */
    public static function getCombinationOne($id){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price,s.cate_id');
        $model = $model->where('c.is_show',1);
        $model = $model->where('c.is_del',0);
        $model = $model->where('c.id',$id);
        $model = $model->where('c.start_time','LT',time());
        $model = $model->where('c.stop_time','GT',time()-86400);
        $list = $model->find();
        if($list) return $list->toArray();
        else return [];
    }
    /**
     * 获取产品状态
     * @param $id
     * @return mixed
     */
    public static function isValidCombination($id){
        $model = new self();
        $model = $model->where('id',$id);
        $model = $model->where('is_del',0);
        $model = $model->where('is_show',1);
        return $model->count();
    }
    /**
     * 判断库存是否足够
     * @param $id
     * @param $cart_num
     * @return int|mixed
     */
    public static function getCombinationStock($id,$cart_num){
        $stock = self::where('id',$id)->value('stock');
        return $stock > $cart_num ? $stock : 0;
    }

    /**
     * 获取推荐的拼团产品
     * @return mixed
     */
    public static function getCombinationHost($limit = 0){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.id,c.image,c.price,c.sales,c.title,c.people,s.price as product_price');
        $model = $model->where('c.is_del',0);
        $model = $model->where('c.is_host',1);
        $model = $model->where('c.is_show',1);
        $model = $model->where('c.start_time','LT',time());
        $model = $model->where('c.stop_time','GT',time());
        if($limit) $model = $model->limit($limit);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }

    /**
     * 修改销量和库存
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function decCombinationStock($num,$CombinationId)
    {
        $res = false !== self::where('id',$CombinationId)->dec('stock',$num)->inc('sales',$num)->update();
        return $res;
    }

    /**
     * 增加浏览量
     * @param int $id
     * @return bool
     */
    public static function editIncBrowse($id = 0){
        if(!$id) return false;
        $browse = self::where('id',$id)->value('browse');
        $browse = bcadd($browse,1,0);
        self::edit(['browse'=>$browse],$id);
    }
}