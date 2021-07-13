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

namespace app\model\coupon;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * TODO 优惠券模板Model
 * Class StoreCoupon
 * @package app\model\coupon
 */
class StoreCoupon extends BaseModel
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
    protected $name = 'store_coupon';

    /**
     * 优惠卷类型
     * @var string[]
     */
    protected $couponType = [0 => '通用券', 1 => '品类券', 2 => '商品券'];

    /**
     * 一对多关联
     * @return \think\model\relation\HasMany
     */
    public function productId()
    {
        return $this->hasMany(StoreCouponProduct::class, 'coupon_id', 'id');
    }

    /**
     * 优惠券类型获取器
     * @param $value
     * @return string
     */
    public function getTypeAttr($value)
    {
        return $this->couponType[$value];
    }

    /**
     * 优惠券模板标题搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTitleAttr($query, $value, $data)
    {
        if ($value) $query->where('title', 'like', '%' . $value . '%');
    }

    /**
     * 优惠券模板状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value != '') $query->where('status', $value);
    }

    /**
     * 最低消费金额搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchUseMinPriceAttr($query, $value, $data)
    {
        $query->where('use_min_price', $value);
    }

    /**
     * 优惠券面值搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCouponPriceAttr($query, $value, $data)
    {
        $query->where('coupon_price', $value);
    }

    /**
     * 是否删除搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsDelAttr($query, $value, $data)
    {
        $query->where('is_del', $value ?? 0);
    }

    /**
     * 优惠券类型搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTypeAttr($query, $value, $data)
    {
        $query->where('type', $value ?? 0);
    }

    /**
     * 分类ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCategoryIdAttr($query, $value, $data)
    {
        $query->where('category_id', $value);
    }
}
