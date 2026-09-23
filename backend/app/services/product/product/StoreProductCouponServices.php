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
declare (strict_types=1);

namespace app\services\product\product;

use crmeb\basic\BaseService;
use app\dao\product\product\StoreProductCouponDao;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;

/**
 *
 * Class StoreProductCouponServices
 * @package app\services\coupon
 * @method delete($id, ?string $key = null) 删除
 */
class StoreProductCouponServices extends BaseService
{
     /**
       * @var StoreProductCouponDao
       */
    protected $dao;

     /**
     * StoreProductCouponServices constructor.
     * @param StoreProductCouponDao $dao
     */
    public function __construct(StoreProductCouponDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 商品关联优惠券
     * @param int $id
     * @param array $coupon_ids
     * @return bool
     */
    public function setCoupon(int $id, array $coupon_ids)
    {
        $this->dao->delete(['product_id' => $id]);
        if ($coupon_ids) {
            $data = $data_all = [];
            $data['product_id'] = $id;
            $data['add_time'] = time();
            foreach ($coupon_ids as $cid) {
                if (!empty($cid) && (int)$cid) {
                    $data['issue_coupon_id'] = $cid;
                    $data_all[] = $data;
                }
            }
            $res = true;
            if ($data_all) {
                $res = $this->dao->saveAll($data_all);
            }
            if (!$res) throw new AdminException('关联优惠券失败');
        }
        return true;
    }

    /**
     * 获取下单赠送优惠券
     * @param int $uid
     * @param $orderId
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getOrderProductCoupon(int $uid, $orderId)
    {
        /** @var OrderCenterDao $storeOrder */
        $storeOrder = app()->make(OrderCenterDao::class);
        // 中台表订单号字段为 order_no，非 order_id
        $order = $storeOrder->getOne(['order_no' => $orderId]);
        if (!$order || $order['uid'] != $uid) {
            throw new ApiException('订单不存在');
        }
        $key = 'order_product_coupon_' . $uid . '_' . $order['id'];
        return CacheService::get($key, []);
    }

    /**
     * 下单赠送优惠劵
     * @param int $uid
     * @param $orderId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function giveOrderProductCoupon(int $uid, $orderId)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException('用户不存在');
        }
        /** @var OrderCenterDao $storeOrder */
        $storeOrder = app()->make(OrderCenterDao::class);
        // 中台表通过订单号 order_no 查询，避免传入旧表 id 与order_center.id不一致
        $order = $storeOrder->getOne(['order_no' => $orderId]);
        if (!$order || $order['uid'] != $uid) {
            throw new ApiException('订单不存在');
        }
        // 中台化读取：基于 extra_info.items 提取商品ID（旧订单购物车明细服务已删除）
        $productIds = app()->make(OrderCenterServices::class)->getCartProductIds($order['id']);
        $list = [];
        if ($productIds) {
            $couponList = $this->dao->getProductCoupon($productIds);
            if ($couponList) {
                /** @var StoreCouponIssueServices $storeCoupon */
                $storeCoupon = app()->make(StoreCouponIssueServices::class);
                $list = $storeCoupon->orderPayGiveCoupon($uid, array_column($couponList, 'issue_coupon_id'));
                foreach ($list as &$item) {
                    $item['add_time'] = date('Y-m-d', $item['add_time']);
                    $item['end_time'] = date('Y-m-d', $item['end_time']);
                }
            }
        }
        $key = 'order_product_coupon_' . $uid . '_' . $order['id'];
        CacheService::set($key, $list, 7200);
        return true;
    }
}
