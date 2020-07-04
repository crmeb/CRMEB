<?php

namespace app\models\routine;

use app\admin\model\wechat\StoreService as ServiceModel;
use crmeb\basic\BaseModel;
use crmeb\services\template\Template;
use app\models\store\StoreOrder;
use app\models\user\WechatUser;


/**
 * TODO 小程序模板消息
 * Class RoutineTemplate
 * @package app\models\routine
 */
class RoutineTemplate extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'template_message';

    /**
     * 确认收货
     * @param $order
     * @param $title
     * @return bool
     */
    public static function sendOrderTakeOver($order, $title)
    {
        return self::sendOut('OREDER_TAKEVER', $order['uid'], [
            'thing1' => $order['order_id'],
            'thing2' => $title,
            'date5' => date('Y-m-d H:i:s', time()),
        ], '/pages/order_details/index?order_id=' . $order['order_id']);
    }

    /**
     * 送货和发货
     * @param $order
     * @param int $isGive 0 = 同城配送， 1 = 快递发货
     * @return bool
     */
    public static function sendOrderPostage($order, $isGive = 0)
    {
        if (is_string($order['cart_id']))
            $order['cart_id'] = json_decode($order['cart_id'], true);
        $storeTitle = StoreOrder::getProductTitle($order['cart_id']);
        $storeTitle = StoreOrder::getSubstrUTf8($storeTitle, 20, 'UTF-8', '');
        if ($isGive) {//快递发货
            return self::sendOut('ORDER_DELIVER_SUCCESS', $order['uid'], [
                'character_string2' => $order['delivery_id'],
                'thing1' => $order['delivery_name'],
                'time3' => date('Y-m-d H:i:s', time()),
                'thing5' => $storeTitle,
            ], '/pages/order_details/index?order_id=' . $order['order_id']);
        } else {//同城配送
            return self::sendOut('ORDER_POSTAGE_SUCCESS', $order['uid'], [
                'thing8' => $storeTitle,
                'character_string1' => $order['order_id'],
                'name4' => $order['delivery_name'],
                'phone_number10' => $order['delivery_id']
            ], '/pages/order_details/index?order_id=' . $order['order_id']);
        }
    }

    /**
     * 充值金额退款
     * @param $UserRecharge
     * @param $refund_price
     * @return bool
     */
    public static function sendRechargeSuccess($UserRecharge, $refund_price)
    {
        return self::sendOut('ORDER_REFUND', $UserRecharge['uid'], [
            'thing1' => '亲，您充值的金额已退款,本次退款' . $refund_price . '金额',
            'thing2' => '余额充值退款',
            'amount3' => $UserRecharge['price'],
            'character_string6' => $UserRecharge['order_id'],
        ], '/pages/user_bill/index?type=2');
    }

    /**
     * 订单退款成功发送消息
     * @param array $order
     * @return bool
     */
    public static function sendOrderRefundSuccess($order = array())
    {
        if (!$order) return false;
        if (is_string($order['cart_id']))
            $order['cart_id'] = json_decode($order['cart_id'], true);
        $storeTitle = StoreOrder::getProductTitle($order['cart_id']);
        $storeTitle = StoreOrder::getSubstrUTf8($storeTitle, 20, 'UTF-8', '');
        return self::sendOut('ORDER_REFUND', $order['uid'], [
            'thing1' => '已成功退款',
            'thing2' => $storeTitle,
            'amount3' => $order['pay_price'],
            'character_string6' => $order['order_id']
        ], '/pages/order_details/index?order_id=' . $order['order_id'] . '&isReturen=1');
    }

    /**
     * 订单退款失败
     * @param $order
     * @return bool
     */
    public static function sendOrderRefundFail($order, $storeTitle)
    {
        return self::sendOut('ORDER_REFUND', $order['uid'], [
            'thing1' => '退款失败',
            'thing2' => $storeTitle,
            'amount3' => $order['pay_price'],
            'character_string6' => $order['order_id']
        ], '/pages/order_details/index?order_id=' . $order['order_id'] . '&isReturen=1');
    }

    /**
     * 用户申请退款给管理员发送消息
     * @param array $order
     * @param string $refundReasonWap
     * @param array $adminList
     */
    public static function sendOrderRefundStatus($order)
    {
        $data['character_string4'] = $order['order_id'];
        $data['date5'] = date('Y-m-d H:i:s', time());
        $data['amount2'] = $order['pay_price'];
        $data['phrase7'] = '申请退款中';
        $data['thing8'] = '请及时处理';
        $kefuIds = ServiceModel::where('notify', 1)->column('uid', 'uid');
        foreach ($kefuIds as $uid) {
            self::sendOut('ORDER_REFUND_STATUS', $uid, $data);
        }
    }

    /**
     * 砍价成功通知
     * @param array $bargain
     * @param array $bargainUser
     * @param int $bargainUserId
     * @return bool
     */
    public static function sendBargainSuccess($bargain = array(), $bargainUser = array(), $bargainUserId = 0)
    {
        $data['thing1'] = $bargain['title'];
        $data['amount2'] = $bargainUser['min_price'];
        $data['thing3'] = '恭喜您，已经砍到最低价了';
        return self::sendOut('BARGAIN_SUCCESS', $bargainUser['uid'], $data, '/pages/activity/user_goods_bargain_list/index');
    }

    /**
     * 订单支付成功发送模板消息
     * @param $uid
     * @param $pay_price
     * @param $orderId
     * @param $payTime
     * @return bool|void
     */
    public static function sendOrderSuccess($uid, $pay_price, $orderId)
    {
        if ($orderId == '') return;
        $data['character_string1'] = $orderId;
        $data['amount2'] = $pay_price . '元';
        $data['date3'] = date('Y-m-d H:i:s', time());
        return self::sendOut('ORDER_PAY_SUCCESS', $uid, $data, '/pages/order_details/index?order_id=' . $orderId);
    }

    /**
     *提现失败
     * @param $uid
     * @param $msg
     * @param $extract_number
     * @param $extract_type
     * @return bool
     */
    public static function sendExtractFail($uid, $msg, $extract_number, $nickname)
    {
        return self::sendOut('USER_EXTRACT', $uid, [
            'thing1' => '提现失败：' . $msg,
            'amount2' => $extract_number . '元',
            'thing3' => $nickname,
            'date4' => date('Y-m-d H:i:s', time())
        ], '/pages/user_spread_money/index?type=2');
    }

    /**
     * 提现成功
     * @param $uid
     * @param $extract_number
     * @param $nickname
     * @return bool
     */
    public static function sendExtractSuccess($uid, $extract_number, $nickname)
    {
        return self::sendOut('USER_EXTRACT', $uid, [
            'thing1' => '提现成功',
            'amount2' => $extract_number . '元',
            'thing3' => $nickname,
            'date4' => date('Y-m-d H:i:s', time())
        ], '/pages/user_spread_money/index?type=2');
    }

    /**
     * 拼团成功通知
     * @param $uid
     * @param $pinkTitle
     * @param $nickname
     * @param $pinkTime
     * @param $count
     * @return bool
     */
    public static function sendPinkSuccess($uid, $pinkTitle, $nickname, $pinkTime, $count, string $link = '')
    {
        return self::sendOut('PINK_TRUE', $uid, [
            'thing1' => StoreOrder::getSubstrUTf8($pinkTitle, 20, 'UTF-8', ''),
            'name3' => $nickname,
            'date5' => date('Y-m-d H:i:s', $pinkTime),
            'number2' => $count
        ], $link);
    }

    /**
     * 拼团状态通知
     * @param $uid
     * @param $pinkTitle
     * @param $count
     * @param $remarks
     * @return bool
     */
    public static function sendPinkFail($uid, $pinkTitle, $count, $remarks, $link)
    {
        return self::sendOut('PINK_STATUS', $uid, [
            'thing2' => StoreOrder::getSubstrUTf8($pinkTitle, 20, 'UTF-8', ''),
            'thing1' => $count,
            'thing3' => $remarks
        ], $link);
    }

    /**
     * 赠送积分消息提醒
     * @param $uid
     * @param $order
     * @param $gainIntegral
     * @param $integral
     * @return bool
     */
    public static function sendUserIntegral($uid, $order, $gainIntegral, $integral)
    {
        if (!$order) return false;
        if (is_string($order['cart_id']))
            $order['cart_id'] = json_decode($order['cart_id'], true);
        $storeTitle = StoreOrder::getProductTitle($order['cart_id']);
        $storeTitle = StoreOrder::getSubstrUTf8($storeTitle, 20);
        return self::sendOut('INTEGRAL_ACCOUT', $uid, [
            'character_string2' => $order['order_id'],
            'thing3' => $storeTitle,
            'amount4' => $order['pay_price'],
            'number5' => $gainIntegral,
            'number6' => $integral
        ], '/pages/user_bill/index?type=2');
    }

    /**
     * 发送模板消息
     * @param string $TempCode 模板消息常量名称
     * @param int $uid 用户uid
     * @param array $data 模板内容
     * @param string $link 跳转链接
     * @return bool
     */
    public static function sendOut(string $tempCode, $uid, array $data, string $link = '')
    {
        $openid = WechatUser::uidToOpenid($uid);
        if (!$openid) return false;
        $template = new Template('subscribe');
        return $template->to($openid)->url($link)->send($tempCode, $data);
    }
}