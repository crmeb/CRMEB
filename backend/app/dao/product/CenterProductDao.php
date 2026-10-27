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
declare(strict_types=1);

namespace app\dao\product;

use crmeb\basic\BaseDao;
use crmeb\trade\product\ProductQueryInterface;
use app\model\product\CenterProductModel;
use think\facade\Config;

/**
 * 中台商品 DAO
 * 操作 eb_product_center 主表
 * 实现 ProductQueryInterface 契约，供 crmeb 核心层通过接口反转依赖
 */
class CenterProductDao extends BaseDao implements ProductQueryInterface
{
    /**
     * 设置模型
     */
    protected function setModel(): string
    {
        return CenterProductModel::class;
    }

    /**
     * 条件获取数量
     * @param array $where
     * @return int
     */
    public function getCount(array $where)
    {
        return $this->search($where, false)->when(isset($where['sid']) && $where['sid'], function ($query) use ($where) {
            $this->applyCategoryFilter($query, $where['sid'], false);
        })->when(isset($where['cid']) && $where['cid'], function ($query) use ($where) {
            $this->applyCategoryFilter($query, $where['cid'], true);
        })->when(!empty($where['coupon_category_id']), function ($query) use ($where) {
            $this->applyCategoryFilter($query, $where['coupon_category_id'], true);
        })->when(isset($where['ids']) && $where['ids'], function ($query) use ($where) {
            if ((isset($where['priceOrder']) && $where['priceOrder'] !== '') || (isset($where['salesOrder']) && $where['salesOrder'] !== '')) {
                $query->whereIn('id', $where['ids']);
            } else {
                $query->whereIn('id', $where['ids'])->orderField('id', $where['ids'], 'asc');
            }
        })->when(isset($where['is_live']) && $where['is_live'] == 1, function ($query) use ($where) {
            $query->whereNotIn('id', function ($query) {
                $query->name('live_goods')->where('is_del', 0)->where('audit_status', '<>', 3)->field('product_id')->select();
            });
        })->count();
    }

    /**
     * 获取商品列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page = 0, int $limit = 0, string $order = '')
    {
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        return $this->search($where, false)->order(($order ? $order . ' ,' : '') . 'sort_order desc,id desc')
            ->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->field([
                '*',
                '(SELECT count(*) FROM `' . $prefix . 'store_product_relation` WHERE `product_id` = `' . $prefix . 'product_center`.`id` AND `type` = \'collect\') as collect',
                '(SELECT count(*) FROM `' . $prefix . 'store_product_relation` WHERE `product_id` = `' . $prefix . 'product_center`.`id` AND `type` = \'like\') as likes',
                '(SELECT count(*) FROM `' . $prefix . 'store_visit` WHERE `product_id` = `' . $prefix . 'product_center`.`id` AND `product_type` = \'product\') as visitor',
            ])->select()->toArray();
    }

    /**
     * 获取商品详情
     * @param int $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo(int $id)
    {
        return $this->search()->with('coupons')->find($id);
    }

    /**
     * 按字段获取商品信息
     * 用于只需部分字段的场景，避免预加载不必要关联
     * @param int $id 商品ID
     * @param array $field 查询字段
     * @return array|\think\Model|null
     */
    public function getProductField(int $id, array $field = ['*'])
    {
        return $this->getModel()->where('id', $id)->field($field)->find();
    }

    /**
     * 统计指定产品类型的商品数量
     * @param string $productType 产品类型标识
     * @return int
     */
    public function countByProductType(string $productType): int
    {
        return (int)$this->getModel()
            ->where('product_type', $productType)
            ->where('is_del', 0)
            ->count();
    }

    /**
     * 汇总指定产品类型的商品销量
     * @param string $productType 产品类型标识
     * @return int|float
     */
    public function sumSalesByProductType(string $productType)
    {
        return $this->getModel()
            ->where('product_type', $productType)
            ->where('is_del', 0)
            ->sum('sales');
    }

    /**
     * 同步冗余总库存（stock 为各类型 SKU 库存的冗余聚合，交易链路扣/回 SKU 后调用）
     * @param int $id 商品ID
     * @param int $delta 变化量，正数加库存、负数减库存
     * @return bool
     */
    public function syncStock(int $id, int $delta): bool
    {
        if ($id <= 0 || $delta === 0) {
            return false;
        }
        $query = $this->getModel()->where('id', $id);
        if ($delta > 0) {
            $query->inc('stock', $delta);
        } else {
            // 冗余字段以 SKU 为准，不做下限校验，避免历史漂移导致同步失败
            $query->dec('stock', -$delta);
        }
        return (int)$query->update() > 0;
    }

    /**
     * 增加冗余销量（支付成功时按已支付数量累加）
     * @param int $id 商品ID
     * @param int $num 数量
     * @return bool
     */
    public function incSales(int $id, int $num): bool
    {
        if ($id <= 0 || $num <= 0) {
            return false;
        }
        return (int)$this->getModel()->where('id', $id)->inc('sales', $num)->update() > 0;
    }

    /**
     * 减少冗余销量（退款成功时按回退数量扣减）
     * @param int $id 商品ID
     * @param int $num 数量
     * @return bool
     */
    public function decSales(int $id, int $num): bool
    {
        if ($id <= 0 || $num <= 0) {
            return false;
        }
        return (int)$this->getModel()->where('id', $id)->where('sales', '>=', $num)->dec('sales', $num)->update() > 0;
    }

    /**
     * 按 ID 列表获取精简商品信息
     * @param array $ids 商品 ID 列表
     * @return array
     */
    public function getSimpleListByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        return $this->getModel()
            ->whereIn('id', $ids)
            ->field('id as product_id,name as store_name,image')
            ->select()
            ->toArray();
    }

    /**
     * 条件获取商品列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param array|string[] $field
     * @param array|string[] $with
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSearchList(array $where, int $page = 0, int $limit = 0, array $field = ['*'], array $with = [])
    {
        if (isset($where['star'])) $with[] = 'star';
        return $this->search($where, false)->with($with)->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when(isset($where['sid']) && $where['sid'], function ($query) use ($where) {
            $this->applyCategoryFilter($query, $where['sid'], false);
        })->when(isset($where['cid']) && $where['cid'], function ($query) use ($where) {
            $this->applyCategoryFilter($query, $where['cid'], true);
        })->when(!empty($where['coupon_category_id']), function ($query) use ($where) {
            $this->applyCategoryFilter($query, $where['coupon_category_id'], true);
        })->when(isset($where['ids']) && $where['ids'], function ($query) use ($where) {
            if ((isset($where['priceOrder']) && $where['priceOrder'] !== '') || (isset($where['salesOrder']) && $where['salesOrder'] !== '')) {
                $query->whereIn('id', $where['ids']);
            } else {
                $query->whereIn('id', $where['ids'])->orderField('id', $where['ids'], 'asc');
            }
        })->when(isset($where['is_live']) && $where['is_live'] == 1, function ($query) use ($where) {
            $query->whereNotIn('id', function ($query) {
                $query->name('live_goods')->where('is_del', 0)->where('audit_status', '<>', 3)->field('product_id')->select();
            });
        })->when(isset($where['priceOrder']) && in_array($where['priceOrder'], ['asc', 'desc'], true), function ($query) use ($where) {
            $query->order('price ' . $where['priceOrder']);
        })->when(isset($where['newsOrder']) && $where['newsOrder'] === 'news', function ($query) {
            $query->order('id desc');
        })->when(isset($where['salesOrder']) && in_array($where['salesOrder'], ['asc', 'desc'], true), function ($query) use ($where) {
            $query->order('sales ' . $where['salesOrder']);
        })->when(!isset($where['ids']), function ($query) use ($where) {
            if (isset($where['timeOrder']) && $where['timeOrder'] == 1) {
                $query->order('id desc');
            } else {
                $query->order('sort_order desc,id desc');
            }
        })->when(!$page && $limit, function ($query) use ($limit) {
            $query->limit($limit);
        })->when(!empty($where['store_label_id']), function ($query) use ($where) {
            $labelIds = toIntArray($where['store_label_id']);
            if (!$labelIds) {
                return;
            }
            $query->where(function ($query) use ($labelIds) {
                foreach ($labelIds as $index => $value) {
                    // 避免循环复用同名绑定导致 PDO 只取最后一次
                    $query->whereOr('FIND_IN_SET(' . (int)$value . ', label_list)');
                }
            });
        })->order('sort_order desc')->field($field)->select()->toArray();
    }

    /**商品列表
     * @param array $where
     * @param $limit
     * @param $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductLimit(array $where, $limit, $field)
    {
        return $this->search($where)->field($field)->order('val', 'desc')->limit($limit)->select()->toArray();

    }

    /**
     * 根据id获取商品数据
     * @param array $ids
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function idByProductList(array $ids, string $field)
    {
        return $this->getModel()->whereIn('id', $ids)->field($field)->select()->toArray();
    }

    /**
     * 按条件获取商品列表
     * 原推荐位查询方法，is_hot/is_new/is_benefit/is_best/is_good 字段已删除，$field 参数保留仅作兼容
     * @param array $where 查询条件
     * @param string $field 保留参数（已废弃，不再用于筛选）
     * @param int $num 限制条数
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecommendProduct(array $where, string $field, int $num = 0, int $page = 0, int $limit = 0)
    {
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        return $this->search($where)->with(['couponId', 'star'])
            ->field(['id', 'image', 'name as store_name', 'intro as store_info', 'cate_id', 'price', 'vip_price', 'original_price as ot_price', 'IFNULL(sales,0) + IFNULL(virtual_sales,0) as sales', 'unit_name', 'sort_order as sort', 'stock'])
            ->when($num, function ($query) use ($num) {
                $query->limit($num);
            })
            ->when($page, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            })
            ->order('sort_order DESC, id desc')->select()->toArray();

    }

    /**
     * 获取加入购物车的商品
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductCartList(array $where = [], array $field = ['*'], int $page = 0, int $limit = 0)
    {
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        return $this->search($where)->when($page, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->field($field)->order('sort_order DESC,id DESC')->select()->toArray();
    }

    /**
     * 获取用户购买热销榜单
     * @param array $where
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserProductHotSale(array $where, int $page = 0, int $limit = 0)
    {
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        // is_hot 字段已删除，热销榜单直接按销量排序
        return $this->search($where)->field(['IFNULL(sales,0) + IFNULL(virtual_sales,0) as sales', 'name as store_name', 'image', 'id', 'price', 'original_price as ot_price', 'stock'])
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            })->order('sales desc')->select()->toArray();
    }

    /**
     * 通过商品id获取商品分类
     * @param array $productIds
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function productIdByCateId(array $productIds)
    {
        return $this->search(['id' => $productIds])->with('cateName')->field('id')->select()->toArray();
    }

    /**
     * @param array $where
     * @param $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductListByWhere(array $where, $field)
    {
        return $this->search($where)->field($field)->select()->toArray();
    }

    /**
     * 获取预售列表
     * @param $where
     * @param $page
     * @param $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAdvanceList($where, $page, $limit)
    {
        $model = $this->getModel()->where('presale', 1)->where('is_del', 0)->where('is_show', 1)->where(function ($query) use ($where) {
            switch ($where['time_type']) {
                case 1:
                    $query->where('presale_start_time', '>', time());
                    break;
                case 2:
                    $query->where('presale_start_time', '<=', time())->where('presale_end_time', '>=', time());
                    break;
                case 3:
                    $query->where('presale_end_time', '<', time());
                    break;
            }
        });
        $count = $model->count();
        $list = $model->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time desc')->select()->toArray();
        return compact('list', 'count');
    }

    /**
     * 预售商品自动到期下架
     */
    public function downAdvance()
    {
        $this->getModel()->where('presale', 1)->where('presale_end_time', '<', time())->update(['is_show' => 0]);
    }

    /**
     * 自定义组件-商品
     * @param $where
     * @param $order
     * @param $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/13
     */
    public function getThemeProduct($where, $order, $limit)
    {
        $model = $this->getModel()->with('cateName')->where('is_del', 0)->where('is_show', 1);
        if (!empty($where['ids'])) {
            $ids = explode(',', $where['ids']);
            $list = $model->whereIn('id', $ids)->order($order)->limit($limit)->select()->toArray();
        } else {
            $list = $model->when(!empty($where['cate_ids']), function ($query) use ($where) {
                $cate_ids = explode(',', $where['cate_ids']);
                $query->whereIn('id', function ($query) use ($cate_ids) {
                    $query->name('store_product_cate')->where('cate_id', 'in', $cate_ids)->whereOr('cate_pid', 'in', $cate_ids)->field('product_id')->select();
                });
            })->order($order)->limit($limit)->select()->toArray();
        }
        return $list;
    }

    /**
     * 按分类筛选商品（与下单品类券校验一致：商品表 cate_id）
     * @param mixed $query
     * @param mixed $categoryId 分类 id 或逗号串/数组
     * @param bool $expandChildren true=展开子分类（一级/品类券）；false=仅匹配自身（二级 sid）
     * @return void
     */
    protected function applyCategoryFilter($query, $categoryId, bool $expandChildren = true): void
    {
        $cateIds = toIntArray($categoryId);
        if (!$cateIds) {
            $query->whereRaw('1 = 0');
            return;
        }
        if ($expandChildren) {
            /** @var \app\dao\product\product\StoreCategoryDao $categoryDao */
            $categoryDao = app()->make(\app\dao\product\product\StoreCategoryDao::class);
            $categoryRows = $categoryDao->getAllById($cateIds) ?: [];
            $cateIds = array_values(array_unique(array_map('intval', array_column($categoryRows, 'id'))));
            if (!$cateIds) {
                $query->whereRaw('1 = 0');
                return;
            }
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
}
