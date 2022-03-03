<?php


namespace app\adminapi\controller\v1\order;


use app\adminapi\controller\AuthController;
use app\Request;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;
use app\services\user\UserServices;
use think\facade\App;

class RefundOrder extends AuthController
{
    /**
     * RefundOrder constructor.
     * @param App $app
     * @param StoreOrderRefundServices $service
     * @method temp
     */
    public function __construct(App $app, StoreOrderRefundServices $service)
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
            ['time', ''],
            ['refund_type', 0]
        ]);
        $where['is_cancel'] = 0;
        $where['is_del'] = 0;
        return app('json')->success($this->services->refundList($where));
    }

    /**
     * 订单详情
     * @param $uni
     * @return mixed
     */
    public function getRefundInfo($uni)
    {
        $data['orderInfo'] = $this->services->refundDetail($uni);
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $data['userInfo'] = $userServices->get($data['orderInfo']['uid']);
        return app('json')->success($data);
    }

    /**
     * 商家同意退款
     * @return mixed
     */
    public function agreeExpress($id)
    {
        $this->services->agreeExpress($id);
        return app('json')->success('操作成功');
    }

    /**
     * 修改备注
     * @param $id
     * @return mixed
     */
    public function remark($id)
    {
        $data = $this->request->postMore([['remark', '']]);
        if (!$data['remark'])
            return app('json')->fail('请输入要备注的内容');
        if (!$id)
            return app('json')->fail('缺少参数');

        if (!$order = $this->services->get($id)) {
            return app('json')->fail('修改的订单不存在!');
        }
        $order->remark = $data['remark'];
        if ($order->save()) {
            return app('json')->success('备注成功');
        } else
            return app('json')->fail('备注失败');
    }

    /**
     * 退款表单生成
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function refund($id)
    {
        if (!$id) return app('json')->fail('Data does not exist!');
        return app('json')->success($this->services->refundOrderForm((int)$id));
    }

    /**
     * 订单退款
     * @param Request $request
     * @param StoreOrderServices $services
     * @param $id
     * @return mixed
     */
    public function refundPrice(Request $request, StoreOrderServices $services, $id)
    {
        $data = $request->postMore([
            ['refund_price', 0],
            ['type', 1]
        ]);
        if (!$id) {
            return app('json')->fail('Data does not exist!');
        }
        $orderRefund = $this->services->get($id);
        if (!$orderRefund) {
            return app('json')->fail('Data does not exist!');
        }
        if ($orderRefund['is_cancel'] == 1) {
            return app('json')->fail('用户已取消申请');
        }
        $order = $services->get((int)$orderRefund['store_order_id']);
        if (!$order) {
            return app('json')->fail('Data does not exist!');
        }
        if (!in_array($orderRefund['refund_type'], [1, 5])) {
            return app('json')->fail('售后订单状态不支持该操作');
        }

        if ($data['type'] == 1) {
            $data['refund_type'] = 6;
        } else if ($data['type'] == 2) {
            $data['refund_type'] = 3;
        }
        $data['refunded_time'] = time();
        $type = $data['type'];
        //拒绝退款
        if ($type == 2) {
            $this->services->refuseRefund((int)$id, $data, $orderRefund);
            return app('json')->successful('修改退款状态成功!');
        } else {
            //0元退款
            if ($orderRefund['refund_price'] == 0 && in_array($orderRefund['refund_type'], [1, 5])) {
                $refund_price = 0;
            } else {
                if (!$data['refund_price']) {
                    return app('json')->fail('请输入退款金额');
                }
                if ($orderRefund['refund_price'] == $orderRefund['refunded_price']) {
                    return app('json')->fail('已退完支付金额!不能再退款了');
                }
                $refund_price = $data['refund_price'];

                $data['refunded_price'] = bcadd($data['refund_price'], $orderRefund['refunded_price'], 2);
                $bj = bccomp((string)$orderRefund['refund_price'], (string)$data['refunded_price'], 2);
                if ($bj < 0) {
                    return app('json')->fail('退款金额大于支付金额，请修改退款金额');
                }
            }

            unset($data['type']);
            $refund_data['pay_price'] = $order['pay_price'];
            $refund_data['refund_price'] = $refund_price;
            if ($order['refund_price'] > 0) {
                mt_srand();
                $refund_data['refund_id'] = $order['order_id'] . rand(100, 999);
            }
            //修改订单退款状态
            unset($data['refund_price']);
            if ($this->services->agreeRefund($id, $refund_data)) {
                $this->services->update($id, $data);
                return app('json')->success('退款成功');
            } else {
                $this->services->storeProductOrderRefundYFasle((int)$id, $refund_price);
                return app('json')->fail('退款失败');
            }
        }
    }

    /**
     * 不退款表单结构
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function noRefund($id)
    {
        if (!$id) return app('json')->fail('Data does not exist!');
        return app('json')->success($this->services->noRefundForm((int)$id));
    }

    /**
     * 订单不退款
     * @param StoreOrderRefundServices $services
     * @param $id
     * @return mixed
     */
    public function refuseRefund($id)
    {
        if (!$id || !($orderRefundInfo = $this->services->get($id)))
            return app('json')->fail('订单不存在');
        [$refund_reason] = $this->request->postMore([['refund_reason', '']], true);
        if (!$refund_reason) {
            return app('json')->fail('请输入不退款原因');
        }
        $refundData = [
            'refuse_reason' => $refund_reason,
            'refund_type' => 3,
            'refunded_time' => time()
        ];
        //拒绝退款处理
        $this->services->refuseRefund((int)$id, $refundData, $orderRefundInfo);
        return app('json')->success('Modified success');
    }
}
