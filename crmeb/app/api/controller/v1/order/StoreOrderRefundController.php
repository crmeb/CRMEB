<?php

namespace app\api\controller\v1\order;

use app\Request;
use app\services\order\StoreOrderRefundServices;

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
        return app('json')->successful($orderData);
    }

    /**
     * 取消申请
     * @param $id
     * @return mixed
     */
    public function cancelApply(Request $request, $uni)
    {
        if (!strlen(trim($uni))) return app('json')->fail('参数错误');
        $orderRefund = $this->services->get(['order_id' => $uni, 'is_cancel' => 0]);
        if (!$orderRefund || $orderRefund['uid'] != $request->uid()) {
            return app('json')->fail('订单不存在');
        }
        if (!in_array($orderRefund['refund_type'], [1, 2, 4, 5])) {
            return app('json')->fail('当前状态不能取消申请');
        }
        $this->services->update($orderRefund['id'], ['is_cancel' => 1]);
        $this->services->cancelOrderRefundCartInfo((int)$orderRefund['id'], (int)$orderRefund['store_order_id'], $orderRefund);
        return app('json')->success('取消成功');
    }

    /**
     * 用户退货提交快递单号
     * @param Request $request
     * @param StoreOrderRefundServices $services
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
        if ($data['id'] == '') return app('json')->fail('参数错误!');
        $res = $this->services->editRefundExpress($data);
        if ($res)
            return app('json')->successful('提交成功');
        else
            return app('json')->fail('提交失败');
    }

    /**
     * 删除退款单
     * @param Request $request
     * @param $order_id
     * @return mixed
     */
    public function delRefund(Request $request, $uni)
    {
        $res = $this->services->update(['order_id' => $uni, 'uid' => $request->uid()], ['is_del' => 1]);
        if ($res)
            return app('json')->successful('删除成功');
        else
            return app('json')->fail('删除失败');
    }
}
