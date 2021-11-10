<?php


namespace app\model\product\sku;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreProductVirtual extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_virtual';

    /**
     * 卡号搜索器
     * @param $query
     * @param $value
     */
    public function searchCardNoAttr($query, $value)
    {
        $query->where('card_no', $value);
    }

    /**
     * 卡密搜索器
     * @param $query
     * @param $value
     */
    public function searchCardPwdAttr($query, $value)
    {
        $query->where('card_pwd', $value);
    }

    /**
     * 商品搜索器
     * @param $query
     * @param $value
     */
    public function searchProductIdAttr($query, $value)
    {
        $query->where('product_id', $value);
    }

    /**
     * 用户搜索器
     * @param $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        $query->where('uid', $value);
    }

    /**
     * 订单搜索器
     * @param $query
     * @param $value
     */
    public function searchOrderIdAttr($query, $value)
    {
        $query->where('order_id', $value);
    }
}
