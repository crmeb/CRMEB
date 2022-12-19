<?php

namespace app\api\controller\v1\order;

use app\Request;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;

class StoreOrderRefundController
{
    /**
     * @var StoreOrderRefundServices
     */
    protected $services;

    /**
     * StoreOrderRefundController constructor.
     * @param StoreOrderRefundServices $services
     */
    public function __construct(StoreOrderRefundServices $services)
    {
        $this->services = $services;
    }

    /**
     * 退款订单列表
     * @param Request $request
     * @return mixed
     */
    public function refundList(Request $request)
    {
        $where = $request->getMore([
            ['refund_status', ''],
        ]);
        $where['uid'] = $request->uid();
        $where['is_cancel'] = 0;
        $where['is_del'] = 0;
        $data = $this->services->refundList($where);
        return app('json')->success($data);
    }

    /**
     * 退款单详情
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function refundDetail(Request $request, $uni)
    {
        $orderData = $this->services->refundDetail($uni);
        return app('json')->success($orderData);
    }

    /**
     * 取消申请
     * @param Request $request
     * @param $uni
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cancelApply(Request $request, $uni)
    {
        if (!strlen(trim($uni))) return app('json')->fail(100100);
        $orderRefund = $this->services->get(['order_id' => $uni, 'is_cancel' => 0]);
        if (!$orderRefund || $orderRefund['uid'] != $request->uid()) {
            return app('json')->fail(410173);
        }
        if (!in_array($orderRefund['refund_type'], [1, 2, 4, 5])) {
            return app('json')->fail(410224);
        }
        $this->services->update($orderRefund['id'], ['is_cancel' => 1]);
        $this->services->cancelOrderRefundCartInfo((int)$orderRefund['id'], (int)$orderRefund['store_order_id'], $orderRefund);
        return app('json')->success(100019);
    }

    /**
     * 用户退货提交快递单号
     * @param Request $request
     * @return mixed
     */
    public function applyExpress(Request $request)
    {
        $data = $request->postMore([
            ['id', ''],
            ['refund_express', ''],
            ['refund_phone', ''],
            ['refund_express_name', ''],
            ['refund_img', ''],
            ['refund_explain', ''],
        ]);
        if ($data['id'] == '') return app('json')->fail(100100);
        $res = $this->services->editRefundExpress($data);
        if ($res)
            return app('json')->success(100017);
        else
            return app('json')->fail(100018);
    }

    /**
     * 删除退款单
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function delRefund(Request $request, $uni)
    {
        $oid = $this->services->value(['order_id' => $uni, 'uid' => $request->uid()], 'store_order_id');
        $res = $this->services->update(['order_id' => $uni, 'uid' => $request->uid()], ['is_del' => 1]);
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $orderServices->update($oid, ['is_del' => 1], 'id');
        if ($res)
            return app('json')->success(100002);
        else
            return app('json')->fail(100008);
    }
}
