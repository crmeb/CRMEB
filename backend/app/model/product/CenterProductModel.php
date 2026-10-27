<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\model\product;

use app\model\product\product\StoreDescription;
use app\model\product\product\StoreProductCoupon;
use app\model\product\product\StoreProductReply;
use app\model\product\product\StoreProductCate;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 中台商品主表 Model
 * 表：eb_product_center
 */
class CenterProductModel extends BaseModel
{
    use ModelTrait;

    /**
     * 主键
     */
    protected $pk = 'id';

    /**
     * 表名（不含前缀 eb_）
     */
    protected $name = 'product_center';

    /**
     * JSON 类型字段（ThinkPHP 自动序列化/反序列化）
     * 删除自定义 getter/setter 避免与 $json 机制冲突
     */
    protected $json = ['delivery_methods', 'freight_config', 'service_region'];

    /**
     * 一对一关联
     * 商品关联商品商品详情
     * @return \think\model\relation\HasOne
     */
    public function description()
    {
        return $this->hasOne(StoreDescription::class, 'product_id', 'id')->where('activity_type', 'normal')->bind(['description']);
    }

    /**
     * 一对多关联
     * 商品关联优惠卷模板id
     * @return \think\model\relation\HasMany
     */
    public function couponId()
    {
        return $this->hasMany(StoreProductCoupon::class, 'product_id', 'id');
    }

    /**
     * 优惠券名称一对多
     * @return \think\model\relation\HasMany
     */
    public function coupons()
    {
        return $this->hasMany(StoreProductCoupon::class, 'product_id', 'id');
    }

    /**
     * 评论一对多
     * @return \think\model\relation\HasMany
     */
    public function star()
    {
        return $this->hasMany(StoreProductReply::class, 'product_id', 'id')->where('is_del', 0)->field('product_score,product_id');
    }

    /**
     * 分类一对多
     * @return \think\model\relation\HasMany
     */
    public function cateName()
    {
        return $this->hasMany(StoreProductCate::class, 'product_id', 'id')->with('cateName');
    }

    /**
     * 轮播图获取器
     * 将 JSON 字符串解码为数组，与旧 StoreProduct 模型行为一致
     * @param $value
     * @return array
     */
    public function getSliderImageAttr($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?: [];
    }

    /**
     * 是否显示搜索器
     * @param $query
     * @param $value
     */
    public function searchIsShowAttr($query, $value)
    {
        if ($value != -1) $query->where('is_show', $value ?? 1);
    }

    /**
     * ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIdAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('id', $value);
        } else {
            $query->where('id', $value);
        }
    }

    /**
     * 是否删除搜索器
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        $query->where('is_del', $value ?: 0);
    }

    /**
     * 商户ID搜索器
     * @param Model $query
     * @param $value
     */
    public function searchMerIdAttr($query, $value)
    {
        $query->where('mer_id', $value ?? 0);
    }

    /**
     * keyword搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchStoreNameAttr($query, $value, $data)
    {
        if ($value !== '') {
            // 中台商品表 eb_product_center 无 store_name/store_info/bar_code 列，映射为 name/intro
            $field = 'keyword|name|intro|id';
            if (is_string($value)) {
                $query->whereLike($field, htmlspecialchars("%" . trim($value) . "%"));
            } elseif (is_array($value) && count($value) > 0) {
                $query->where(function ($q) use ($value, $field) {
                    $data = [];
                    foreach ($value as $k) {
                        $data[] = [$field, 'like', "%" . trim($k) . "%"];
                    }
                    $q->whereOr($data);
                });
            }
        }
    }

    /**
     * 标签商品搜索器
     * @param Model $query
     * @param int $value
     */
    public function searchLabelIdAttr($query, $value)
    {
        // 中台表使用 label_list 字段存储标签ID
        $query->whereFindInSet('label_list', $value);
    }

    /**
     * SPU搜索器
     * @param Model $query
     * @param int $value
     */
    public function searchSpuAttr($query, $value)
    {
        $query->where('spu', $value);
    }

    /**
     * 库存搜索器
     * @param Model $query
     * @param int $value
     */
    public function searchStockAttr($query, $value)
    {
        $query->where('stock', $value);
    }

    /**
     * 会员专属商品搜索器
     * 传入当前用户 is_money_level（未登录/非会员为 0）：隐藏 vip_product=1（仅会员可见可买）
     * vip_product=2（所有人可见仅会员可买）仍展示
     * @param Model $query
     * @param mixed $value
     */
    public function searchVipUserAttr($query, $value)
    {
        // 未登录或非付费会员：仅保留不限制(0)与所有人可见仅会员可买(2)
        // 必须用 whereIn，禁止 whereOr 破坏外层 is_show/is_del 等条件
        if ((int)$value <= 0) {
            $query->whereIn('vip_product', [0, 2]);
        }
    }

    /**
     * 是否预售商品
     * @param $query
     * @param $value
     */
    public function searchIsPresaleAttr($query, $value)
    {
        if ($value >= 0) {
            $query->where('presale', $value);
        }
    }

    /**
     * 分类搜索器
     * @param Model $query
     * @param int $value
     */
    public function searchCateIdAttr($query, $value)
    {
        if (!$value) {
            return;
        }
        $cateIds = toIntArray($value);
        if (!$cateIds) {
            return;
        }
        // 展开传入分类及其子分类，再按商品 cate_id 匹配（与列表/用券逻辑一致）
        try {
            $categoryRows = app()->make(\app\dao\product\product\StoreCategoryDao::class)->getAllById($cateIds) ?: [];
            $expanded = array_values(array_unique(array_map('intval', array_column($categoryRows, 'id'))));
            if ($expanded) {
                $cateIds = $expanded;
            }
        } catch (\Throwable $e) {
            // 分类服务不可用时回退为原始 id
        }
        $query->where(function ($q) use ($cateIds) {
            foreach ($cateIds as $index => $cid) {
                if ($index === 0) {
                    $q->whereFindInSet('cate_id', $cid);
                } else {
                    $q->whereOr(function ($sub) use ($cid) {
                        $sub->whereFindInSet('cate_id', $cid);
                    });
                }
            }
        });
    }

    /**
     * 商品数量条件搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchTypeAttr($query, $value, $data)
    {
        switch ((int)$value) {
            case 1:
                $query->where(['is_show' => 1, 'is_del' => 0]);
                break;
            case 2:
                $query->where(['is_show' => 0, 'is_del' => 0]);
                break;
            case 3:
                $query->where(['is_del' => 0, 'vip_product' => 0]);
                break;
            case 4:
                $query->where(['is_del' => 0])->where('stock', '<=', 0);
                break;
            case 5:
                if (isset($data['store_stock']) && $data['store_stock']) {
                    $store_stock = $data['store_stock'];
                    $query->where(['is_show' => 1, 'is_del' => 0])->where('stock', '<', $store_stock)->where('stock', '>', 0);
                } else {
                    $query->where(['is_show' => 1, 'is_del' => 0])->where('stock', '>', 0);
                }
                break;
            case 6:
                $query->where(['is_del' => 1]);
                break;
            case 7:
                $query->where(['is_del' => 0, 'vip_product' => 0, 'is_virtual' => 0]);
                break;
        }
    }

    /**
     * 在当前id中查询
     * @param $query
     * @param $value
     */
    public function searchIdsAttr($query, $value)
    {
        if (is_string($value)) {
            if ($value !== '') {
                $value = explode(',', $value);
            } else {
                $value = [];
            }
        }
        if (count($value)) $query->whereIn('id', $value);
    }

    /**
     * 不在当前id中查询
     * @param $query
     * @param $value
     */
    public function searchNotIdsAttr($query, $value)
    {
        if (!empty($value)) $query->whereNotIn('id', $value);
    }

    /**
     * 自定义表单搜索器
     * @param $query
     * @param $value
     */
    public function searchCustomFormAttr($query, $value)
    {
        if ($value !== '') $query->whereLike('custom_form', '%' . $value . '%');
    }

    /**
     * 虚拟类型搜索器
     * @param $query
     * @param $value
     */
    public function searchProductTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('product_type', $value);
    }

    /**
     * 规格类型搜索器
     * @param $query
     * @param $value
     */
    public function searchSpecTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('spec_type', $value);
    }

    /**
     * 是否礼品搜索器
     * @param $query
     * @param $value
     */
    public function searchIsGiftAttr($query, $value)
    {
        if ($value !== '') $query->where('is_gift', $value);
    }

    /**
     * 会员专属商品搜索器
     * @param $query
     * @param $value
     */
    public function searchVipProductAttr($query, $value)
    {
        if ($value !== '') $query->where('vip_product', $value);
    }

    /**
     * 价格区间搜索器
     * @param $query
     * @param $value
     */
    public function searchPriceSAttr($query, $value)
    {
        if (count($value) == 2 && ($value[0] !== '' || $value[1] !== '')) {
            if ($value[0] !== '' && $value[1] !== '') {
                $query->whereBetween('price', [$value[0], $value[1]]);
            } elseif ($value[0] !== '') {
                $query->where('price', '>=', $value[0]);
            } elseif ($value[1] !== '') {
                $query->where('price', '<=', $value[1]);
            }
        }
    }

    /**
     * 库存区间搜索器
     * @param $query
     * @param $value
     */
    public function searchStockSAttr($query, $value)
    {
        if (count($value) == 2 && ($value[0] !== '' || $value[1] !== '')) {
            if ($value[0] !== '' && $value[1] !== '') {
                $query->whereBetween('stock', [$value[0], $value[1]]);
            } elseif ($value[0] !== '') {
                $query->where('stock', '>=', $value[0]);
            } elseif ($value[1] !== '') {
                $query->where('stock', '<=', $value[1]);
            }
        }
    }

    /**
     * 销量区间搜索器
     * @param $query
     * @param $value
     */
    public function searchSalesSAttr($query, $value)
    {
        if (count($value) == 2 && ($value[0] !== '' || $value[1] !== '')) {
            if ($value[0] !== '' && $value[1] !== '') {
                $query->whereBetween('sales', [$value[0], $value[1]]);
            } elseif ($value[0] !== '') {
                $query->where('sales', '>=', $value[0]);
            } elseif ($value[1] !== '') {
                $query->where('sales', '<=', $value[1]);
            }
        }
    }

    /**
     * 标签搜索器
     * @param $query
     * @param $value
     */
    public function searchStoreLabelIdAttr($query, $value)
    {
        if (count($value)) {
            $query->where(function ($query) use ($value) {
                foreach ($value as $item) {
                    $query->whereOr('FIND_IN_SET(:value, label_list)', ['value' => $item]);
                }
            });
        }
    }

    /**
     * 配送方式搜索器
     * @param $query
     * @param $value
     */
    public function searchLogisticsAttr($query, $value)
    {
        if ($value !== '') $query->whereFindInSet('logistics', $value);
    }

}
