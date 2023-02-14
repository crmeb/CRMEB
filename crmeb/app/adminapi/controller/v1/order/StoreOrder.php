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
namespace app\adminapi\controller\v1\order;

use app\adminapi\controller\AuthController;
use app\adminapi\validate\order\StoreOrderValidate;
use app\services\serve\ServeServices;
use app\services\order\{StoreOrderCartInfoServices,
    StoreOrderDeliveryServices,
    StoreOrderRefundServices,
    StoreOrderStatusServices,
    StoreOrderTakeServices,
    StoreOrderWriteOffServices,
    StoreOrderServices
};
use app\services\pay\OrderOfflineServices;
use app\services\shipping\ExpressServices;
use app\services\system\store\SystemStoreServices;
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
     * 订单列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
        $where['is_system_del'] = 0;
        $where['pid'] = 0;
        return app('json')->success($this->services->getOrderList($where, ['*'], ['split' => function ($query) {
            $query->field('id,pid');
        }, 'pink', 'invoice', 'division']));
    }

    /**
     * 核销码核销
     * @param StoreOrderWriteOffServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function write_order(StoreOrderWriteOffServices $services)
    {
        [$code, $confirm] = $this->request->getMore([
            ['code', ''],
            ['confirm', 0]
        ], true);
        if (!$code) return app('json')->fail(100100);
        $orderInfo = $services->writeOffOrder($code, (int)$confirm);
        if ($confirm == 0) {
            return app('json')->success(400151, $orderInfo);
        }
        return app('json')->success(400152);
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
            return app('json')->fail(400153);
        } else {
            if (!$orderInfo->verify_code) {
                return app('json')->fail(100100);
            }
            $orderInfo = $services->writeOffOrder($orderInfo->verify_code, 1);
            if ($orderInfo) {
                return app('json')->success(400151);
            } else {
                return app('json')->fail(400154);
            }
        }
    }

    /**
     * 订单改价表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit($id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->services->updateForm($id));
    }

    /**
     * 订单改价
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function update($id)
    {
        if (!$id) return app('json')->fail(100100);
        $data = $this->request->postMore([
            ['order_id', ''],
            ['total_price', 0],
            ['total_postage', 0],
            ['pay_price', 0],
            ['pay_postage', 0],
            ['gain_integral', 0],
        ]);

        $this->validate($data, StoreOrderValidate::class);

        if ($data['total_price'] < 0) return app('json')->fail(400155);
        if ($data['pay_price'] < 0) return app('json')->fail(400155);

        $this->services->updateOrder((int)$id, $data);
        return app('json')->success(100001);
    }

    /**
     * 获取快递公司
     * @return mixed
     */
    public function express(ExpressServices $services)
    {
        [$status] = $this->request->getMore([
            ['status', ''],
        ], true);
        if ($status != '') $data['status'] = $status;
        $data['is_show'] = 1;
        return app('json')->success($services->express($data));
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
        if (!count($ids)) return app('json')->fail(400156);
        if ($this->services->getOrderIdsCount($ids))
            return app('json')->fail(400157);
        if ($this->services->batchUpdate($ids, ['is_system_del' => 1]))
            return app('json')->success(100002);
        else
            return app('json')->fail(100008);
    }

    /**
     * 删除订单
     * @param $id
     * @return mixed
     */
    public function del($id)
    {
        if (!$id || !($orderInfo = $this->services->get($id)))
            return app('json')->fail(400118);
        if (!$orderInfo->is_del)
            return app('json')->fail(400157);
        $orderInfo->is_system_del = 1;
        if ($orderInfo->save()) {
            /** @var StoreOrderRefundServices $refundServices */
            $refundServices = app()->make(StoreOrderRefundServices::class);
            $refundServices->update(['store_order_id' => $id], ['is_system_del' => 1]);
            return app('json')->success(100002);
        } else
            return app('json')->fail(100008);
    }

    /**
     * 订单发送货
     * @param $id
     * @param StoreOrderDeliveryServices $services
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
        return app('json')->success(100010);
    }

    /**
     * 订单拆单发送货
     * @param $id
     * @param StoreOrderDeliveryServices $services
     * @return mixed
     */
    public function split_delivery($id, StoreOrderDeliveryServices $services)
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

            ['fictitious_content', ''],//虚拟发货内容

            ['cart_ids', []]
        ]);
        if (!$id) {
            return app('json')->fail(100100);
        }
        if (!$data['cart_ids']) {
            return app('json')->fail(400158);
        }
        foreach ($data['cart_ids'] as $cart) {
            if (!isset($cart['cart_id']) || !$cart['cart_id'] || !isset($cart['cart_num']) || !$cart['cart_num']) {
                return app('json')->fail(400159);
            }
        }
        $services->splitDelivery((int)$id, $data);
        return app('json')->success(100010);
    }

    /**
     * 获取订单可拆分发货商品列表
     * @param $id
     * @param StoreOrderCartInfoServices $services
     * @return mixed
     */
    public function split_cart_info($id, StoreOrderCartInfoServices $services)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        return app('json')->success($services->getSplitCartList((int)$id));
    }

    /**
     * 获取订单拆分子订单列表
     * @return mixed
     */
    public function split_order($id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        return app('json')->success($this->services->getSplitOrderList(['pid' => $id, 'is_system_del' => 0], ['*'], ['split', 'pink', 'invoice']));
    }


    /**
     * 确认收货
     * @param $id 订单id
     * @return mixed
     * @throws \Exception
     */
    public function take_delivery(StoreOrderTakeServices $services, $id)
    {
        if (!$id) return app('json')->fail(100100);
        $order = $this->services->get($id);
        if (!$order)
            return app('json')->fail(400118);
        if ($order['status'] == 2)
            return app('json')->fail(400114);
        if ($order['paid'] == 1 && $order['status'] == 1)
            $data['status'] = 2;
        else if ($order['pay_type'] == 'offline')
            $data['status'] = 2;
        else
            return app('json')->fail(400115);

        if (!$this->services->update($id, $data)) {
            return app('json')->fail(400116);
        } else {
            $services->storeProductOrderUserTakeDelivery($order);
            return app('json')->success(400117);
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
            'export_open' => (bool)((int)sys_config('config_export_open'))
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
            return app('json')->fail(100100);
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
            return app('json')->fail(100100);
        }
        $order = $this->services->get($id);
        if (!$order) {
            return app('json')->fail(400118);
        }
        //0元退款
        if ($order['pay_price'] == 0 && in_array($order['refund_status'], [0, 1])) {
            $refund_price = 0;
        } else {
            if ($order['pay_price'] == $order['refund_price']) {
                return app('json')->fail(400147);
            }
            if (!$data['refund_price']) {
                return app('json')->fail(400146);
            }
            $refund_price = $data['refund_price'];
            $data['refund_price'] = bcadd($data['refund_price'], $order['refund_price'], 2);
            $bj = bccomp((string)$order['pay_price'], (string)$data['refund_price'], 2);
            if ($bj < 0) {
                return app('json')->fail(400148);
            }
        }
        if ($data['type'] == 1) {
            $data['refund_status'] = 2;
        } else if ($data['type'] == 2) {
            $data['refund_status'] = 0;
        }
        $data['refund_type'] = 6;
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
            return app('json')->success(400149);
        } else {
            $services->storeProductOrderRefundYFasle((int)$id, $refund_price);
            return app('json')->fail(400150);
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
            return app('json')->fail(400118);
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userInfo = $services->get($orderInfo['uid']);
        if (!$userInfo) return app('json')->fail(400119);
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

        $orderInfo = $this->services->tidyOrder($orderInfo->toArray(), true, true);
        //核算优惠金额
        $vipTruePrice = $levelPrice = $memberPrice = 0;
        foreach ($orderInfo['cartInfo'] as $cart) {
            $vipTruePrice = bcadd((string)$vipTruePrice, (string)$cart['vip_sum_truePrice'], 2);
            if ($cart['price_type'] == 'member') $memberPrice = bcadd((string)$memberPrice, (string)$cart['vip_sum_truePrice'], 2);
            if ($cart['price_type'] == 'level') $levelPrice = bcadd((string)$levelPrice, (string)$cart['vip_sum_truePrice'], 2);
        }
        $orderInfo['vip_true_price'] = $vipTruePrice;
        $orderInfo['levelPrice'] = $levelPrice;
        $orderInfo['memberPrice'] = $memberPrice;
        $orderInfo['total_price'] = bcadd($orderInfo['total_price'], $orderInfo['vip_true_price'], 2);
        if ($orderInfo['store_id'] && $orderInfo['shipping_type'] == 2) {
            /** @var  $storeServices */
            $storeServices = app()->make(SystemStoreServices::class);
            $orderInfo['_store_name'] = $storeServices->value(['id' => $orderInfo['store_id']], 'name');
        } else
            $orderInfo['_store_name'] = '';
        $orderInfo['spread_name'] = $services->value(['uid' => $orderInfo['spread_uid']], 'nickname') ?? '无';
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
            return app('json')->fail(400118);
        if ($orderInfo['delivery_type'] != 'express' || !$orderInfo['delivery_id'])
            return app('json')->fail(400120);

        $cacheName = $orderInfo['order_id'] . $orderInfo['delivery_id'];

        $data['delivery_name'] = $orderInfo['delivery_name'];
        $data['delivery_id'] = $orderInfo['delivery_id'];
        $data['result'] = $services->query($cacheName, $orderInfo['delivery_id'], $orderInfo['delivery_code'] ?? null, $orderInfo['user_phone']);
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
            return app('json')->fail(100100);
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
        $data = $this->request->postMore([['delivery_name', ''], ['delivery_code', ''], ['delivery_id', '']]);
        if (!$id) return app('json')->fail(100100);
        $services->updateDistribution($id, $data);
        return app('json')->success(100010);
    }

    /**
     * 不退款表单结构
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function no_refund(StoreOrderRefundServices $services, $id)
    {
        if (!$id) return app('json')->fail(100100);
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
            return app('json')->fail(400118);
        [$refund_reason] = $this->request->postMore([['refund_reason', '']], true);
        if (!$refund_reason) {
            return app('json')->fail(400113);
        }
        $orderInfo->refund_reason = $refund_reason;
        $orderInfo->refund_status = 0;
        $orderInfo->refund_type = 3;
        $orderInfo->save();
        if ($orderInfo->pid > 0) {
            $res1 = $this->services->getCount([
                ['pid', '=', $orderInfo->pid],
                ['refund_type', '>', 0],
                ['refund_type', '<>', 3],
            ]);
            if ($res1 == 0) {
                $this->services->update($orderInfo->pid, ['refund_status' => 0]);
            }
        }
        $services->storeProductOrderRefundNo((int)$id, $refund_reason);
        //提醒推送
        event('notice.notice', [['orderInfo' => $orderInfo], 'send_order_refund_no_status']);
        return app('json')->success(100010);
    }

    /**
     * 线下支付
     * @param $id 订单id
     * @return mixed
     */
    public function pay_offline(OrderOfflineServices $services, $id)
    {
        if (!$id) return app('json')->fail(100100);
        $res = $services->orderOffline((int)$id);
        if ($res) {
            return app('json')->success(100010);
        } else {
            return app('json')->fail(100005);
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
            return app('json')->fail(100100);
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
            return app('json')->fail(400118);
        }
        if ($orderInfo->is_del) {
            return app('json')->fail(400160);
        }
        if ($back_integral <= 0) {
            return app('json')->fail(400161);
        }
        if ($orderInfo['use_integral'] == $orderInfo['back_integral']) {
            return app('json')->fail(400162);
        }

        $data['back_integral'] = bcadd((string)$back_integral, (string)$orderInfo['back_integral'], 2);
        $bj = bccomp((string)$orderInfo['use_integral'], (string)$data['back_integral'], 2);
        if ($bj < 0) {
            return app('json')->fail(400163);
        }
        //积分退款处理
        $orderInfo->back_integral = $data['back_integral'];
        if ($services->refundIntegral($orderInfo, $back_integral)) {
            return app('json')->success(400164);
        } else {
            return app('json')->fail(400165);
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
            return app('json')->fail(400106);
        if (!$id)
            return app('json')->fail(100100);

        if (!$order = $this->services->get($id)) {
            return app('json')->fail(400118);
        }
        $order->remark = $data['remark'];
        if ($order->save()) {
            return app('json')->success(100024);
        } else
            return app('json')->fail(100025);
    }

    /**
     * 获取订单状态列表并分页
     * @param $id
     * @return mixed
     */
    public function status(StoreOrderStatusServices $services, $id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($services->getStatusList(['oid' => $id])['list']);
    }

    /**
     * 小票打印机打印
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function order_print($id)
    {
        if (!$id) return app('json')->fail(100100);
        $res = $this->services->orderPrintTicket($id, true);
        if ($res) {
            return app('json')->success(100010);
        } else {
            return app('json')->fail(100005);
        }
    }

    /**
     * 电子面单模板
     * @param $com
     * @return mixed
     */
    public function expr_temp(ServeServices $services, $com)
    {
        if (!$com) {
            return app('json')->fail(400123);
        }
        $list = $services->express()->temp($com);
        return app('json')->success($list);
    }

    /**
     * 获取模板
     */
    public function express_temp(ServeServices $services)
    {
        $data = $this->request->getMore([['com', '']]);
        if (!$data['com']) {
            return app('json')->fail(400123);
        }
        $tpd = $services->express()->temp($data['com']);
        return app('json')->success($tpd['data']);
    }

    /**
     * 订单发货后打印电子面单
     * @param $orderId
     * @param StoreOrderDeliveryServices $storeOrderDeliveryServices
     * @return mixed
     */
    public function order_dump($order_id, StoreOrderDeliveryServices $storeOrderDeliveryServices)
    {
        return app('json')->success($storeOrderDeliveryServices->orderDump($order_id));

    }

}
