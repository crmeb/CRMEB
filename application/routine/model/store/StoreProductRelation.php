<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\routine\model\store;

use behavior\wap\StoreProductBehavior;
use service\HookService;
use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 点赞收藏model
 * Class StoreProductRelation
 * @package app\routine\model\store
 */
class StoreProductRelation extends ModelBasic
{
    use ModelTrait;

    /**
     * 获取用户收藏所有产品的个数
     * @param $uid
     * @return int|string
     */
    public static function getUserIdLike($uid = 0){
        $count = self::where('uid',$uid)->where('type','like')->count();
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
        self::set($data);
        HookService::afterListen('store_'.$category.'_'.$relationType,$productId,$uid,false,StoreProductBehavior::class);
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

}