<?php
/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2019/11/13 4:52 PM
 */

namespace crmeb\repositories;

use app\models\user\WechatUser;
use crmeb\services\WechatService;
use crmeb\services\WechatTemplateService;
use crmeb\services\workerman\ChannelService;
use app\models\routine\RoutineTemplate;
use app\models\store\StoreOrderCartInfo;
use app\models\user\User;
use crmeb\services\YLYService;
use think\facade\Log;
use think\facade\Route;

/** 消息通知静态类
 * Class NoticeRepositories
 * @package crmeb\repositories
 */
class NoticeRepositories
{
    /** 支付成功通知
     * @param $order
     * @param $formId
     */
    public static function noticeOrderPaySuccess($order)
    {
        $wechatUser = WechatUser::where('uid', $order['uid'])->field(['openid', 'routine_openid'])->find();
        if ($wechatUser) {
            $openid = $wechatUser['openid'];
            $routineOpenid = $wechatUser['routine_openid'];
            try {
                if ($openid && in_array($order['is_channel'], [0, 2])) {//公众号发送模板消息
                    WechatTemplateService::sendTemplate($openid, WechatTemplateService::ORDER_PAY_SUCCESS, [
                        'first' => '亲，您购买的商品已支付成功',
                        'keyword1' => $order['order_id'],
                        'keyword2' => $order['pay_price'],
                        'remark' => '点击查看订单详情'
                    ], Route::buildUrl('order/detail/' . $order['order_id'])->suffix('')->domain(true)->build());
                    //订单支付成功后给客服发送模版消息
                    WechatTemplateService::sendAdminNoticeTemplate([
                        'first' => "亲,您有一个新订单 \n订单号:{$order['order_id']}",
                        'keyword1' => '新订单',
                        'keyword2' => '已支付',
                        'keyword3' => date('Y/m/d H:i', time()),
                        'remark' => '请及时处理'
                    ]);
                    //订单支付成功后给客服发送客服消息
                    CustomerRepository::sendOrderPaySuccessCustomerService($order, 1);
                } else if ($routineOpenid && in_array($order['is_channel'], [1, 2])) {//小程序发送模板消息
                    RoutineTemplate::sendOrderSuccess($order['uid'], $order['pay_price'], $order['order_id']);
                    //订单支付成功后给客服发送客服消息
                    CustomerRepository::sendOrderPaySuccessCustomerService($order, 0);
                }

            } catch (\Exception $e) {
                Log::error('购买后发送提醒失败，错误原因：' . $e->getMessage());
            }
        }
        //打印小票
        $switch = sys_config('pay_success_printing_switch') ? true : false;
        if ($switch) {
            try {
                $order['cart_id'] = is_string($order['cart_id']) ? json_decode($order['cart_id'], true) : $order['cart_id'];
                $cartInfo = StoreOrderCartInfo::whereIn('cart_id', $order['cart_id'])->where('oid', $order['id'])->field('cart_info')->select();
                $cartInfo = count($cartInfo) ? $cartInfo->toArray() : [];
                $product = [];
                foreach ($cartInfo as $item) {
                    $value = is_string($item['cart_info']) ? json_decode($item['cart_info'], true) : $item['cart_info'];
                    $value['productInfo']['store_name'] = $value['productInfo']['store_name'] ?? "";
                    $value['productInfo']['store_name'] = StoreOrderCartInfo::getSubstrUTf8($value['productInfo']['store_name'], 10, 'UTF-8', '');
                    $product[] = $value;
                }
                YLYService::instance()->setContent(sys_config('site_name'), is_object($order) ? $order->toArray() : $order, $product)->orderPrinting();
            } catch (\Exception $e) {
                Log::error('小票打印出现错误,错误原因：' . $e->getMessage());
            }
        }
        //短信通知 下发用户支付成功 下发管理员支付通知
        event('ShortMssageSend', [$order['order_id'], ['PaySuccess', 'AdminPaySuccess']]);

    }
}