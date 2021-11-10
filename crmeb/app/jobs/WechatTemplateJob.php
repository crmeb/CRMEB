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

namespace app\jobs;


use crmeb\basic\BaseJobs;
use crmeb\services\template\Template;
use crmeb\traits\QueueTrait;
use think\facade\Log;
use think\facade\Route;


/**
 * Class WechatTemplateJob
 * @package crmeb\jobs
 */
class WechatTemplateJob extends BaseJobs
{
    use QueueTrait;


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
            $template->to($openid);
            $template->color($color);
            if ($link) {
                $url =
                    sys_config('site_url') . Route::buildUrl($link)
                        ->suffix('')
                        ->domain(false)->build();
                $template->url($url);
            }
            return $template->send($tempCode, $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return true;
        }
    }

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
        ], '/pages/users/order_details/index?order_id=' . $order['order_id']);
    }

    /**
     * 购买会员成功
     * @param $openid
     * @param $order
     * @return bool|mixed
     */
    public function sendMemberOrderPaySuccess($openid, $order)
    {
        return $this->sendTemplate('ORDER_PAY_SUCCESS', $openid, [
            'first' => '亲，购买会员成功，恭喜您成为本平台尊贵会员！',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['pay_price'],
            'remark' => '点击查看订单详情'
        ], '/pages/annex/vip_paid/index');
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
        ], '/pages/users/order_details/index?order_id=' . $order['order_id']);
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
        ], '/pages/users/order_details/index?order_id=' . $order['order_id']);
    }

    /**
     * 发送客服消息
     * @param $order
     * @param string|null $link
     * @return bool
     */
    public function sendServiceNotice($openid, $data)
    {
        return $this->sendTemplate('ADMIN_NOTICE', $openid,
            [
                'keyword1' => '新订单',
                'keyword2' => $data['delivery_name'],
                'keyword3' => $data['delivery_id'],
                'first' => '亲,您有新的订单待处理',
                'remark' => '点击查看订单详情'
            ], '/pages/users/order_details/index?order_id=' . $data['order_id']);
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
            'first' => '你有一笔退款订单需要处理',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['status'],
            'keyword3' => date('Y-m-d H:i:s', $order['add_time']),
            'remark' => '点击查看退款详情'
        ], '/pages/admin/orderDetail/index?id=' . $order['order_id']);
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
        ], '/pages/users/order_details/index?order_id=' . $order['order_id']);
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
        ], '/pages/users/order_details/index?order_id=' . $order['order_id']);
    }

    /**
     * 发送用户充值退款模板消息
     * @param array $data
     * @param $userRecharge
     * @return bool|mixed
     */
    public function sendRechargeRefundStatus($openid, array $data, $userRecharge)
    {
        return $this->sendTemplate('ORDER_REFUND_STATUS', $openid, [
            'first' => '亲，您充值的金额已退款,本次退款' .
                $data['refund_price'] . '金额',
            'keyword1' => $userRecharge['order_id'],
            'keyword2' => $userRecharge['price'],
            'keyword3' => date('Y-m-d H:i:s', $userRecharge['add_time']),
            'remark' => '点击查看订单详情'
        ], '/pages/users/user_bill/index');
    }

    /**
     * 拼团成功发送模板消息
     * @param $uid
     * @param $order_id
     * @param $title
     * @return bool|mixed
     */
    public function sendOrderPinkSuccess($openid, $order_id, $pinkId, $title)
    {
        return $this->sendTemplate('ORDER_USER_GROUPS_SUCCESS', $openid, [
            'first' => '亲，您的拼团已经完成了',
            'keyword1' => $order_id,
            'keyword2' => $title,
            'remark' => '点击查看订单详情'
        ], '/pages/activity/goods_combination_status/index?id=' . $pinkId);
    }

    /**
     * 参团成功发送模板消息
     * @param $uid
     * @param $order_id
     * @param $title
     * @return bool|mixed
     */
    public function sendOrderPinkUseSuccess($openid, string $order_id, string $title, int $pink_id)
    {
        return $this->sendTemplate('ORDER_USER_GROUPS_SUCCESS', $openid, [
            'first' => '亲，您已成功参与拼团',
            'keyword1' => $order_id,
            'keyword2' => $title,
            'remark' => '点击查看订单详情'
        ], '/pages/activity/goods_combination_status/index?id=' . $pink_id);
    }

    /**
     * 取消拼团发送模板消息
     * @param $uid
     * @param StorePink $order_id
     * @param $price
     * @param string $title
     * @return bool|mixed
     */
    public function sendOrderPinkClone($openid, $pink, $title)
    {
        return $this->sendTemplate('ORDER_USER_GROUPS_LOSE', $openid, [
            'first' => '亲，您的拼团取消',
            'keyword1' => $title,
            'keyword2' => $pink->price,
            'keyword3' => $pink->price,
            'remark' => '点击查看订单详情'
        ], '/pages/activity/goods_combination_status/index?id=' . $pink->id);
    }

    /**
     * 拼团失败发送模板消息
     * @param $uid
     * @param StorePink $pink
     * @param $title
     * @return bool|mixed
     */
    public function sendOrderPinkFial($openid, $pink, $title)
    {
        return $this->sendTemplate('ORDER_USER_GROUPS_LOSE', $openid, [
            'first' => '亲，您的拼团失败',
            'keyword1' => $title,
            'keyword2' => $pink->price,
            'keyword3' => $pink->price,
            'remark' => '点击查看订单详情'
        ], '/pages/activity/goods_combination_status/index?id=' . $pink->id);
    }

    /**
     * 开团成功发送模板消息
     * @param $uid
     * @param StorePink $pink
     * @param $title
     * @return bool|mixed
     */
    public function sendOrderPinkOpenSuccess($openid, $pink, $title)
    {
        return $this->sendTemplate('OPEN_PINK_SUCCESS', $openid, [
            'first' => '您好，您已成功开团！赶紧与小伙伴们分享吧！！！',
            'keyword1' => $title,
            'keyword2' => $pink['total_price'],
            'keyword3' => $pink['people'],
            'remark' => '点击查看订单详情'
        ], '/pages/activity/goods_combination_status/index?id=' . $pink['id']);
    }

    /**
     * 砍价成功发送模板消息
     * @param $uid
     * @param StoreBargain $bargain
     * @return bool|mixed
     */
    public function sendBargainSuccess($openid, $bargain, $bargainUser = [], $bargainUserId = 0)
    {
        return $this->sendTemplate('BARGAIN_SUCCESS', $openid, [
            'first' => '好腻害！你的朋友们已经帮你砍到底价了！',
            'keyword1' => $bargain['title'],
            'keyword2' => $bargain['min_price'],
            'remark' => '点击查看订单详情'
        ], '/pages/activity/goods_bargain_details/index?id=' . $bargain['id'] . '&bargain=' . $bargainUserId);
    }


    /**
     * 佣金到账发送模板消息
     * @param $order
     * @return bool
     */
    public function sendOrderBrokerageSuccess(string $openid, string $brokeragePrice, string $goodsName, string $goodsPrice, $orderTime)
    {
        return $this->sendTemplate('ORDER_BROKERAGE', $openid, [
            'first' => '亲，您有一笔佣金入账!',
            'keyword1' => $brokeragePrice,//分销佣金
            'keyword2' => $goodsPrice . "元",//交易金额
            'keyword3' => date('Y-m-d H:i:s', $orderTime),//结算时间
            'remark' => '点击查看订单详情'
        ], '/pages/users/user_spread_user/index');
    }

    /** 绑定推广关系发送消息提醒
     * @param string $openid
     * @param string $userName
     * @return bool|mixed
     */
    public function sendBindSpreadUidSuccess(string $openid, string $userName)
    {
        return $this->sendTemplate('BIND_SPREAD_UID', $openid, [
            'first' => '恭喜，加入您的团队',
            'keyword1' => $userName,
            'keyword2' => date('Y-m-d H:i:s', time()),
            'remark' => '授人以鱼不如授人以渔，一起分享赚钱吧，点击查看详情！'
        ], '/pages/users/user_spread_user/index');
    }
}
