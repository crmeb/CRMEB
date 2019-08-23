<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/14
 */

namespace app\wap\model\store;


use basic\ModelBasic;
use behavior\wap\StoreProductBehavior;
use service\HookService;
use traits\ModelTrait;

class StoreProductRelation extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr($value)
    {
        return time();
    }

    public static function productRelation($productId,$uid,$relationType,$category = 'product')
    {
        if(!$productId) return self::setErrorInfo('产品不存在!');
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        $data = ['uid'=>$uid,'product_id'=>$productId,'type'=>$relationType,'category'=>$category];
        if(self::be($data)) return true;
        self::set($data);
        HookService::afterListen('store_'.$category.'_'.$relationType,$productId,$uid,false,StoreProductBehavior::class);
        return true;
    }

    public static function unProductRelation($productId,$uid,$relationType,$category = 'product')
    {
        if(!$productId) return self::setErrorInfo('产品不存在!');
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        self::where(['uid'=>$uid,'product_id'=>$productId,'type'=>$relationType,'category'=>$category])->delete();
        HookService::afterListen('store_'.$category.'_un_'.$relationType,$productId,$uid,false,StoreProductBehavior::class);
        return true;
    }

    public static function productRelationNum($productId,$relationType,$category = 'product')
    {
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        return self::where('type',$relationType)->where('product_id',$productId)->where('category',$category)->count();
    }

    public static function isProductRelation($product_id,$uid,$relationType,$category = 'product')
    {
        $type = strtolower($relationType);
        $category = strtolower($category);
        return self::be(compact('product_id','uid','type','category'));
    }

    /**
     * TODO 获取普通产品收藏
     * @param $uid
     * @param int $first
     * @param int $limit
     * @return array
     */
    public static function getProductRelation($uid, $first = 0,$limit = 8)
    {
        $model = new self;
        $model = $model->alias('A');
        $model = $model->join('StoreProduct B','A.product_id = B.id');
        $model = $model->where('A.uid',$uid);
        $model = $model->field('B.id pid,B.store_name,B.price,B.ot_price,B.ficti sales,B.image,B.is_del,B.is_show,A.category,A.add_time');
        $model = $model->where('A.type','collect');
        $model = $model->where('A.category','product');
        $model = $model->order('A.add_time DESC');
        $model = $model->limit($first,$limit);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }

    /**
     * TODO 获取秒杀产品收藏
     * @param $uid
     * @param int $first
     * @param int $limit
     * @return array
     */
    public static function getSeckillRelation($uid, $first = 0,$limit = 8)
    {
        $model = new self;
        $model = $model->alias('A');
        $model = $model->join('StoreSeckill B','A.product_id = B.id');
        $model = $model->where('A.uid',$uid);
        $model = $model->field('B.id pid,B.title store_name,B.price,B.ot_price,B.sales,B.image,B.is_del,B.is_show,A.category,A.add_time');
        $model = $model->where('A.type','collect');
        $model = $model->where('A.category','product_seckill');
        $model = $model->order('A.add_time DESC');
        $model = $model->limit($first,$limit);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }

}