<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\model\order;

use app\model\product\CenterProductModel;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 购物车Model
 * Class StoreCart
 * @package app\model\order
 */
class StoreCart extends BaseModel
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
    protected $name = 'store_cart';

    /**
     * 自动添加字段
     * @var string[]
     */
    protected $insert = ['add_time'];

    /**
     * 添加时间修改器
     * @return int
     */
    protected function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 一对一关联
     * 购物车关联商品商品详情
     * @return \think\model\relation\HasOne
     */
    public function productInfo()
    {
        return $this->hasOne(CenterProductModel::class, 'id', 'product_id');
    }

    /**
     * 一对一关联
     * 购物车关联商品规格
     * 旧表 eb_store_product_attr_value 已弃用，此处临时关联中台商品表，SKU 详情请通过 ProductTypeHandler 获取
     * @return \think\model\relation\HasOne
     */
    public function attrInfo()
    {
        return $this->hasOne(CenterProductModel::class, 'id', 'product_id');
    }


    /**
     * 类型搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTypeAttr($query, $value, $data)
    {
        $query->where('type', $value);
    }

    /**
     * 是否支付
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsPayAttr($query, $value, $data)
    {
        $query->where('is_pay', $value);
    }

    /**
     * 是否删除
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsDelAttr($query, $value, $data)
    {
        $query->where('is_del', $value);
    }

    /**
     * 是否立即支付
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsNewAttr($query, $value, $data)
    {
        $query->where('is_new', $value);
    }

    /**
     * 查询用户购物车
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchUidAttr($query, $value, $data)
    {
        $query->where('uid', $value);
    }

    /**
     * 商品ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchProductIdAttr($query, $value, $data)
    {
        if (is_array($value)) {
            $query->whereIn('product_id', $value);
        } else {
            $query->where('product_id', $value);
        }
    }

    /**
     * 商品规格唯一值搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchProductAttrUniqueAttr($query, $value, $data)
    {
        $query->where('product_attr_unique', $value);
    }

    /**
     * 拼团ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCombinationIdAttr($query, $value, $data)
    {
        $query->where('combination_id', $value);
    }

    /**
     * 砍价ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchBargainIdAttr($query, $value, $data)
    {
        $query->where('bargain_id', $value);
    }

    /**
     * 秒杀ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchSeckillIdAttr($query, $value, $data)
    {
        $query->where('seckill_id', $value);
    }

    /**
     * 一对多关联
     * 商品关联优惠卷模板id
     * @return \think\model\relation\HasMany
     */
    public function product()
    {
        return $this->hasMany(CenterProductModel::class, 'id', 'product_id');

    }
}
