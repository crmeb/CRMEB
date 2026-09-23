<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\api\controller\v1\order;


use app\services\order\OrderInvoiceServices;
use app\services\order\OrderCenterServices;
use crmeb\services\invoice\Invoice;
use app\services\driver\pay\PayChannelServices;
use think\Request;

/**
 * Class StoreOrderInvoiceController
 * @package app\api\controller\v1\order
 */
class StoreOrderInvoiceController
{
    /**
     * @var OrderInvoiceServices
     */
    protected $services;

    /**
     * StoreOrderInvoiceController constructor.
     * @param OrderInvoiceServices $services
     */
    public function __construct(OrderInvoiceServices $services)
    {
        $this->services = $services;
    }

    /**
     * 订单开票
     * @param Request $request
     * @return mixed
     */
    public function makeUp(Request $request)
    {
        [$order_id, $invoice_id] = $request->postMore([
            ['order_id', 0],
            ['invoice_id', 0]
        ], true);
        $uid = (int)$request->uid();
        return app('json')->success($this->services->makeUp($uid, $order_id, (int)$invoice_id));
    }

    /**
     * 开票记录
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getOrderInvoiceList(['uid' => $uid]));
    }

    /**
     * 订单开票详情
     * @param OrderCenterServices $services
     * @param \app\Request $request
     * @param $uni
     * @return mixed
     */
    public function detail(OrderCenterServices $services, \app\Request $request, $uni)
    {
        $uni = trim((string)$uni);
        if (!strlen($uni)) return app('json')->fail('参数错误');
        $uid = (int)$request->uid();
        try {
            // 订单中台统一详情（含状态、支付、配送、发票等信息）
            $order = $services->getDetailByOrderNo($uni);
        } catch (\Throwable) {
            return app('json')->fail('订单不存在');
        }
        // 越权防护：仅能查看本人订单的开票详情
        if ((int)($order['uid'] ?? 0) !== $uid) {
            return app('json')->fail('订单不存在');
        }
        // 本人开票记录
        $orderInvoice = $this->services->getOne(['order_id' => (int)$order['id'], 'uid' => $uid]);
        $order['invoice'] = $orderInvoice ? $orderInvoice->toArray() : [];
        // 以下映射旧版发票详情页所需的订单字段（旧 eb_store_order 结构已由订单中台替代）
        $order['order_id'] = $order['order_no'] ?? $uni;
        $order['add_time_y'] = date('Y-m-d', (int)($order['add_time'] ?? time()));
        $order['add_time_h'] = date('H:i:s', (int)($order['add_time'] ?? time()));
        $order['paid'] = (int)($order['pay_status'] ?? 0) == 1;
        $order['_status'] = [
            '_type' => $this->services->statusToLegacy($order['status_code'] ?? ''),
            '_payType' => $order['pay_type_name'] ?? ($order['pay_type'] ?? ''),
        ];
        // 收货人信息优先取订单配送快照
        $contact = is_array($order['contact'] ?? null) ? $order['contact'] : [];
        $order['real_name'] = $contact['name'] ?? '';
        $order['user_phone'] = $contact['phone'] ?? '';
        // 物流信息
        $logistics = $order['extensions']['logistics'] ?? [];
        $order['delivery_type'] = ($order['delivery_type'] ?? '') ?: 'express';
        $order['delivery_name'] = $logistics['delivery_name'] ?? '';
        $order['delivery_id'] = $logistics['delivery_id'] ?? '';
        // 商品快照合成旧版 cartInfo，供订单商品组件展示
        $order['cartInfo'] = $this->services->buildLegacyCartInfo(is_array($order['extra_info'] ?? null) ? $order['extra_info'] : []);
        $order['mapKey'] = sys_config('tengxun_map_key');
        /** @var PayChannelServices $payChannelServices */
        $payChannelServices = app()->make(PayChannelServices::class);
        $order['yue_pay_status'] = $payChannelServices->getChannelStatus('yue') ? (int)1 : (int)2;//余额支付渠道 1 开启 2 关闭
        $order['pay_weixin_open'] = $payChannelServices->getChannelStatus('weixin') ? 1 : 0;//微信支付 1 开启 0 关闭
        $order['ali_pay_status'] = $payChannelServices->getChannelStatus('alipay');//支付宝支付 1 开启 0 关闭
        return app('json')->success($order);
    }

    /**
     * 前端下载电子发票
     * @param \app\Request $request
     * @param $id
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/14
     */
    public function downInvoice(\app\Request $request, $id)
    {
        // 越权防护：仅能下载本人订单的发票
        $info = $this->services->getOne(['id' => (int)$id, 'uid' => (int)$request->uid()]);
        if (!$info) return app('json')->fail('发票不存在');
        // 发票未开具时无需请求一号通
        if (empty($info['invoice_num'])) return app('json')->fail('发票尚未开具');
        $invoice = app()->make(Invoice::class, [yihaotong_config()]);
        return app('json')->success($invoice->downloadInvoice($info['invoice_num']));
    }
}
