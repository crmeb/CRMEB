<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/13
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use think\facade\Db;
use crmeb\traits\ModelTrait;

/**
 * TODO  产品属性Model
 * Class StoreProductAttr
 * @package app\models\store
 */
class StoreProductAttr extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr';

    use ModelTrait;

    protected function getAttrValuesAttr($value)
    {
        return explode(',', $value);
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
    public static function getProductAttrDetail($productId, $uid = 0, $type = 0, $type_id = 0)
    {
        $attrDetail = self::where('product_id', $productId)->where('type', $type_id)->order('attr_values asc')->select()->toArray() ?: [];
        $_values = self::storeProductAttrValueDb()->where('product_id', $productId)->where('type', $type_id)->select();
        $values = [];
        foreach ($_values as $value) {
            if ($type) {
                if ($uid)
                    $value['cart_num'] = StoreCart::where('product_attr_unique', $value['unique'])->where('is_pay', 0)->where('is_del', 0)->where('is_new', 0)->where('type', 'product')->where('product_id', $productId)->where('uid', $uid)->value('cart_num');
                else
                    $value['cart_num'] = 0;
                if (is_null($value['cart_num'])) $value['cart_num'] = 0;
            }
            unset($value['cost']);
            $values[$value['suk']] = $value;
        }
        foreach ($attrDetail as $k => $v) {
            $attr = $v['attr_values'];
//            unset($productAttr[$k]['attr_values']);
            foreach ($attr as $kk => $vv) {
                $attrDetail[$k]['attr_value'][$kk]['attr'] = $vv;
                $attrDetail[$k]['attr_value'][$kk]['check'] = false;
            }
        }
        return [$attrDetail, $values];
    }

    public static function uniqueByStock($unique)
    {
        return self::storeProductAttrValueDb()->where('unique', $unique)->value('stock') ?: 0;
    }

    public static function uniqueByAttrInfo($unique, $field = '*')
    {
        return self::storeProductAttrValueDb()->field($field)->where('unique', $unique)->find();
    }

    public static function issetProductUnique($productId, $unique)
    {
//        $res = self::be(['product_id'=>$productId]);
        $res = self::where('product_id', $productId)->where('type', 0)->find();
        if ($unique) {
            return $res && self::storeProductAttrValueDb()->where('product_id', $productId)->where('unique', $unique)->where('type', 0)->count() > 0;
        } else {
            return !$res;
        }
    }

}