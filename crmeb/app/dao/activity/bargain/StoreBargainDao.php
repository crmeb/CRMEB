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
declare (strict_types=1);

namespace app\dao\activity\bargain;

use app\dao\BaseDao;
use app\model\activity\bargain\StoreBargain;

/**
 *
 * Class StoreBargainDao
 * @package app\dao\activity
 */
class StoreBargainDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreBargain::class;
    }

    /**
     * 获取砍价列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page = 0, int $limit = 0)
    {
        return $this->search($where)->where('is_del', 0)
            ->when(isset($where['start_status']) && $where['start_status'] !== '', function ($query) use ($where) {
                $time = time();
                switch ($where['start_status']) {
                    case -1:
                        $query->where('stop_time', '<', $time);
                        break;
                    case 0:
                        $query->where('start_time', '>', $time);
                        break;
                    case 1:
                        $query->where('start_time', '<=', $time)->where('stop_time', '>=', $time);
                        break;
                }
            })->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->order('sort desc,id desc')->select()->toArray();
    }

    /**
     * 获取活动开启中的砍价id以数组形式返回
     * @param array $ids 为空返回所有
     * @param array $field
     * @return array
     */
    public function getBargainIdsArray(array $ids = [], array $field = [])
    {
        return $this->search(['is_del' => 0, 'status' => 1])->where('start_time', '<=', time())
            ->where('stop_time', '>=', time())
            ->when($ids, function ($query) use ($ids) {
                $query->whereIn('product_id', $ids);
            })->column(implode(',', $field), 'product_id');
    }

    /**
     * 根据id获取砍价数据
     * @param array $ids
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function idByBargainList(array $ids, string $field)
    {
        return $this->getModel()->whereIn('id', $ids)->field($field)->select()->toArray();
    }

    /**
     * 正在开启的砍价活动
     * @param int $status
     * @return StoreBargain
     */
    public function validWhere(int $status = 1)
    {
        return $this->getModel()->where('is_del', 0)->where('status', $status)->where('start_time', '<', time())->where('stop_time', '>', time());

    }

    /**
     * 砍价列表
     * @param int $page
     * @param int $limit
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bargainList(int $page, int $limit, string $field = '*')
    {
        return $this->search(['is_del' => 0, 'status' => 1])
            ->where('start_time', '<=', time())
            ->where('stop_time', '>=', time())
            ->where('product_id', 'IN', function ($query) {
                $query->name('store_product')->where('is_show', 1)->where('is_del', 0)->field('id');
            })->with('product')->field($field)->page($page, $limit)->order('sort DESC,id DESC')->select()->toArray();
    }

    /**
     * 后台页面设计获取砍价列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function DiyBargainList(array $where, int $page, int $limit)
    {
        return $this->search($where)
            ->when(isset($where['sid']) && $where['sid'], function ($query) use ($where) {
                $query->whereIn('id', function ($query) use ($where) {
                    $query->name('store_product_cate')->where('cate_id', $where['sid'])->field('product_id')->select();
                });
            })->when(isset($where['cid']) && $where['cid'], function ($query) use ($where) {
                $query->whereIn('id', function ($query) use ($where) {
                    $query->name('store_product_cate')->whereIn('cate_id', function ($query) use ($where) {
                        $query->name('store_category')->where('pid', $where['cid'])->field('id')->select();
                    })->field('product_id')->select();
                });
            })->where('start_time', '<=', time())
            ->where('stop_time', '>=', time())
            ->where('product_id', 'IN', function ($query) {
                $query->name('store_product')->where('is_show', 1)->where('is_del', 0)->field('id');
            })->with('product')->page($page, $limit)->order('sort DESC,id DESC')->select()->toArray();
    }

    /**
     * 首页砍价
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getHomeList(array $where, int $page, int $limit)
    {
        return $this->search($where)
            ->when(isset($where['sid']) && $where['sid'], function ($query) use ($where) {
                $query->whereIn('id', function ($query) use ($where) {
                    $query->name('store_product_cate')->where('cate_id', $where['sid'])->field('product_id')->select();
                });
            })->when(isset($where['cid']) && $where['cid'], function ($query) use ($where) {
                $query->whereIn('id', function ($query) use ($where) {
                    $query->name('store_product_cate')->whereIn('cate_id', function ($query) use ($where) {
                        $query->name('store_category')->where('pid', $where['cid'])->field('id')->select();
                    })->field('product_id')->select();
                });
            })->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
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
            })->when(isset($where['ids']) && $where['ids'], function ($query) use ($where) {
                if ((isset($where['priceOrder']) && $where['priceOrder'] != '') || (isset($where['salesOrder']) && $where['salesOrder'] != '')) {
                    $query->whereIn('id', $where['ids']);
                } else {
                    $query->whereIn('id', $where['ids'])->orderField('id', $where['ids'], 'asc');
                }
            })
            ->where('start_time', '<=', time())
            ->where('stop_time', '>=', time())
            ->where('product_id', 'IN', function ($query) {
                $query->name('store_product')->where('is_show', 1)->where('is_del', 0)->field('id');
            })->with('product')->select()->toArray();
    }

    /**
     * 条件获取数量
     * @param array $where
     * @return int
     */
    public function getCount(array $where)
    {
        return $this->search($where)
            ->when(isset($where['sid']) && $where['sid'], function ($query) use ($where) {
                $query->whereIn('product_id', function ($query) use ($where) {
                    $query->name('store_product_cate')->where('cate_id', $where['sid'])->field('product_id')->select();
                });
            })->when(isset($where['cid']) && $where['cid'], function ($query) use ($where) {
                $query->whereIn('product_id', function ($query) use ($where) {
                    $query->name('store_product_cate')->whereIn('cate_id', function ($query) use ($where) {
                        $query->name('store_category')->where('pid', $where['cid'])->field('id')->select();
                    })->field('product_id')->select();
                });
            })->where('start_time', '<=', time())
            ->where('stop_time', '>=', time())
            ->where('product_id', 'IN', function ($query) {
                $query->name('store_product')->where('is_show', 1)->where('is_del', 0)->field('id');
            })->count();
    }

    /**
     * 修改砍价状态
     * @param int $id
     * @param string $field
     * @return mixed
     */
    public function addBargain(int $id, string $field)
    {
        return $this->getModel()->where('id', $id)->inc($field, 1)->update();
    }
}
