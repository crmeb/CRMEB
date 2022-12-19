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

namespace app\services\order;


use app\dao\order\StoreOrderDao;
use app\services\activity\combination\StorePinkServices;
use app\services\BaseServices;
use app\services\system\store\SystemStoreStaffServices;
use app\services\user\UserServices;
use crmeb\exceptions\ApiException;

/**
 * 核销订单
 * Class StoreOrderWriteOffServices
 * @package app\sservices\order
 */
class StoreOrderWriteOffServices extends BaseServices
{

    /**
     * 构造方法
     * StoreOrderWriteOffServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 订单核销
     * @param string $code
     * @param int $confirm
     * @param int $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function writeOffOrder(string $code, int $confirm, int $uid = 0)
    {
        $orderInfo = $this->dao->getOne(['verify_code' => $code, 'paid' => 1, 'refund_status' => 0, 'is_del' => 0]);
        if (!$orderInfo) {
            throw new ApiException(410173);
        }
        if (!$orderInfo['verify_code'] || ($orderInfo->shipping_type != 2 && $orderInfo->delivery_type != 'send')) {
            throw new ApiException(410267);
        }
        /** @var StoreOrderRefundServices $storeOrderRefundServices */
        $storeOrderRefundServices = app()->make(StoreOrderRefundServices::class);
        if ($storeOrderRefundServices->count(['store_order_id' => $orderInfo['id'], 'refund_type' => [1, 2, 4, 5], 'is_cancel' => 0, 'is_del' => 0])) {
            throw new ApiException(410268);
        }
        if ($uid) {
            $isAuth = true;
            switch ($orderInfo['shipping_type']) {
                case 1://配送订单
                    /** @var DeliveryServiceServices $deliverServiceServices */
                    $deliverServiceServices = app()->make(DeliveryServiceServices::class);
                    $isAuth = $deliverServiceServices->getCount(['uid' => $uid, 'status' => 1]) > 0;
                    break;
                case 2://自提订单
                    /** @var SystemStoreStaffServices $storeStaffServices */
                    $storeStaffServices = app()->make(SystemStoreStaffServices::class);
                    $isAuth = $storeStaffServices->getCount(['uid' => $uid, 'verify_status' => 1, 'status' => 1]) > 0;
                    break;
            }
            if (!$isAuth) {
                throw new ApiException(410269);
            }
        }
        if ($orderInfo->status == 2) {
            throw new ApiException(410270);
        }
        /** @var StoreOrderCartInfoServices $orderCartInfo */
        $orderCartInfo = app()->make(StoreOrderCartInfoServices::class);
        $cartInfo = $orderCartInfo->getOne([
            ['cart_id', '=', $orderInfo['cart_id'][0]]
        ], 'cart_info');
        if ($cartInfo) $orderInfo['image'] = $cartInfo['cart_info']['productInfo']['image'];
        if ($orderInfo->shipping_type == 2) {
            if ($orderInfo->status > 0) {
                throw new ApiException(410270);
            }
        }
        if ($orderInfo->combination_id && $orderInfo->pink_id) {
            /** @var StorePinkServices $services */
            $services = app()->make(StorePinkServices::class);
            $res = $services->getCount([['id', '=', $orderInfo->pink_id], ['status', '<>', 2]]);
            if ($res) throw new ApiException(410271);
        }
        if ($confirm == 0) {
            /** @var UserServices $services */
            $services = app()->make(UserServices::class);
            $orderInfo['nickname'] = $services->value(['uid' => $orderInfo['uid']], 'nickname');
            return $orderInfo->toArray();
        }
        $orderInfo->status = 2;
        if ($uid) {
            if ($orderInfo->shipping_type == 2) {
                $orderInfo->clerk_id = $uid;
            }
        }
        if ($orderInfo->save()) {
            /** @var StoreOrderTakeServices $storeOrdeTask */
            $storeOrdeTask = app()->make(StoreOrderTakeServices::class);
            $re = $storeOrdeTask->storeProductOrderUserTakeDelivery($orderInfo);
            if (!$re) {
                throw new ApiException(410272);
            }
            return $orderInfo->toArray();
        } else {
            throw new ApiException(410272);
        }
    }
}
