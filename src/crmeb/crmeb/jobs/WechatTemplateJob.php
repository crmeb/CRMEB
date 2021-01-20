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

namespace crmeb\jobs;


use crmeb\basic\BaseJob;
use crmeb\services\template\Template;
use think\facade\Route;

/**
 * Class WechatTemplateJob
 * @package crmeb\jobs
 */
class WechatTemplateJob extends BaseJob
{
    /**
     * 支付成功发送模板消息
     * @param $order
     * @return bool
     */
    public function sendOrderPaySuccess($openid, $order)
    {
        return $this->sendTemplate('ORDER_PAY_SUCCESS', $openid, [
            'first' => '亲，您购买的商品已支付成功',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['pay_price'],
            'remark' => '点击查看订单详情'
        ], sys_config('site_url') . Route::buildUrl('/pages/users/order_details/index?order_id=' . $order['order_id'])->suffix('')->domain(false)->build());
    }


    /**
     * 订单发货
     * @param $order
     * @param array $data
     * @return bool|mixed
     */
    public function sendOrderDeliver($openid, string $goodsName, $order, array $data)
    {
        return $this->sendTemplate('ORDER_DELIVER_SUCCESS', $openid, [
            'keyword1' => $goodsName,
            'keyword2' => $order['pay_type'] == 'offline' ? '线下支付' : date('Y/m/d H:i', $order['pay_time']),
            'keyword3' => $order['user_address'],
            'keyword4' => $data['delivery_name'],
            'keyword5' => $data['delivery_id'],
            'first' => '亲,您的订单已发货,请注意查收',
            'remark' => '点击查看订单详情'
        ], sys_config('site_url') . Route::buildUrl('/pages/users/order_details/index?order_id=' . $order['order_id'])->suffix(false)->domain(false)->build());
    }

    /**
     * 订单发货
     * @param $order
     * @param array $data
     * @return bool|mixed
     */
    public function sendOrderPostage($openid, $order, array $data)
    {
        return $this->sendTemplate('ORDER_POSTAGE_SUCCESS', $openid, [
            'keyword1' => $order['order_id'],
            'keyword2' => $data['delivery_name'],
            'keyword3' => $data['delivery_id'],
            'first' => '亲,您的订单已发货,请注意查收',
            'remark' => '点击查看订单详情'
        ], sys_config('site_url') . Route::buildUrl('/pages/users/order_details/index?order_id=' . $order['order_id'])->suffix(false)->domain(false)->build());
    }


    /**
     * 退款发送客服消息
     * @param $order
     * @param string|null $link
     * @return bool
     */
    public function sendRefundServiceNotice($openid, $data, ?string $link = null)
    {
        return $this->sendTemplate('ADMIN_NOTICE', $openid, $data, $link);
    }

    /**
     * 确认收货发送模板消息
     * @param $order
     * @return bool|mixed
     */
    public function sendOrderTakeSuccess($openid, $order, $title)
    {
        return $this->sendTemplate('ORDER_TAKE_SUCCESS', $openid, [
            'first' => '亲，您的订单已收货',
            'keyword1' => $order['order_id'],
            'keyword2' => '已收货',
            'keyword3' => date('Y-m-d H:i:s', time()),
            'keyword4' => $title,
            'remark' => '感谢您的光临！'
        ]);
    }

    /**
     * 发送退款申请模板消息
     * @param array $data
     * @param $order
     * @return bool|mixed
     */
    public function sendOrderApplyRefund($openid, $order)
    {
        return $this->sendTemplate('ORDER_REFUND_STATUS', $openid, [
            'first' => '退款申请中',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['pay_price'],
            'keyword3' => date('Y-m-d H:i:s', $order['add_time']),
            'remark' => '点击查看订单详情'
        ], sys_config('site_url') . Route::buildUrl('/pages/users/order_details/index?order_id=' . $order['order_id'])->suffix('')->domain(false)->build());
    }

    /**
     * 发送退款模板消息
     * @param array $data
     * @param $order
     * @return bool|mixed
     */
    public function sendOrderRefundSuccess($openid, array $data, $order)
    {
        return $this->sendTemplate('ORDER_REFUND_STATUS', $openid, [
            'first' => '亲，您购买的商品已退款,本次退款' . $data['refund_price'] . '金额',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['pay_price'],
            'keyword3' => date('Y-m-d H:i:s', $order['add_time']),
            'remark' => '点击查看订单详情'
        ], sys_config('site_url') . Route::buildUrl('/pages/users/order_details/index?order_id=' . $order['order_id'])->suffix('')->domain(false)->build());
    }

    /**
     * 发送退款模板消息
     * @param array $data
     * @param $order
     * @return bool|mixed
     */
    public function sendOrderRefundNoStatus($openid, $order)
    {
        return $this->sendTemplate('ORDER_REFUND_STATUS', $openid, [
            'first' => '亲，您的退款申请未申请通过',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['pay_price'],
            'keyword3' => date('Y-m-d H:i:s', $order['add_time']),
            'remark' => '点击查看订单详情'
        ], sys_config('site_url') . Route::buildUrl('/pages/users/order_details/index?order_id=' . $order['order_id'])->suffix('')->domain(false)->build());
    }


    /**
     * 发送模板消息
     * @param string $tempCode 模板消息常量名称
     * @param $uid 用户uid
     * @param array $data 模板内容
     * @param string $link 跳转链接
     * @param string|null $color 文字颜色
     * @return bool|mixed
     */
    public function sendTemplate(string $tempCode, $openid, array $data, string $link = null, string $color = null)
    {
        try {
            if (!$openid) return true;
            $template = new Template('wechat');
            $template->to($openid)->color($color);
            if ($link) $template->url($link);
            return $template->send($tempCode, $data);
        } catch (\Exception $e) {
            return true;
        }
    }

}
