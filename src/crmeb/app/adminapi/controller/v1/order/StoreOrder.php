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
namespace app\adminapi\controller\v1\order;


use app\adminapi\controller\AuthController;
use app\adminapi\validate\order\StoreOrderValidate;
use app\services\serve\ServeServices;
use app\services\order\{
    StoreOrderDeliveryServices,
    StoreOrderRefundServices,
    StoreOrderStatusServices,
    StoreOrderTakeServices,
    StoreOrderWriteOffServices,
    StoreOrderServices
};
use app\services\pay\OrderOfflineServices;
use app\services\shipping\ExpressServices;
use app\services\user\UserServices;
use think\facade\App;

/**
 * 订单管理
 * Class StoreOrder
 * @package app\adminapi\controller\v1\order
 */
class StoreOrder extends AuthController
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
     * 获取订单类型数量
     * @return mixed
     */
    public function chart()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time'],
            [['type', 'd'], 0],
        ]);
        $data = $this->services->orderCount($where);
        return app('json')->success($data);
    }

    /**
     * 获取订单列表
     * @return mixed
     */
    public function lst()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', ''],
            ['data', '', '', 'time'],
            ['type', ''],
            ['pay_type', ''],
            ['order', ''],
            ['field_key', ''],
        ]);
        $where['shipping_type'] = 1;
        $where['is_system_del'] = 0;
        return app('json')->success($this->services->getOrderList($where, ['*'], ['invoice']));
    }

    /**
     * 核销码核销
     * @param $code 核销码
     * @param int $confirm 确认核销 0=确认，1=核销
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function write_order(StoreOrderWriteOffServices $services)
    {
        [$code, $confirm] = $this->request->getMore([
            ['code', ''],
            ['confirm', 0]
        ], true);
        if (!$code) return app('json')->fail('Lack of write-off code');
        $orderInfo = $services->writeOffOrder($code, (int)$confirm);
        if ($confirm == 0) {
            return app('json')->success('验证成功', $orderInfo);
        }
        return app('json')->success('Write off successfully');
    }

    /**
     * 订单号核销
     * @param StoreOrderWriteOffServices $services
     * @param $order_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function write_update(StoreOrderWriteOffServices $services, $order_id)
    {
        $orderInfo = $this->services->getOne(['order_id' => $order_id, 'is_del' => 0]);
        if ($orderInfo->shipping_type != 2 && $orderInfo->delivery_type != 'send') {
            return app('json')->fail('核销订单未查到!');
        } else {
            if (!$orderInfo->verify_code) {
                return app('json')->fail('Lack of write-off code');
            }
            $orderInfo = $services->writeOffOrder($orderInfo->verify_code, 1);
            if ($orderInfo) {
                return app('json')->success('Write off successfully');
            } else {
                return app('json')->fail('核销失败!');
            }
        }
    }

    /**
     * 修改支付金额等
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($id)
    {
        if (!$id) return app('json')->fail('Data does not exist!');
        return app('json')->success($this->services->updateForm($id));
    }

    /**
     * 修改订单
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        if (!$id) return app('json')->fail('Missing order ID');
        $data = $this->request->postMore([
            ['order_id', ''],
            ['total_price', 0],
            ['total_postage', 0],
            ['pay_price', 0],
            ['pay_postage', 0],
            ['gain_integral', 0],
        ]);

        validate(StoreOrderValidate::class)->check($data);

        if ($data['total_price'] < 0) return app('json')->fail('Please enter the total price');
        if ($data['pay_price'] < 0) return app('json')->fail('Please enter the actual payment amount');

        $this->services->updateOrder((int)$id, $data);
        return app('json')->success('Modified success');
    }

    /**
     * 获取快递公司
     * @return mixed
     */
    public function express(ExpressServices $services)
    {
        return app('json')->success($services->express(['is_show' => 1]));
    }

    /**
     * 批量删除用户已经删除的订单
     * @return mixed
     */
    public function del_orders()
    {
        [$ids] = $this->request->postMore([
            ['ids', []],
        ], true);
        if (!count($ids)) return app('json')->fail('请选择需要删除的订单');
        if ($this->services->getOrderIdsCount($ids))
            return app('json')->fail('您选择的的订单存在用户未删除的订单');
        if ($this->services->batchUpdate($ids, ['is_system_del' => 1]))
            return app('json')->success('SUCCESS');
        else
            return app('json')->fail('ERROR');
    }

    /**
     * 删除订单
     * @param $id
     * @return mixed
     */
    public function del($id)
    {
        if (!$id || !($orderInfo = $this->services->get($id)))
            return app('json')->fail('订单不存在');
        if (!$orderInfo->is_del)
            return app('json')->fail('订单用户未删除无法删除');
        $orderInfo->is_system_del = 1;
        if ($orderInfo->save())
            return app('json')->success('SUCCESS');
        else
            return app('json')->fail('ERROR');
    }

    /**
     * 订单发送货
     * @param $id 订单id
     * @return mixed
     */
    public function update_delivery($id, StoreOrderDeliveryServices $services)
    {
        $data = $this->request->postMore([
            ['type', 1],
            ['delivery_name', ''],//快递公司名称
            ['delivery_id', ''],//快递单号
            ['delivery_code', ''],//快递公司编码

            ['express_record_type', 2],//发货记录类型
            ['express_temp_id', ""],//电子面单模板
            ['to_name', ''],//寄件人姓名
            ['to_tel', ''],//寄件人电话
            ['to_addr', ''],//寄件人地址

            ['sh_delivery_name', ''],//送货人姓名
            ['sh_delivery_id', ''],//送货人电话
            ['sh_delivery_uid', ''],//送货人ID

            ['fictitious_content', '']//虚拟发货内容
        ]);
        $services->delivery((int)$id, $data);
        return app('json')->success('SUCCESS');
    }


    /**
     * 确认收货
     * @param $id 订单id
     * @return mixed
     * @throws \Exception
     */
    public function take_delivery(StoreOrderTakeServices $services, $id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        $order = $this->services->get($id);
        if (!$order)
            return app('json')->fail('Data does not exist!');
        if ($order['status'] == 2)
            return app('json')->fail('不能重复收货!');
        if ($order['paid'] == 1 && $order['status'] == 1)
            $data['status'] = 2;
        else if ($order['pay_type'] == 'offline')
            $data['status'] = 2;
        else
            return app('json')->fail('请先发货或者送货!');

        if (!$this->services->update($id, $data)) {
            return app('json')->fail('收货失败,请稍候再试!');
        } else {
            $services->storeProductOrderUserTakeDelivery($order);
            return app('json')->success('收货成功');
        }
    }


    /**
     * 获取配置信息
     * @return mixed
     */
    public function getDeliveryInfo()
    {
        return app('json')->success([
            'express_temp_id' => sys_config('config_export_temp_id'),
            'id' => sys_config('config_export_id'),
            'to_name' => sys_config('config_export_to_name'),
            'to_tel' => sys_config('config_export_to_tel'),
            'to_add' => sys_config('config_export_to_address'),
            'export_open' => (int)sys_config('config_export_open') ? true : false
        ]);
    }

    /**
     * 退款表单生成
     * @param $id 订单id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function refund(StoreOrderRefundServices $services, $id)
    {
        if (!$id) {
            return app('json')->fail('Data does not exist!');
        }
        return app('json')->success($services->refundOrderForm((int)$id));
    }

    /**
     * 订单退款
     * @param $id 订单id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update_refund(StoreOrderRefundServices $services, $id)
    {
        $data = $this->request->postMore([
            ['refund_price', 0],
            ['type', 1]
        ]);
        if (!$id) {
            return app('json')->fail('Data does not exist!');
        }
        $order = $this->services->get($id);
        if (!$order) {
            return app('json')->fail('Data does not exist!');
        }
        //0元退款
        if ($order['pay_price'] == 0 && in_array($order['refund_status'], [0, 1])) {
            $refund_price = 0;
        } else {
            if ($order['pay_price'] == $order['refund_price']) {
                return app('json')->fail('已退完支付金额!不能再退款了');
            }
            if (!$data['refund_price']) {
                return app('json')->fail('请输入退款金额');
            }
            $refund_price = $data['refund_price'];
            $data['refund_price'] = bcadd($data['refund_price'], $order['refund_price'], 2);
            $bj = bccomp((string)$order['pay_price'], (string)$data['refund_price'], 2);
            if ($bj < 0) {
                return app('json')->fail('退款金额大于支付金额，请修改退款金额');
            }
        }
        if ($data['type'] == 1) {
            $data['refund_status'] = 2;
        } else if ($data['type'] == 2) {
            $data['refund_status'] = 0;
        }
        $type = $data['type'];
        unset($data['type']);
        $refund_data['pay_price'] = $order['pay_price'];
        $refund_data['refund_price'] = $refund_price;
        if ($order['refund_price'] > 0) {
            $refund_data['refund_id'] = $order['order_id'] . rand(100, 999);
        }
        //退款处理
        $services->payOrderRefund($type, $order, $refund_data);
        //修改订单退款状态
        if ($this->services->update($id, $data)) {
            $services->storeProductOrderRefundY($data, $order, $refund_price);
            return app('json')->success('退款成功');
        } else {
            $services->storeProductOrderRefundYFasle((int)$id, $refund_price);
            return app('json')->fail('退款失败');
        }
    }

    /**
     * 订单详情
     * @param $id 订单id
     * @return mixed
     */
    public function order_info($id)
    {
        if (!$id || !($orderInfo = $this->services->get($id))) {
            return app('json')->fail('订单不存在');
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userInfo = $services->get($orderInfo['uid']);
        if (!$userInfo) return app('json')->fail('用户信息不存在');
        $userInfo = $userInfo->hidden(['pwd', 'add_ip', 'last_ip', 'login_type']);
        $userInfo['spread_name'] = '无';
        if ($userInfo['spread_uid']) {
            $spreadName = $services->value(['uid' => $userInfo['spread_uid']], 'nickname');
            if ($spreadName) {
                $userInfo['spread_name'] = $spreadName;
            } else {
                $userInfo['spread_uid'] = '';
            }
        } else {
            $userInfo['spread_uid'] = '';
        }

        $orderInfo = $this->services->tidyOrder($orderInfo->toArray());
        if ($orderInfo['store_id'] && $orderInfo['shipping_type'] == 2) {
            $orderInfo['_store_name'] = '';
        } else
            $orderInfo['_store_name'] = '';
        $userInfo = $userInfo->toArray();
        return app('json')->success(compact('orderInfo', 'userInfo'));
    }

    /**
     * 查询物流信息
     * @param $id 订单id
     * @return mixed
     */
    public function get_express($id, ExpressServices $services)
    {
        if (!$id || !($orderInfo = $this->services->get($id)))
            return app('json')->fail('订单不存在');
        if ($orderInfo['delivery_type'] != 'express' || !$orderInfo['delivery_id'])
            return app('json')->fail('该订单不存在快递单号');

        $cacheName = $orderInfo['order_id'] . $orderInfo['delivery_id'];

        $data['delivery_name'] = $orderInfo['delivery_name'];
        $data['delivery_id'] = $orderInfo['delivery_id'];
        $data['result'] = $services->query($cacheName, $orderInfo['delivery_id'], $orderInfo['delivery_code'] ?? null);
        return app('json')->success($data);
    }


    /**
     * 获取修改配送信息表单结构
     * @param $id 订单id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function distribution(StoreOrderDeliveryServices $services, $id)
    {
        if (!$id) {
            return app('json')->fail('订单不存在');
        }
        return app('json')->success($services->distributionForm((int)$id));
    }

    /**
     * 修改配送信息
     * @param $id  订单id
     * @return mixed
     */
    public function update_distribution(StoreOrderDeliveryServices $services, $id)
    {
        $data = $this->request->postMore([['delivery_name', ''], ['delivery_id', '']]);
        if (!$id) return app('json')->fail('Data does not exist!');
        $services->updateDistribution($id, $data);
        return app('json')->success('Modified success');
    }

    /**
     * 不退款表单结构
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function no_refund(StoreOrderRefundServices $services, $id)
    {
        if (!$id) return app('json')->fail('Data does not exist!');
        return app('json')->success($services->noRefundForm((int)$id));
    }

    /**
     * 订单不退款
     * @param StoreOrderRefundServices $services
     * @param $id
     * @return mixed
     */
    public function update_un_refund(StoreOrderRefundServices $services, $id)
    {
        if (!$id || !($orderInfo = $this->services->get($id)))
            return app('json')->fail('订单不存在');
        [$refund_reason] = $this->request->postMore([['refund_reason', '']], true);
        if (!$refund_reason) {
            return app('json')->fail('请输入不退款原因');
        }
        $orderInfo->refund_reason = $refund_reason;
        $orderInfo->refund_status = 0;
        $orderInfo->save();
        $services->storeProductOrderRefundNo((int)$id, $refund_reason);
        $services->OrderRefundNoSendTemplate($orderInfo);
        return app('json')->success('Modified success');
    }

    /**
     * 线下支付
     * @param $id 订单id
     * @return mixed
     */
    public function pay_offline(OrderOfflineServices $services, $id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        $res = $services->orderOffline((int)$id);
        if ($res) {
            return app('json')->success('Modified success');
        } else {
            return app('json')->fail('Modification failed');
        }
    }

    /**
     * 退积分表单获取
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function refund_integral(StoreOrderRefundServices $services, $id)
    {
        if (!$id)
            return app('json')->fail('订单不存在');
        return app('json')->success($services->refundIntegralForm((int)$id));
    }

    /**
     * 退积分保存
     * @param $id
     * @return mixed
     */
    public function update_refund_integral(StoreOrderRefundServices $services, $id)
    {
        [$back_integral] = $this->request->postMore([['back_integral', 0]], true);
        if (!$id || !($orderInfo = $this->services->get($id))) {
            return app('json')->fail('订单不存在');
        }
        if ($orderInfo->is_del) {
            return app('json')->fail('订单已删除无法退积分');
        }
        if ($back_integral <= 0) {
            return app('json')->fail('请输入积分');
        }
        if ($orderInfo['use_integral'] == $orderInfo['back_integral']) {
            return app('json')->fail('已退完积分!不能再积分了');
        }

        $data['back_integral'] = bcadd((string)$back_integral, (string)$orderInfo['back_integral'], 2);
        $bj = bccomp((string)$orderInfo['use_integral'], (string)$data['back_integral'], 2);
        if ($bj < 0) {
            return app('json')->fail('退积分大于支付积分，请修改退积分');
        }
        //积分退款处理
        $orderInfo->back_integral = $data['back_integral'];
        if ($services->refundIntegral($orderInfo, $back_integral)) {
            return app('json')->success('退积分成功');
        } else {
            return app('json')->fail('退积分失败');
        }
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
     * 获取订单状态列表并分页
     * @param $id
     * @return mixed
     */
    public function status(StoreOrderStatusServices $services, $id)
    {
        if (!$id) return app('json')->fail('缺少参数');
        return app('json')->success($services->getStatusList(['oid' => $id])['list']);
    }


}
