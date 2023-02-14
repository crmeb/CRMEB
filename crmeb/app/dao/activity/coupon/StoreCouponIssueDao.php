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

namespace app\dao\activity\coupon;

use app\dao\BaseDao;
use app\model\activity\coupon\StoreCouponIssue;

/**
 *
 * Class StoreCouponIssueDao
 * @package app\dao\coupon
 */
class StoreCouponIssueDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StoreCouponIssue::class;
    }

    /**
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    public function search(array $where = [])
    {
        return parent::search($where)->when(isset($where['type']) && $where['type'] != '', function ($query) use ($where) {
            if ($where['type'] == 'send') {
                $query->where('receive_type', 3)->where(function ($query1) {
                    $query1->where(function ($query2) {
                        $query2->where('start_time', '<', time())->where('end_time', '>', time());
                    })->whereOr(function ($query3) {
                        $query3->where('start_time', 0)->where('end_time', 0);
                    });
                })->where('status', 1);
            }
        })->when(isset($where['receive_type']) && $where['receive_type'], function ($query) use ($where) {
            $query->where('receive_type', $where['receive_type']);
        });
    }

    /**
     * 获取列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page, int $limit, string $field = '*')
    {
        return $this->search($where)->field($field)
            ->page($page, $limit)->order('id desc')->select()->toArray();
    }

    /**
     * 获取优惠券列表
     * @param int $uid 用户ID
     * @param int $type 0通用，1分类，2商品
     * @param array|int $typeId 分类ID或商品ID
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getIssueCouponList(int $uid, int $type, $typeId, int $page, int $limit)
    {
        return $this->getModel()->where('status', 1)
            ->where('is_del', 0)
            ->where('remain_count > 0 OR is_permanent = 1')
            ->where(function ($query) {
                $query->where('receive_type', 1)->whereOr('receive_type', 4);
            })->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('start_time', '<', time())->where('end_time', '>', time());
                })->whereOr(function ($query) {
                    $query->where('start_time', 0)->where('end_time', 0);
                });
            })
            ->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }])
            ->where('type', $type)
            ->when($type == 1, function ($query) use ($typeId) {
                if ($typeId) {
                    $query->where(function ($query) use ($typeId) {
                        $query->where('id', 'in', function ($query) use ($typeId) {
                            $query->name('store_coupon_product')->whereIn('category_id', $typeId)->field(['coupon_id'])->select();
                        })->whereOr('category_id', 'in', $typeId);
                    });
                }
            })
            ->when($type == 2, function ($query) use ($typeId) {
                if ($typeId) $query->whereFindinSet('product_id', $typeId);
            })
            ->when($page != 0, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->order('sort desc,id desc')->select()->toArray();
    }

    /**
     * PC端获取优惠券
     * @param int $uid
     * @param array $cate_ids
     * @param int $product_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPcIssueCouponList(int $uid, $cate_ids = [], $product_id = 0, int $limit = 0)
    {
        return $this->getModel()->where('status', 1)
            ->where('is_del', 0)
            ->where('remain_count > 0 OR is_permanent = 1')
            ->where(function ($query) {
                $query->where('receive_type', 1)->whereOr('receive_type', 4);
            })->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('start_time', '<', time())->where('end_time', '>', time());
                })->whereOr(function ($query) {
                    $query->where('start_time', 0)->where('end_time', 0);
                });
            })->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }])->where(function ($query) use ($product_id, $cate_ids) {
                if ($product_id != 0 && $cate_ids != []) {
                    $query->whereFindinSet('product_id', $product_id)->whereOr('id', 'in', function ($query) use ($cate_ids) {
                        $query->name('store_coupon_product')->whereIn('category_id', $cate_ids)->field(['coupon_id'])->select();
                    })->whereOr('category_id', 'in', $cate_ids)->whereOr('type', 0);
                }
            })->when($limit > 0, function ($query) use ($limit) {
                $query->limit($limit);
            })->order('sort desc,id desc')->select()->toArray();
    }

    /**
     * 获取优惠券数量
     * @param $productId
     * @param $cateId
     * @return mixed
     */
    public function getIssueCouponCount($productId, $cateId)
    {
        $model = function ($query) {
            $query->where('status', 1)
                ->where('is_del', 0)
                ->where('remain_count > 0 OR is_permanent = 1')
                ->where(function ($query) {
                    $query->where('receive_type', 4)->whereOr('receive_type', 1);
                })->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('start_time', '<', time())->where('end_time', '>', time());
                    })->whereOr(function ($query) {
                        $query->where('start_time', 0)->where('end_time', 0);
                    });
                });
        };
        $count[0] = $this->getModel()->where($model)->where('type', 0)->count();
        $count[1] = $this->getModel()->where($model)->where('type', 1)
            ->when(count($cateId) != 0, function ($query) use ($cateId) {
                $query->where(function ($query) use ($cateId) {
                    $query->where('id', 'in', function ($query) use ($cateId) {
                        $query->name('store_coupon_product')->whereIn('category_id', $cateId)->field(['coupon_id'])->select();
                    })->whereOr('category_id', 'in', $cateId);
                });
            })->count();
        $count[2] = $this->getModel()->where($model)->where('type', 2)
            ->when($productId != 0, function ($query) use ($productId) {
                if ($productId) $query->whereFindinSet('product_id', $productId);
            })->count();
        return $count;
    }

    /**
     * 获取优惠卷详情
     * @param int $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo(int $id)
    {
        return $this->getModel()->where('status', 1)
            ->where('id', $id)
            ->where('is_del', 0)
            ->where('remain_count > 0 OR is_permanent = 1')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('start_time', '<', time())->where('end_time', '>', time());
                })->whereOr(function ($query) {
                    $query->where('start_time', 0)->where('end_time', 0);
                });
            })->find();
    }

    /**
     * 获取金大于额的优惠卷金额
     * @param string $totalPrice
     * @return float
     */
    public function getUserIssuePrice(string $totalPrice)
    {
        return $this->search(['status' => 1, 'is_full_give' => 1, 'is_del' => 0])
            ->where('full_reduction', '<=', $totalPrice)
            ->sum('coupon_price');
    }

    /**
     * 获取新人券
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNewCoupon()
    {
        return $this->getModel()->where('status', 1)
            ->where('is_del', 0)
            ->where('remain_count > 0 OR is_permanent = 1')
            ->where('receive_type', 2)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('start_time', '<', time())->where('end_time', '>', time());
                })->whereOr(function ($query) {
                    $query->where('start_time', 0)->where('end_time', 0);
                });
            })->select()->toArray();
    }

    /**
     * 获取一条优惠券信息
     * @param int $id
     * @return mixed
     */
    public function getCouponInfo(int $id)
    {
        return $this->getModel()->where('id', $id)->where('status', 1)->where('is_del', 0)->find();
    }

    /**
     * 获取满赠、下单、关注赠送优惠券
     * @param array $where
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGiveCoupon(array $where, string $field = '*')
    {
        return $this->getModel()->field($field)
            ->where('status', 1)
            ->where('is_del', 0)
            ->where('remain_count > 0 OR is_permanent = 1')
            ->where($where)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('start_time', '<', time())->where('end_time', '>', time());
                })->whereOr(function ($query) {
                    $query->where('start_time', 0)->where('end_time', 0);
                });
            })->select()->toArray();
    }

    /**
     * 获取商品优惠卷列表
     * @param $where
     * @param $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function productCouponList($where, $field)
    {
        return $this->getModel()->where($where)->field($field)->select()->toArray();
    }

    /**
     * 获取优惠券弹窗列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTodayCoupon($uid)
    {
//        return $this->getModel()->where('receive_type', 1)->where('is_del', 0)->whereDay('add_time')->select()->toArray();
        return $this->getModel()->where('status', 1)
            ->where('is_del', 0)
            ->where('remain_count > 0 OR is_permanent = 1')
            ->where(function ($query) {
                $query->where('receive_type', 1)->whereOr('receive_type', 4);
//                $query->where('receive_type', 1);
            })->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('start_time', '<', time())->where('end_time', '>', time());
                })->whereOr(function ($query) {
                    $query->where('start_time', 0)->where('end_time', 0);
                });
            })->when($uid != 0, function ($query) use ($uid) {
                $query->with(['used' => function ($query) use ($uid) {
                    $query->where('uid', $uid);
                }]);
            })->whereDay('add_time')->order('sort desc,id desc')->select()->toArray();
    }


    /**api数据获取优惠券
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getApiIssueList(array $where)
    {
        return $this->getModel()->where($where)->select()->toArray();
    }

    /**
     * 检测是否有商品券
     * @param $product_id
     * @return bool
     */
    public function checkProductCoupon($product_id)
    {
        return (bool)$this->getModel()->whereFindInSet('product_id', $product_id)->count();
    }
}
