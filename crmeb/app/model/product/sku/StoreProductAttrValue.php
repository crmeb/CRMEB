<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\product\sku;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;
use app\model\product\product\StoreProduct;

/**
 * Class StoreProductAttrValue
 * @package app\common\model\product
 */
class StoreProductAttrValue extends BaseModel
{
    use ModelTrait;

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr_value';

    protected $insert = ['unique'];

    /**
     * sku 字段写入
     * @param $value
     * @return string
     */
    public function setSukAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * Unique字段写入
     * @param $value
     * @param $data
     * @return mixed
     */
    public function setUniqueAttr($value, $data)
    {
        if (is_array($data['suk'])) {
            $data['suk'] = $this->setSukAttr($data['suk']);
        }
        return $data['unique'] ?: substr(md5($data['product_id'] . $data['suk'] . uniqid(true)), 12, 8);
    }

    /**
     * 商品搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchProductIdAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('product_id', $value);
        } else {
            $query->where('product_id', $value);
        }
    }

    /**
     * 商品类型搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTypeAttr($query, $value)
    {
        $query->where('type', $value);
    }

    /**
     * 商品属性名称搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchSukAttr($query, $value)
    {
        if ($value) {
            $query->where('suk', $value);
        }
    }

    /**
     * 规格唯一值搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchUniqueAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('unique', $value);
        } else {
            if ($value) {
                $query->where('unique', $value);
            }
        }
    }

    public function product()
    {
        return $this->hasOne(StoreProduct::class, 'id', 'product_id')->field('store_name,id')->bind(['store_name']);
    }

}
