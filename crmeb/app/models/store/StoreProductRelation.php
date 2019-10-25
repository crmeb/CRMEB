<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\models\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 点赞收藏model
 * Class StoreProductRelation
 * @package app\models\store
 */
class StoreProductRelation extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_relation';

    use ModelTrait;

    /**
     * 获取用户点赞所有产品的个数
     * @param $uid
     * @return int|string
     */
    public static function getUserIdLike($uid = 0){
        $count = self::where('uid',$uid)->where('type','like')->count();
        return $count;
    }

    /**
     * 获取用户收藏所有产品的个数
     * @param $uid
     * @return int|string
     */
    public static function getUserIdCollect($uid = 0){
        $count = self::where('uid',$uid)->where('type','collect')->count();
        return $count;
    }

    /**
     * 添加点赞 收藏
     * @param $productId
     * @param $uid
     * @param $relationType
     * @param string $category
     * @return bool
     */
    public static function productRelation($productId,$uid,$relationType,$category = 'product')
    {
        if(!$productId) return self::setErrorInfo('产品不存在!');
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        $data = ['uid'=>$uid,'product_id'=>$productId,'type'=>$relationType,'category'=>$category];
        if(self::be($data)) return true;
        $data['add_time'] = time();
        self::create($data);
        event('StoreProductUserOperationConfirmAfter',[$category, $productId, $relationType, $uid]);
        return true;
    }

    /**
     * 批量 添加点赞 收藏
     * @param $productIdS
     * @param $uid
     * @param $relationType
     * @param string $category
     * @return bool
     */
    public static function productRelationAll($productIdS,$uid,$relationType,$category = 'product'){
        $res = true;
        if(is_array($productIdS)){
            self::beginTrans();
            foreach ($productIdS as $productId){
                $res = $res && self::productRelation($productId,$uid,$relationType,$category);
            }
            if($res){
                foreach ($productIdS as $productId){
                    event('StoreProductUserOperationConfirmAfter',[$category, $productId, $relationType, $uid]);
                }
            }
            self::checkTrans($res);
            return $res;
        }
        return $res;
    }

    /**
     * 取消 点赞 收藏
     * @param $productId
     * @param $uid
     * @param $relationType
     * @param string $category
     * @return bool
     */
    public static function unProductRelation($productId,$uid,$relationType,$category = 'product')
    {
        if(!$productId) return self::setErrorInfo('产品不存在!');
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        self::where('uid', $uid)->where('product_id', $productId)->where('type', $relationType)->where('category', $category)->delete();
        event('StoreProductUserOperationCancelAfter',[$category, $productId, $relationType, $uid]);
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

    /*
     * 获取某个用户收藏产品
     * @param int uid 用户id
     * @param int $first 行数
     * @param int $limit 展示行数
     * @return array
     * */
    public static function getUserCollectProduct($uid,$page,$limit)
    {
        if(!$limit) return [];
        if($page){
            $list = self::where('A.uid',$uid)
                ->field('B.id pid,A.category,B.store_name,B.price,B.ot_price,B.sales,B.image,B.is_del,B.is_show')
                ->alias('A')
                ->where('A.type','collect')/*->where('A.category','product')*/
                ->order('A.add_time DESC')
                ->join('__store_product__ B','A.product_id = B.id')
                ->page($page, $limit)
                ->select();
        }else{
            $list = self::where('A.uid',$uid)
                ->field('B.id pid,A.category,B.store_name,B.price,B.ot_price,B.sales,B.image,B.is_del,B.is_show')->alias('A')
                ->where('A.type','collect')/*->where('A.category','product')*/
                ->order('A.add_time DESC')->join('__store_product__ B','A.product_id = B.id')
                ->select();
        }
        if(!$list) return [];
        $list = $list->toArray();
        foreach ($list as $k=>$product){
            if($product['pid']){
                $list[$k]['is_fail'] = $product['is_del'] && $product['is_show'];
            }else{
                unset($list[$k]);
            }
        }
        return $list;
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