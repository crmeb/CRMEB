<?php


namespace app\adminapi\controller\v1\order;


use app\adminapi\controller\AuthController;
use app\services\order\StoreOrderServices;
use think\facade\App;

class RefundOrder extends AuthController
{
    /**
     * StoreOrder constructor.
     * @param App $app
     * @param StoreOrderServices $service
     * @method temp
     */
    public function __construct(App $app, StoreOrderServices $service)
    {
        parent::__construct($app);
        $this->services = $service;
    }

    /**
     * 退款订单列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRefundList()
    {
        $where = $this->request->getMore([
            ['order_id', ''],
            ['time', '', '', 'refund_reason_time'],
            ['refund_type', 0]
        ]);
        return app('json')->success($this->services->refundList($where));
    }

    /**
     * 商家同意退款
     * @return mixed
     */
    public function agreeRefund()
    {
        [$order_id] = $this->request->getMore([
            ['order_id', '']
        ], true);
        $this->services->agreeRefund($order_id);
        return app('json')->success('操作成功');
    }
}