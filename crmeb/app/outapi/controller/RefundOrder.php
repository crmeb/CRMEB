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
namespace app\outapi\controller;

use app\Request;
use app\services\order\OutStoreOrderRefundServices;
use think\facade\App;

/**
 * 售后单控制器
 * Class RefundOrder
 * @package app\outapi\controller
 */
class RefundOrder extends AuthController
{
    /**
     * RefundOrder constructor.
     * @param App $app
     * @param OutStoreOrderRefundServices $service
     * @method temp
     */
    public function __construct(App $app, OutStoreOrderRefundServices $service)
    {
        parent::__construct($app);
        $this->services = $service;
    }

    /**
     * 获取售后订单列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst()
    {
        $where = $this->request->getMore([
            ['order_id', ''],
            ['time', ''],
            ['refund_type', 0]
        ]);
        $where['is_cancel'] = 0;

        return app('json')->success($this->services->refundList($where));
    }

    /**
     * 修改备注
     * @param string $order_id 售后单号
     * @return mixed
     */
    public function remark(string $order_id)
    {
        if (!$order_id) return app('json')->fail(100100);
        [$remark] = $this->request->postMore([['remark', '']], true);

        $this->services->remark($order_id, $remark);
        return app('json')->success(100024);
    }

    /**
     * 同意退款
     * @param string $order_id 售后单号
     * @return mixed
     */
    public function agree(string $order_id)
    {
        if (!$order_id) return app('json')->fail(100100);
       $this->services->agree($order_id);
        return app('json')->success(100010);
    }

    /**
     * 订单不退款
     * @param string $order_id 售后单号
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refuse(string $order_id)
    {
        if (!$order_id) return app('json')->fail(100100);
        [$refund_reason] = $this->request->postMore([['refund_reason', '']], true);

        $this->services->refuse($order_id, $refund_reason);
        return app('json')->success(100010);
    }

    /**
     * 订单详情
     * @param string $order_id 售后单号
     * @return mixed
     */
    public function read(string $order_id)
    {
        if (!$order_id) return app('json')->fail(100100);
        $data = $this->services->getInfo($order_id);
        return app('json')->success($data);
    }

    /**
     * 订单退款
     * @param string $order_id 售后单号
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundPrice(string $order_id, Request $request)
    {
        if (!$order_id) return app('json')->fail(100100);
        [$refund_price] = $request->postMore([['refund_price', '']], true);
        $this->services->refundPrice($order_id, $refund_price);
        return app('json')->success(400149);
    }

}
