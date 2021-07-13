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
declare (strict_types=1);

namespace app\dao\coupon;

use app\dao\BaseDao;
use app\model\coupon\StoreCouponUser;

/**
 *
 * Class StoreCouponUserDao
 * @package app\dao\coupon
 */
class StoreCouponUserDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreCouponUser::class;
    }

    /**
     * 获取列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', array $with = ['issue'], int $page, int $limit)
    {
        return $this->search($where)->field($field)->with($with)->page($page, $limit)->order('id desc')->select()->toArray();
    }

    /**
     * 使用优惠券修改优惠券状态
     * @param $id
     * @return \think\Model|null
     */
    public function useCoupon(int $id)
    {
        return $this->getModel()->where('id', $id)->update(['status' => 1, 'use_time' => time()]);
    }

    /**
     * 获取指定商品id下的优惠卷
     * @param array $productIds
     * @param int $uid
     * @param string $price
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function productIdsByCoupon(array $productIds, int $uid, string $price)
    {
        return $this->getModel()->whereIn('cid', function ($query) use ($productIds) {
            $query->name('store_coupon_issue')->whereIn('id', function ($q) use ($productIds) {
                $q->name('store_coupon_product')->whereIn('product_id', $productIds)->field('coupon_id')->select();
            })->field(['id'])->select();
        })->with('issue')->where(['uid' => $uid, 'status' => 0])->order('coupon_price DESC')
            ->where('use_min_price', '<=', $price)->select()
            ->where('start_time', '<', time())->where('end_time', '>', time())
            ->hidden(['status', 'is_fail'])->toArray();
    }

    /**
     * 根据商品id获取
     * @param array $cateIds
     * @param int $uid
     * @param string $price
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cateIdsByCoupon(array $cateIds, int $uid, string $price)
    {
        return $this->getModel()->whereIn('cid', function ($query) use ($cateIds) {
            $query->name('store_coupon_issue')->whereIn('category_id', $cateIds)->where('type', 1)->field('id')->select();
        })->where(['uid' => $uid, 'status' => 0])->where('use_min_price', '<=', $price)
            ->where('start_time', '<', time())->where('end_time', '>', time())
            ->order('coupon_price DESC')->with('issue')->select()->hidden(['status', 'is_fail'])->toArray();
    }

    /**
     * 获取当前用户可用的优惠卷
     * @param array $ids
     * @param int $uid
     * @param string $price
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserCoupon(array $ids, int $uid, string $price)
    {
        return $this->getModel()->where(['uid' => $uid, 'status' => 0])->when(count($ids) != 0, function ($query) use ($ids) {
            $query->whereNotIn('id', $ids);
        })->whereIn('cid', function ($query) {
            $query->name('store_coupon_issue')->where('type', 0)->field(['id'])->select();
        })->where('use_min_price', '<=', $price)
            ->where('start_time', '<', time())->where('end_time', '>', time())
            ->order('coupon_price DESC')->with('issue')->select()->hidden(['status', 'is_fail'])->toArray();;
    }

    /**
     * 获取当前用户所有可用的优惠卷
     * @param int $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserAllCoupon(int $uid)
    {
        return $this->getModel()->where(['uid' => $uid, 'status' => 0, 'is_fail' => 0])
            ->where('start_time', '<', time())->where('end_time', '>', time())
            ->order('coupon_price DESC')->with('issue')->select()->hidden(['status', 'is_fail'])->toArray();
    }

    /**
     * 获取列表带排序
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCouponListByOrder(array $where, $order, int $page = 0, int $limit = 0)
    {
        return $this->search($where)->with('issue')->when($page > 0 && $limit > 0, function ($qeury) use ($page, $limit) {
            $qeury->page($page, $limit);
        })->when($order != '', function ($query) use ($order) {
            $query->order($order);
        })->when(isset($where['coupon_ids']), function ($qeury) use ($where) {
            $qeury->whereIn('cid', $where['coupon_ids']);
        })->when(isset($where['status']), function ($qeury) use ($where) {
            if ($where['status'] == 1) {
                $qeury->where(function ($query) {
                    $query->where('status', 1)->whereOr('status', 2);
                });
            } else {
                $qeury->where('status', $where['status']);
            }
        })->select()->toArray();
    }

    /**根据月份查询用户获得的优惠券
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function memberCouponUserGroupBymonth(array $where)
    {
        return $this->search($where)
            ->whereMonth('add_time')
            ->whereIn('cid', $where['couponIds'])
            ->field('count(id) as num,FROM_UNIXTIME(add_time, \'%Y-%m\') as time')
            ->group("FROM_UNIXTIME(add_time, '%Y-%m')")
            ->select()->toArray();
        //echo $this->getModel()->getLastSql();die;

    }

    /**根据时间查询
     * @param array $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserCounponByMonth(array $where)
    {
        return $this->search($where)->whereMonth('add_time')->find();
    }

    /**
     * 获取本月领取的优惠券
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getVipCouponList($uid)
    {
        return $this->getModel()->where('uid', $uid)->whereMonth('add_time')->select()->toArray();
    }
}
