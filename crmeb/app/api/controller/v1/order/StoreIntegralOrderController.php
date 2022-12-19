<?php


namespace app\api\controller\v1\order;


use app\Request;
use app\services\activity\integral\StoreIntegralOrderServices;
use app\services\activity\integral\StoreIntegralServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\shipping\ExpressServices;
use crmeb\services\CacheService;

class StoreIntegralOrderController
{
    protected $services;

    public function __construct(StoreIntegralOrderServices $services)
    {
        $this->services = $services;
    }

    /**
     * 订单确认
     * @param Request $request
     * @return mixed
     */
    public function confirm(Request $request)
    {
        [$unique, $num] = $request->postMore([
            'unique',
            'num'
        ], true);
        if (!$unique) {
            return app('json')->fail(410201);
        }
        $user = $request->user()->toArray();
        return app('json')->success($this->services->getOrderConfirmData($user, $unique, $num));
    }

    /**
     * 订单创建
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create(Request $request, StoreProductAttrValueServices $storeProductAttrValueServices, StoreIntegralServices $storeIntegralServices)
    {
        $uid = (int)$request->uid();
        [$addressId, $mark, $unique, $num] = $request->postMore([
            [['addressId', 'd'], 0],
            ['mark', ''],
            ['unique', ''],
            [['num', 'd'], 0]
        ], true);
        $productInfo = $storeProductAttrValueServices->uniqueByField($unique);
        if (!$productInfo || !isset($productInfo['storeIntegral']) || !$productInfo['storeIntegral']) {
            return app('json')->fail(410202);
        }
        $productInfo = is_object($productInfo) ? $productInfo->toArray() : $productInfo;

        $num = (int)$num;
        //判断积分商品限量
        $unique = $storeIntegralServices->checkoutProductStock($uid, $productInfo['product_id'], $num, $unique);
        try {
            //弹出队列
            if (!CacheService::popStock($unique, $num, 4)) {
                return app('json')->fail(410296);
            }
            $order = $this->services->createOrder($uid, $addressId, $mark, $request->user()->toArray(), $num, $productInfo);
        } catch (\Throwable $e) {
            //生成失败归还库存
            CacheService::setStock($unique, $num, 4, false);
            return app('json')->fail($e->getMessage());
        }
        return app('json')->status('success', 410203, ['orderId' => $order['order_id']]);
    }

    /**
     * 订单详情
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function detail(Request $request, $uni)
    {
        if (!strlen(trim($uni))) return app('json')->fail(100100);
        $order = $this->services->getOne(['order_id' => $uni, 'is_del' => 0]);
        if (!$order) return app('json')->fail(410173);
        $order = $order->toArray();
        $orderData = $this->services->tidyOrder($order);
        return app('json')->success($orderData);
    }

    /**
     * 订单列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $where['uid'] = $request->uid();
        $where['is_del'] = 0;
        $where['is_system_del'] = 0;
        $list = $this->services->getOrderApiList($where);
        return app('json')->success($list);
    }

    /**
     * 订单收货
     * @param Request $request
     * @return mixed
     */
    public function take(Request $request)
    {
        list($order_id) = $request->postMore([
            ['order_id', ''],
        ], true);
        if (!$order_id) return app('json')->fail(100100);
        $order = $this->services->takeOrder($order_id, (int)$request->uid());
        if ($order) {
            return app('json')->success(410204);
        } else
            return app('json')->fail(410205);
    }

    /**
     * 订单 查看物流
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function express(Request $request, ExpressServices $expressServices, $uni)
    {
        if (!$uni || !($order = $this->services->getUserOrderDetail($uni, $request->uid()))) return app('json')->fail(410173);
        if ($order['delivery_type'] != 'express' || !$order['delivery_id']) return app('json')->fail(410206);
        $cacheName = 'integral' . $order['order_id'] . $order['delivery_id'];
        return app('json')->success([
            'order' => $order,
            'express' => [
                'result' => ['list' => $expressServices->query($cacheName, $order['delivery_id'], $order['delivery_code'], $order['user_phone'])
                ]
            ]
        ]);
    }

    /**
     * 订单删除
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        [$order_id] = $request->postMore([
            ['order_id', ''],
        ], true);
        if (!$order_id) return app('json')->fail(100100);
        $res = $this->services->removeOrder($order_id, (int)$request->uid());
        if ($res) {
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }
}
