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
use app\Request;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use think\facade\App;

/**
 * 退款订单
 * Class RefundOrder
 * @package app\adminapi\controller\v1\order
 */
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
     */
    public function getRefundList()
    {
        $where = $this->request->getMore([
            ['order_id', ''],
            ['time', ''],
            ['refund_type', 0]
        ]);
        $where['is_cancel'] = 0;
        $where['is_system_del'] = 0;
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
        return app('json')->success(100010);
    }

    /**
     * 修改备注
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remark($id)
    {
        [$remark] = $this->request->postMore([['remark', '']], true);

        $this->services->updateRemark((int)$id, $remark);
        return app('json')->success(100024);
    }

    /**
     * 退款表单生成
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function refund($id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->services->refundOrderForm((int)$id));
    }

    /**
     * 订单退款(产品)
     * @param Request $request
     * @param StoreOrderServices $services
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundPrice(Request $request, StoreOrderServices $services, $id)
    {
        $data = $request->postMore([
            ['refund_price', 0],
            ['type', 1]
        ]);
        if (!$id) {
            return app('json')->fail(100100);
        }
        $orderRefund = $this->services->get($id);
        if (!$orderRefund) {
            return app('json')->fail(100026);
        }
        if ($orderRefund['is_cancel'] == 1) {
            return app('json')->fail(400118);
        }
        $order = $services->get((int)$orderRefund['store_order_id']);
        if (!$order) {
            return app('json')->fail(100026);
        }
        if (!in_array($orderRefund['refund_type'], [1, 5])) {
            return app('json')->fail(400144);
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
            return app('json')->success(400145);
        } else {
            //0元退款
            if ($orderRefund['refund_price'] == 0 && in_array($orderRefund['refund_type'], [1, 5])) {
                $refund_price = 0;
            } else {
                if (!$data['refund_price']) {
                    return app('json')->fail(400146);
                }
                if ($orderRefund['refund_price'] == $orderRefund['refunded_price']) {
                    return app('json')->fail(400147);
                }
                $refund_price = $data['refund_price'];

                $data['refunded_price'] = bcadd($data['refund_price'], $orderRefund['refunded_price'], 2);
                $bj = bccomp((string)$orderRefund['refund_price'], (string)$data['refunded_price'], 2);
                if ($bj < 0) {
                    return app('json')->fail(400148);
                }
            }

            unset($data['type']);
            $refund_data['pay_price'] = $order['pay_price'];
            $refund_data['refund_price'] = $refund_price;
            if ($order['refund_price'] > 0) {
                mt_srand();
                $refund_data['refund_id'] = $order['order_id'] . rand(100, 999);
            }
            ($order['pid'] > 0) ? $refund_data['order_id'] = $services->value(['id' => (int)$order['pid']], 'order_id') : $refund_data['order_id'] = $order['order_id'];
            /** @var WechatUserServices $wechatUserServices */
            $wechatUserServices = app()->make(WechatUserServices::class);
            $refund_data['open_id'] = $wechatUserServices->uidToOpenid((int)$order['uid'], 'routine') ?? '';
            $refund_data['refund_no'] = $orderRefund['order_id'];
            //修改订单退款状态
            unset($data['refund_price']);
            if ($this->services->agreeRefund($id, $refund_data)) {
                $this->services->update($id, $data);
                return app('json')->success(400149);
            } else {
                $this->services->storeProductOrderRefundYFasle((int)$id, $refund_price);
                return app('json')->fail(400150);
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
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->services->noRefundForm((int)$id));
    }

    /**
     * 订单不退款
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refuseRefund($id)
    {
        [$refund_reason] = $this->request->postMore([['refund_reason', '']], true);
        $this->services->refuse($id, $refund_reason);
        return app('json')->success(100010);
    }
}
