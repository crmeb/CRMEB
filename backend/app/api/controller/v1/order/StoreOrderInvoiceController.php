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
            $order = $services->getDetailByOrderNo($uni, false);
        } catch (\Throwable) {
            return app('json')->fail('订单不存在');
        }
        // 越权防护：仅能查看本人订单的开票详情
        if ((int)($order['uid'] ?? 0) !== $uid) {
            return app('json')->fail('订单不存在');
        }
        // 本人开票记录（申请表为真源）；无记录时回退下单快照
        $orderInvoice = $this->services->getOne(['order_id' => (int)$order['id'], 'uid' => $uid]);
        if ($orderInvoice) {
            $invoice = $orderInvoice->toArray();
        } else {
            $invoice = is_array($order['invoice'] ?? null) ? $order['invoice'] : [];
        }
        // 兼容旧页字段：备注列名、时间展示
        if ($invoice) {
            if (!isset($invoice['remark']) && isset($invoice['invoice_reamrk'])) {
                $invoice['remark'] = $invoice['invoice_reamrk'];
            }
            if (isset($invoice['add_time']) && is_numeric($invoice['add_time'])) {
                $invoice['add_time'] = date('Y-m-d H:i:s', (int)$invoice['add_time']);
            }
            if (isset($invoice['invoice_time']) && is_numeric($invoice['invoice_time']) && (int)$invoice['invoice_time'] > 0) {
                $invoice['invoice_time'] = date('Y-m-d H:i:s', (int)$invoice['invoice_time']);
            }
        }
        $order['invoice'] = $invoice ?: [];

        // 以下映射旧版发票详情页所需的订单字段（旧 eb_store_order 结构已由订单中台替代）
        $order['order_id'] = $order['order_no'] ?? $uni;
        $addTime = (int)($order['add_time'] ?? time());
        $order['add_time_y'] = date('Y-m-d', $addTime);
        $order['add_time_h'] = date('H:i:s', $addTime);
        $payStatus = (int)($order['pay_status'] ?? 0);
        $order['paid'] = in_array($payStatus, [1, 2], true) ? 1 : 0;
        $legacyStatus = $this->services->statusToLegacy((string)($order['status_code'] ?? ''));
        $order['status'] = $legacyStatus;
        $order['_status'] = [
            '_type' => $legacyStatus,
            '_payType' => (string)($order['pay_type_name'] ?? ($order['pay_type'] ?? '')),
        ];

        // 收货人：顶层 contact → extra_info.contact → delivery.contact
        $extra = is_array($order['extra_info'] ?? null) ? $order['extra_info'] : [];
        $contact = [];
        if (is_array($order['contact'] ?? null) && $order['contact']) {
            $contact = $order['contact'];
        } elseif (is_array($extra['contact'] ?? null)) {
            $contact = $extra['contact'];
        } elseif (is_array($order['delivery']['contact'] ?? null)) {
            $contact = $order['delivery']['contact'];
        }
        $order['real_name'] = trim((string)($contact['name'] ?? ($order['real_name'] ?? '')));
        if ($order['real_name'] === '') {
            $order['real_name'] = trim((string)($order['nickname'] ?? ''));
        }
        $order['user_phone'] = trim((string)($contact['phone'] ?? ($order['user_phone'] ?? '')));
        // 收货地址
        if (empty($order['user_address'])) {
            $addr = $contact['address'] ?? ($order['delivery']['user_address'] ?? '');
            if (is_array($addr)) {
                $order['user_address'] = implode('', array_filter([
                    $addr['province'] ?? '',
                    $addr['city'] ?? '',
                    $addr['district'] ?? '',
                    $addr['detail'] ?? '',
                ]));
            } else {
                $order['user_address'] = trim((string)$addr);
            }
        }

        // 物流：顶层 → extensions → extra_info.delivery
        $logistics = is_array($order['extensions']['logistics'] ?? null) ? $order['extensions']['logistics'] : [];
        $deliveryExtra = is_array($extra['delivery'] ?? null) ? $extra['delivery'] : [];
        if (empty($order['delivery_name'])) {
            $order['delivery_name'] = (string)($logistics['delivery_name'] ?? ($deliveryExtra['delivery_name'] ?? ''));
        }
        if (empty($order['delivery_id'])) {
            $order['delivery_id'] = (string)($logistics['delivery_id'] ?? ($deliveryExtra['delivery_id'] ?? ''));
        }
        if (empty($order['delivery_type'])) {
            $shippingType = (int)($order['shipping_type'] ?? 0);
            $order['delivery_type'] = (string)($logistics['delivery_type']
                ?? ($deliveryExtra['delivery_type'] ?? ($extra['delivery_type'] ?? '')));
            if ($order['delivery_type'] === '') {
                $order['delivery_type'] = $shippingType === 2 ? 'pickup' : ($shippingType === 3 ? 'door' : 'express');
            }
        }

        // 商品快照：extra_info.items 优先，缺省回退 order_items
        if (empty($extra['items']) && !empty($order['order_items']) && is_array($order['order_items'])) {
            $extra['items'] = $order['order_items'];
        }
        $order['cartInfo'] = $this->services->buildLegacyCartInfo($extra);
        $order['mark'] = (string)($order['mark'] ?? ($extra['mark'] ?? ''));
        $order['use_integral'] = (int)($order['use_integral'] ?? ($order['use_integral_num'] ?? 0));
        $order['system_store'] = is_array($order['store'] ?? null)
            ? $order['store']
            : (is_array($order['system_store'] ?? null) ? $order['system_store'] : []);
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
