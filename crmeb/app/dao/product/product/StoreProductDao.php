<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\dao\product\product;


use app\dao\BaseDao;
use app\model\product\product\StoreProduct;
use think\facade\Config;
use think\facade\Log;

/**
 * Class StoreProductDao
 * @package app\dao\product\product
 */
class StoreProductDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreProduct::class;
    }

    /**
     * 条件获取数量
     * @param array $where
     * @return int
     */
    public function getCount(array $where)
    {
        return $this->search($where)->when(isset($where['sid']) && $where['sid'], function ($query) use ($where) {
            $query->whereIn('id', function ($query) use ($where) {
                $query->name('store_product_cate')->where('cate_id', $where['sid'])->field('product_id')->select();
            });
        })->when(isset($where['cid']) && $where['cid'], function ($query) use ($where) {
            $query->whereIn('id', function ($query) use ($where) {
                $query->name('store_product_cate')->whereIn('cate_id', function ($query) use ($where) {
                    $query->name('store_category')->where('pid', $where['cid'])->field('id')->select();
                })->field('product_id')->select();
            });
        })->when(isset($where['ids']), function ($query) use ($where) {
            $query->whereIn('id', $where['ids'])->orderField('id', $where['ids'], 'asc');
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
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page = 0, int $limit = 0, string $order = '')
    {
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        return $this->search($where)->order(($order ? $order . ' ,' : '') . 'sort desc,id desc')
            ->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->field([
                '*',
                '(SELECT count(*) FROM `' . $prefix . 'store_product_relation` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = \'collect\') as collect',
                '(SELECT count(*) FROM `' . $prefix . 'store_product_relation` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = \'like\') as likes',
                '(SELECT SUM(stock) FROM `' . $prefix . 'store_product_attr_value` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = 0) as stock',
//                '(SELECT SUM(sales) FROM `' . $prefix . 'store_product_attr_value` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = 0) as sales',
                '(SELECT count(*) FROM `' . $prefix . 'store_visit` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `product_type` = \'product\') as visitor',
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
    public function getSearchList(array $where, int $page = 0, int $limit = 0, array $field = ['*'], array $with = ['couponId', 'description'])
    {
        if (isset($where['star'])) $with[] = 'star';
        return $this->search($where)->with($with)->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->when(isset($where['sid']) && $where['sid'], function ($query) use ($where) {
            $query->whereIn('id', function ($query) use ($where) {
                $query->name('store_product_cate')->where('cate_id', $where['sid'])->field('product_id')->select();
            });
        })->when(isset($where['cid']) && $where['cid'], function ($query) use ($where) {
            $query->whereIn('id', function ($query) use ($where) {
                $query->name('store_product_cate')->whereIn('cate_id', function ($query) use ($where) {
                    $query->name('store_category')->where('pid', $where['cid'])->field('id')->select();
                })->field('product_id')->select();
            });
        })->when(isset($where['coupon_category_id']) && $where['coupon_category_id'] != '', function ($query) use ($where) {
            $query->whereIn('id', function ($query) use ($where) {
                $query->name('store_product_cate')->whereIn('cate_id', function ($query) use ($where) {
                    $query->name('store_category')->whereIn('pid', $where['coupon_category_id'])->field('id')->select();
                })->whereOr('cate_id', 'in', $where['coupon_category_id'])->field('product_id')->select();
            });
        })->when(isset($where['ids']) && $where['ids'], function ($query) use ($where) {
            if ((isset($where['priceOrder']) && $where['priceOrder'] != '') || (isset($where['salesOrder']) && $where['salesOrder'] != '')) {
                $query->whereIn('id', $where['ids']);
            } else {
                $query->whereIn('id', $where['ids'])->orderField('id', $where['ids'], 'asc');
            }
        })->when(isset($where['is_live']) && $where['is_live'] == 1, function ($query) use ($where) {
            $query->whereNotIn('id', function ($query) {
                $query->name('live_goods')->where('is_del', 0)->where('audit_status', '<>', 3)->field('product_id')->select();
            });
        })->when(isset($where['priceOrder']) && $where['priceOrder'] != '', function ($query) use ($where) {
            if ($where['priceOrder'] === 'desc') {
                $query->order("price desc");
            } else {
                $query->order("price asc");
            }
        })->when(isset($where['newsOrder']) && $where['newsOrder'] != '', function ($query) use ($where) {
            if ($where['newsOrder'] === 'news') {
                $query->order("id desc");
            }
        })->when(isset($where['salesOrder']) && $where['salesOrder'] != '', function ($query) use ($where) {
            if ($where['salesOrder'] === 'desc') {
                $query->order("sales desc");
            } else {
                $query->order("sales asc");
            }
        })->when(!isset($where['ids']), function ($query) use ($where) {
            if (isset($where['timeOrder']) && $where['timeOrder'] == 1) {
                $query->order('id desc');
            } else if (isset($where['is_best']) && $where['is_best'] == 1) {
                $query->order('sales desc,sort desc');
            } else {
                $query->order('sort desc,id desc');
            }
        })->when(!$page && $limit, function ($query) use ($limit) {
            $query->limit($limit);
        })->order('sort desc')->field($field)->select()->toArray();
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
     * 获取推荐商品
     * @param string $field
     * @param int $num
     * @param int $page
     * @param int $limit
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
            ->field(['id', 'image', 'store_name', 'store_info', 'cate_id', 'price', 'ot_price', 'IFNULL(sales,0) + IFNULL(ficti,0) as sales', 'unit_name', 'sort', 'activity', 'stock', 'vip_price', 'is_vip'])
            ->when(in_array($field, ['is_best', 'is_new', 'is_benefit', 'is_hot', 'is_vip']), function ($query) use ($field) {
                if ($field != 'is_vip') {
                    $query->where($field, 1);
                } else {
                    $query->where('is_vip', 1)->where('vip_price', '>', 0);
                }
            })
            ->when($num, function ($query) use ($num) {
                $query->limit($num);
            })
            ->when($page, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            })
            ->order(in_array($field, ['is_hot', 'is_best']) ? 'sales DESC,sort DESC, id desc' : 'sort DESC, id desc')->select()->toArray();

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
    public function getProductCartList(array $where, int $page, int $limit, array $field = ['*'])
    {
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        return $this->search($where)->when($page, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->field($field)->order('sort DESC,id DESC')->select()->toArray();
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
        $where['is_hot'] = 1;
        return $this->search($where)->field(['IFNULL(sales,0) + IFNULL(ficti,0) as sales', 'store_name', 'image', 'id', 'price', 'ot_price', 'stock'])
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
}
