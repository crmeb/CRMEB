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

namespace app\kefuapi\controller;


use app\Request;
use app\services\order\StoreOrderWriteOffServices;
use think\facade\App;
use app\services\order\DeliveryServiceServices;
use app\services\product\product\StoreProductServices;
use app\services\serve\ServeServices;
use app\services\shipping\ExpressServices;
use app\services\user\UserServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderDeliveryServices;
use app\services\system\store\SystemStoreServices;
use app\adminapi\validate\order\StoreOrderValidate;
use app\services\message\service\StoreServiceRecordServices;

/**
 * Class Order
 * @package app\kefuapi\controller
 */
class Order extends AuthController
{

    /**
     * Order constructor.
     * @param App $app
     * @param StoreOrderServices $services
     */
    public function __construct(App $app, StoreOrderServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取订单列表
     * @param Request $request
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserOrderList(Request $request, StoreServiceRecordServices $services, $uid)
    {
        $where = $request->getMore([
            ['type', '', '', 'status'],
            ['search', '', '', 'real_name'],
        ]);
        $where['uid'] = $uid;
        $where['is_del'] = 0;
        $where['is_system_del'] = 0;
        if ($where['status'] == -1) $where['refund_type'] = [1, 3, 6];
        if (!$services->count(['to_uid' => $uid])) {
            return app('json')->fail('用户uid不再当前聊天用户范围内');
        }
        return app('json')->success($this->services->getOrderApiList($where));
    }

    /**
     * 订单发货
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delivery_keep(StoreOrderDeliveryServices $services, $id)
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
        return app('json')->success('发货成功!');
    }

    /**
     * 修改支付金额等
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($id)
    {
        if (!$id) {
            return app('json')->fail('Data does not exist!');
        }
        return app('json')->success($this->services->updateForm($id));
    }

    /**
     * 修改订单
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        if (!$id) {
            return app('json')->fail('Missing order ID');
        }
        $data = $this->request->postMore([
            ['order_id', ''],
            ['total_price', 0],
            ['total_postage', 0],
            ['pay_price', 0],
            ['pay_postage', 0],
            ['gain_integral', 0],
        ]);

        validate(StoreOrderValidate::class)->check($data);

        if ($data['total_price'] < 0) {
            return app('json')->fail('Please enter the total price');
        }
        if ($data['pay_price'] < 0) {
            return app('json')->fail('Please enter the actual payment amount');
        }

        $this->services->updateOrder((int)$id, $data);
        return app('json')->success('Modified success');
    }

    /**
     * 订单备注
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function remark(Request $request)
    {
        [$order_id, $remark] = $request->postMore([
            ['order_id', ''],
            ['remark', '']
        ], true);
        $order = $this->services->getOne(['order_id' => $order_id], 'id,remark');
        if (!$order) {
            return app('json')->fail('订单不存在');
        }
        if (!strlen(trim($remark))) {
            return app('json')->fail('请填写备注内容');
        }
        $order->remark = $remark;
        if (!$order->save()) {
            return app('json')->fail('备注失败');
        }
        return app('json')->success('备注成功');

    }

    /**
     * 退款表单生成
     * @param $id 订单id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function refundForm(StoreOrderRefundServices $services, $id)
    {
        if (!$id) {
            return app('json')->fail('Data does not exist!');
        }
        return app('json')->success($services->refundOrderForm((int)$id));
    }

    /**
     * 订单退款
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refund(Request $request, StoreOrderRefundServices $services)
    {
        [$orderId, $price, $type] = $request->postMore([
            ['order_id', ''],
            ['price', '0'],
            ['type', 1],
        ], true);
        if (!strlen(trim($orderId))) return app('json')->fail('参数错误');
        $orderInfo = $this->services->getOne(['order_id' => $orderId]);
        if (!$orderInfo) return app('json')->fail('数据不存在!');
        //仅退款类型
        if ($orderInfo['refund_type'] != 1) {
            return app('json')->fail('请去后台售后订单列表处理');
        }
        if ($type == 1) {
            $data['refund_status'] = 2;
            $data['refund_type'] = 6;
        } else if ($type == 2) {
            $data['refund_status'] = 0;
            $data['refund_type'] = 3;
        } else {
            return app('json')->fail('退款修改状态错误');
        }
        if ($orderInfo['pay_price'] == 0 || $type == 2) {
            $orderInfo->refund_status = $data['refund_status'];
            $orderInfo->save();
            return app('json')->success('修改退款状态成功!');
        }
        if ($orderInfo['pay_price'] == $orderInfo['refund_price']) return app('json')->fail('已退完支付金额!不能再退款了');
        if (!$price) {
            return app('json')->fail('请输入退款金额');
        }
        $data['refund_price'] = bcadd($price, $orderInfo['refund_price'], 2);
        $bj = bccomp((float)$orderInfo['pay_price'], (float)$data['refund_price'], 2);
        if ($bj < 0) {
            return app('json')->fail('退款金额大于支付金额，请修改退款金额');
        }
        $refundData['pay_price'] = $orderInfo['pay_price'];
        $refundData['refund_price'] = $price;
        if ($orderInfo['refund_price'] > 0) {
            $refundData['refund_id'] = $orderInfo['order_id'] . rand(100, 999);
        }
        //退款处理
        $services->payOrderRefund($type, $orderInfo, $refundData);
        //修改订单退款状态
        if ($this->services->update((int)$orderInfo['id'], $data)) {
            $services->storeProductOrderRefundY($data, $orderInfo, $price);
            return app('json')->success('退款成功');
        } else {
            $services->storeProductOrderRefundYFasle((int)$orderInfo['id'], $price);
            return app('json')->fail('退款失败');
        }
    }

    /**
     * 订单详情
     * @param $id 订单id
     * @return mixed
     */
    public function orderInfo(StoreProductServices $productServices, $id)
    {
        if (!$id || !($orderInfo = $this->services->get($id))) {
            return app('json')->fail('订单不存在');
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userInfo = $services->get($orderInfo['uid']);
        if (!$userInfo) {
            return app('json')->fail('用户信息不存在');
        }
        $userInfo = $userInfo->hidden(['pwd', 'add_ip', 'last_ip', 'login_type']);
        $userInfo['spread_name'] = '';
        if ($userInfo['spread_uid'])
            $userInfo['spread_name'] = $services->value(['uid' => $userInfo['spread_uid']], 'nickname');
        $orderInfo = $this->services->tidyOrder($orderInfo->toArray(), true);
        $productId = array_column($orderInfo['cartInfo'], 'product_id');
        $cateData = $productServices->productIdByProductCateName($productId);
        foreach ($orderInfo['cartInfo'] as &$item) {
            $item['class_name'] = $cateData[$item['product_id']] ?? '';
        }
        if ($orderInfo['store_id'] && $orderInfo['shipping_type'] == 2) {
            /** @var  $storeServices */
            $storeServices = app()->make(SystemStoreServices::class);
            $orderInfo['_store_name'] = $storeServices->value(['id' => $orderInfo['store_id']], 'name');
        } else {
            $orderInfo['_store_name'] = '';
        }
        $userInfo = $userInfo->toArray();
        return app('json')->success(compact('orderInfo', 'userInfo'));
    }

    /**
     * 获取物流
     * @param ExpressServices $services
     * @return mixed
     */
    public function export(ExpressServices $services)
    {
        return app('json')->success($services->express());
    }

    /**
     *
     * 获取面单信息
     * @param string $com
     * @return mixed
     */
    public function getExportTemp(ServeServices $services)
    {
        [$com] = $this->request->getMore([
            ['com', ''],
        ], true);
        return app('json')->success($services->express()->temp($com));
    }

    /**
     * 获取所有配送员列表
     * @param DeliveryServiceServices $services
     * @return mixed
     */
    public function getDeliveryAll(DeliveryServiceServices $services)
    {
        $list = $services->getDeliveryList();
        return app('json')->success($list['list']);
    }

    /**
     * 获取配置信息
     * @return mixed
     */
    public function getDeliveryInfo()
    {
        return app('json')->success([
            'express_temp_id' => sys_config('config_export_temp_id'),
            'to_name' => sys_config('config_export_to_name'),
            'id' => sys_config('config_export_id'),
            'to_tel' => sys_config('config_export_to_tel'),
            'to_add' => sys_config('config_export_to_address')
        ]);
    }

    /**
     * 门店核销
     * @param Request $request
     */
    public function order_verific(StoreOrderWriteOffServices $services, $id)
    {

        $orderInfo = $this->services->get(['id' => $id], ['verify_code', 'uid']);
        if (!$orderInfo) {
            return app('json')->fail('核销订单未查到');
        }
        if (!$orderInfo->verify_code) {
            return app('json')->fail('Lack of write-off code');
        }
        $services->writeOffOrder($orderInfo->verify_code, 1, $orderInfo->uid);
        return app('json')->success('Write off successfully');
    }

}
