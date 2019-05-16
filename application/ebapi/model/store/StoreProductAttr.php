<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/13
 */

namespace app\ebapi\model\store;


use basic\ModelBasic;
use think\Db;
use traits\ModelTrait;

class StoreProductAttr extends ModelBasic
{

    use ModelTrait;

    protected function getAttrValuesAttr($value)
    {
        return explode(',',$value);
    }

    public static function storeProductAttrValueDb()
    {
        return Db::name('StoreProductAttrValue');
    }


    /**
     * 获取商品属性数据
     * @param $productId
     * @return array
     */
    public static function getProductAttrDetail($productId)
    {
        $attrDetail = self::where('product_id',$productId)->order('attr_values asc')->select()->toArray()?:[];
        $_values = self::storeProductAttrValueDb()->where('product_id',$productId)->select();
        $values = [];
        foreach ($_values as $value){
            $values[$value['suk']] = $value;
        }
        foreach ($attrDetail as $k=>$v){
            $attr = $v['attr_values'];
//            unset($productAttr[$k]['attr_values']);
            foreach ($attr as $kk=>$vv){
                $attrDetail[$k]['attr_value'][$kk]['attr'] =  $vv;
                $attrDetail[$k]['attr_value'][$kk]['check'] =  false;
            }
        }
        return [$attrDetail,$values];
    }

    public static function uniqueByStock($unique)
    {
        return self::storeProductAttrValueDb()->where('unique',$unique)->value('stock')?:0;
    }

    public static function uniqueByAttrInfo($unique, $field = '*')
    {
        return self::storeProductAttrValueDb()->field($field)->where('unique',$unique)->find();
    }

    public static function issetProductUnique($productId,$unique)
    {
        $res = self::be(['product_id'=>$productId]);
        if($unique){
            return $res && self::storeProductAttrValueDb()->where('product_id',$productId)->where('unique',$unique)->count() > 0;
        }else{
            return !$res;
        }
    }

}