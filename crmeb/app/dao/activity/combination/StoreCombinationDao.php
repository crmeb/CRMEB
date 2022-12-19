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

namespace app\dao\activity\combination;

use app\dao\BaseDao;
use app\model\activity\combination\StoreCombination;

/**
 *
 * Class StoreCombinationDao
 * @package app\dao\activity
 */
class StoreCombinationDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreCombination::class;
    }

    /**
     * 搜索
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    public function search(array $where = [])
    {
        return parent::search($where)->when(isset($where['pinkIngTime']), function ($query) use ($where) {
            $time = time();
            [$startTime, $stopTime] = is_array($where['pinkIngTime']) ? $where['pinkIngTime'] : [$time, $time];
            $query->where('start_time', '<=', $startTime)->where('stop_time', '>=', $stopTime);
        })->when(isset($where['storeProductId']), function ($query) {
            $query->where('product_id', 'IN', function ($query) {
                $query->name('store_product')->where('is_show', 1)->where('is_del', 0)->field('id');
            });
        })->when(isset($where['sid']) && $where['sid'], function ($query) use ($where) {
            $query->whereIn('product_id', function ($query) use ($where) {
                $query->name('store_product_cate')->where('cate_id', $where['sid'])->field('product_id')->select();
            });
        })->when(isset($where['cid']) && $where['cid'], function ($query) use ($where) {
            $query->whereIn('product_id', function ($query) use ($where) {
                $query->name('store_product_cate')->whereIn('cate_id', function ($query) use ($where) {
                    $query->name('store_category')->where('pid', $where['cid'])->field('id')->select();
                })->field('product_id')->select();
            });
        });
    }

    /**
     * 获取指定条件下的条数
     * @param array $where
     * @return int
     */
    public function count(array $where = []): int
    {
        return $this->search($where)->count();
    }

    /**
     * 拼团商品列表
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
        return $this->search($where)->where('is_del', 0)->with('getPrice')
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
    /**获取列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getHomeList(array $where, int $page = 0, int $limit = 0)
    {
        return $this->search($where)
            ->when($page != 0 && $limit != 0, function ($query) use ($page, $limit) {
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
            })->with(['getPrice'])->select()->toArray();
    }

    /**
     * 获取正在进行拼团的商品以数组形式返回
     * @param array $ids ids 为空返回所有
     * @param array $field
     * @return array
     */
    public function getPinkIdsArray(array $ids = [], array $field = [])
    {
        return $this->search(['is_del' => 0, 'is_show' => 1, 'pinkIngTime' => 1])
            ->when($ids, function ($query) use ($ids) {
                $query->whereIn('product_id', $ids);
            })->column(implode(',', $field), 'product_id');
    }

    /**
     * 获取拼团列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function combinationList(array $where, int $page, int $limit)
    {
        return $this->search($where)->with('getPrice')->page($page, $limit)->order('sort desc,id desc')->select()->toArray();
    }
    /**
     * 条件获取数量
     * @param array $where
     * @return int
     */
    public function getCount(array $where)
    {
        return $this->search($where)->count();
    }
    /**
     * 页面设计获取商拼团列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function diyCombinationList(array $where, int $page, int $limit){
        return $this->search($where)->with('getCategory')->page($page, $limit)->order('sort desc,id desc')->select()->toArray();
    }
    /**
     * 根据id获取拼团数据
     * @param array $ids
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function idCombinationList(array $ids, string $field)
    {
        return $this->getModel()->whereIn('id', $ids)->field($field)->select()->toArray();
    }

    /**
     * 获取一条拼团数据
     * @param int $id
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function validProduct(int $id, string $field)
    {
        $where = ['is_show' => 1, 'is_del' => 0, 'pinkIngTime' => true];
        return $this->search($where)->where('id', $id)->with(['total'])->field($field)->order('add_time desc')->find();
    }

    /**
     * 获取推荐拼团
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCombinationHost()
    {
        $where = ['is_del' => 0, 'is_host' => 1, 'is_show' => 1, 'pinkIngTime' => true];
        return $this->search($where)->order('id desc')->select()->toArray();
    }
}
