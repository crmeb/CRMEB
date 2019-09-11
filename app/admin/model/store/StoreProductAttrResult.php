<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/09
 */

namespace app\admin\model\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreProductAttrResult extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr_result';

    use ModelTrait;

    protected $insert = ['change_time'];

    protected static function setChangeTimeAttr($value)
    {
        return time();
    }

    protected static function setResultAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    public static function setResult($result,$product_id)
    {
        $result = self::setResultAttr($result);
        $change_time = self::setChangeTimeAttr(0);
        $count = self::where('product_id',$product_id)->count();
        $res = true;
        if($count) $res = self::where('product_id',$product_id)->delete();
        if($res) return self::insert(compact('product_id','result','change_time'),true);
        return $res;
    }

    public static function getResult($productId)
    {
        return json_decode(self::where('product_id',$productId)->value('result'),true) ?: [];
    }

    public static function clearResult($productId)
    {
        return self::where('product_id', $productId)->delete();
    }

}