<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/26
 */

namespace app\models\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 订单记录Model
 * Class StoreOrderCartInfo
 * @package app\models\store
 */
class StoreOrderCartInfo extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_order_cart_info';

    use ModelTrait;

    public static function getCartInfoAttr($value)
    {
        return json_decode($value,true)?:[];
    }

    public static function setCartInfo($oid,array $cartInfo)
    {
        $group = [];
        foreach ($cartInfo as $cart){
            $group[] = [
                'oid'=>$oid,
                'cart_id'=>$cart['id'],
                'product_id'=>$cart['productInfo']['id'],
                'cart_info'=>json_encode($cart),
                'unique'=>md5($cart['id'].''.$oid)
            ];
        }
        return self::setAll($group);
    }

    public static function getProductNameList($oid)
    {
        $cartInfo = self::where('oid',$oid)->select();
        $goodsName = [];
        foreach ($cartInfo as $cart){
            $suk = isset($cart['cart_info']['productInfo']['attrInfo']) ? '('.$cart['cart_info']['productInfo']['attrInfo']['suk'].')' : '';
            $goodsName[] = $cart['cart_info']['productInfo']['store_name'].$suk;
        }
        return $goodsName;
    }

}