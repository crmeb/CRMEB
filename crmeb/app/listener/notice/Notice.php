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

namespace app\listener\notice;

use app\jobs\notice\EnterpriseWechatJob;
use app\jobs\notice\PrintJob;
use app\services\order\StoreOrderServices;
use app\services\message\notice\{
    EnterpriseWechatService,
    RoutineTemplateListService,
    SmsService,
    SystemMsgService,
    WechatTemplateListService
};
use app\services\order\StoreOrderCartInfoServices;
use app\services\user\UserServices;
use crmeb\interfaces\ListenerInterface;
use crmeb\utils\Str;

/**
 * 订单创建事件
 * Class Create
 * @package app\listener\order
 */
class Notice implements ListenerInterface
{
    public function handle($event): void
    {
        try {
            [$data, $mark] = $event;

            /** @var WechatTemplateListService $WechatTemplateList */
            $WechatTemplateList = app()->make(WechatTemplateListService::class);

            /** @var RoutineTemplateListService $RoutineTemplateList */
            $RoutineTemplateList = app()->make(RoutineTemplateListService::class);

            /** @var SystemMsgService $SystemMsg */
            $SystemMsg = app()->make(SystemMsgService::class);

            /** @var EnterpriseWechatService $EnterpriseWechat */
            $EnterpriseWechat = app()->make(EnterpriseWechatService::class);

            /** @var SmsService $NoticeSms */
            $NoticeSms = app()->make(SmsService::class);

            /** @var StoreOrderCartInfoServices $orderInfoServices */
            $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);

            /** @var UserServices $UserServices */
            $UserServices = app()->make(UserServices::class);

            if ($mark) {
                $WechatTemplateList->setEvent($mark);
                $SystemMsg->setEvent($mark);
                $NoticeSms->setEvent($mark);
                $RoutineTemplateList->setEvent($mark);
                $EnterpriseWechat->setEvent($mark);
                switch ($mark) {
                    //绑定推广关系
                    case 'bind_spread_uid':
                        if (isset($data['spreadUid']) && $data['spreadUid']) {
                            $name = $data['nickname'] ?? '';
                            //站内信
                            $SystemMsg->sendMsg($data['spreadUid'], ['nickname' => $name]);
                            //模板消息公众号模版消息
                            $WechatTemplateList->sendBindSpreadUidSuccess($data['spreadUid'], $name);
                        }
                        break;
                    //支付成功给用户
                    case 'order_pay_success':
                        $pay_price = $data['pay_price'];
                        $order_id = $data['order_id'];
                        //短信
                        $NoticeSms->sendSms($data['user_phone'], compact('order_id', 'pay_price'));
                        $data['is_channel'] = $data['is_channel'] ?? 2;
                        $data['total_num'] = $data['total_num'] ?? 1;
                        //站内信
                        $SystemMsg->sendMsg($data['uid'], ['order_id' => $data['order_id'], 'total_num' => $data['total_num'], 'pay_price' => $data['pay_price']]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderPaySuccess($data['uid'], $data);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendOrderSuccess($data['uid'], $data['pay_price'], $data['order_id']);
                        //小票打印
                        if (isset($data['cart_id']) && $data['cart_id']) {
                            PrintJob::dispatch([$data['id']]);
                        }
                        break;
                    //发货给用户
                    case 'order_deliver_success':
                        $orderInfo = $data['orderInfo'];
                        $storeTitle = $data['storeName'];
                        $datas = $data['data'];
                        $service = app()->make(UserServices::class);
                        $nickname = $service->value(['uid' => $orderInfo->uid], 'nickname');
                        //短信
                        $order_id = $orderInfo->order_id;
                        $store_name = $storeTitle;
                        $NoticeSms->sendSms($orderInfo->user_phone, compact('order_id', 'store_name', 'nickname'));
                        //小程序公众号消息
                        $storeTitle = Str::substrUTf8($storeTitle, 20, 'UTF-8', '');
                        $isGive = 0;
                        //站内信
                        $SystemMsg->sendMsg($orderInfo['uid'], ['nickname' => $nickname, 'store_name' => $storeTitle, 'order_id' => $orderInfo['order_id'], 'delivery_name' => $orderInfo['delivery_name'], 'delivery_id' => $orderInfo['delivery_id'], 'user_address' => $orderInfo['user_address']]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderDeliver($orderInfo['uid'], $storeTitle, $orderInfo->toArray(), $datas);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendOrderPostage($orderInfo['uid'], $orderInfo->toArray(), $storeTitle, $isGive);
                        break;
                    //发货快递给用户
                    case 'order_postage_success':
                        $orderInfo = $data['orderInfo'];
                        $storeTitle = $data['storeName'];
                        $datas = $data['data'];
                        $service = app()->make(UserServices::class);
                        $nickname = $service->value(['uid' => $orderInfo->uid], 'nickname');
                        //短信
                        $order_id = $orderInfo->order_id;
                        $store_name = $storeTitle;
                        $NoticeSms->sendSms($orderInfo->user_phone, compact('order_id', 'store_name', 'nickname'));
                        $storeTitle = Str::substrUTf8($storeTitle, 20, 'UTF-8', '');
                        $isGive = 1;
                        //站内信
                        $smsdata = ['nickname' => $nickname, 'store_name' => $storeTitle, 'order_id' => $orderInfo['order_id'], 'delivery_name' => $orderInfo['delivery_name'], 'delivery_id' => $orderInfo['delivery_id'], 'user_address' => $orderInfo['user_address']];
                        $SystemMsg->sendMsg($orderInfo['uid'], $smsdata);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderPostage($orderInfo['uid'], $orderInfo->toArray(), $store_name);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendOrderPostage($orderInfo['uid'], $orderInfo->toArray(), $storeTitle, $isGive);
                        break;
                    //确认收货给用户
                    case 'order_take':
                        $order = is_object($data['order']) ? $data['order']->toArray() : $data['order'];
                        $storeTitle = $data['storeTitle'];
                        //模板变量
                        $store_name = $storeTitle;
                        $order_id = $order['order_id'];
                        $NoticeSms->sendSms($order['user_phone'], compact('store_name', 'order_id'));
                        //站内信
                        $SystemMsg->sendMsg($order['uid'], ['order_id' => $order['order_id'], 'store_name' => $storeTitle]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderTakeSuccess($order['uid'], $order, $storeTitle);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendOrderTakeOver($order['uid'], $order, $storeTitle);
                        break;
                    //改价给用户
                    case 'price_revision':
                        $order = $data['order'];
                        $pay_price = $data['pay_price'];
                        $order['storeName'] = $orderInfoServices->getCarIdByProductTitle((int)$order['id']);
                        //短信
                        $NoticeSms->sendSms($order['user_phone'], ['order_id' => $order['order_id'], 'pay_price' => $pay_price]);
                        //站内信
                        $SystemMsg->sendMsg($order['uid'], ['order_id' => $order['order_id'], 'pay_price' => $pay_price]);
                        $WechatTemplateList->sendPriceRevision($order['uid'], $order);
                        break;
                    //退款成功
                    case 'order_refund':
                        $datas = $data['data'];
                        $order = $data['order'];
                        $order['refund_price'] = $datas['refund_price'];
                        $order['refund_no'] = $datas['refund_no'];
                        $storeName = $orderInfoServices->getCarIdByProductTitle((int)$order['id']);
                        $storeTitle = Str::substrUTf8($storeName, 20, 'UTF-8', '');
                        //站内信
                        $SystemMsg->sendMsg($order['uid'], ['order_id' => $order['order_id'], 'pay_price' => $order['pay_price'], 'refund_price' => $datas['refund_price']]);
                        //模板消息公众号模版消息
                        $title = '亲，您购买的商品已退款';
                        $WechatTemplateList->sendOrderRefund($order['uid'], $order, $title);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendOrderRefundSuccess($order['uid'], $order, $storeTitle, $datas);
                        break;
                    //退款未通过
                    case 'send_order_refund_no_status':
                        $order = $data['orderInfo'];
                        $order['pay_price'] = $order['refund_price'];
                        $order['refund_no'] = $order['order_id'];
                        $storeTitle = Str::substrUTf8($order['cart_info'][0]['productInfo']['store_name'], 20, 'UTF-8', '');
                        //站内信
                        $SystemMsg->sendMsg($order['uid'], ['order_id' => $order['order_id'], 'pay_price' => $order['refund_price'], 'store_name' => $storeTitle]);
                        //模板消息公众号模版消息
                        $title = '亲，您的订单拒绝退款，点击查看原因';
                        $WechatTemplateList->sendOrderRefund($order['uid'], $order, $title);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendOrderRefundFail($order['uid'], $order, $storeTitle);
                        break;
                    //充值余额
                    case 'recharge_success':
                        $order = $data['order'];
                        $now_money = $data['now_money'];
                        //站内信
                        $SystemMsg->sendMsg($order['uid'], ['order_id' => $order['order_id'], 'price' => $order['price'], 'now_money' => $now_money]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendRechargeSuccess($order['uid'], $order);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendRechargeSuccess($order['uid'], $order, $now_money);
                        break;
                    //充值退款
                    case 'recharge_order_refund_status':
                        $datas = $data['data'];
                        $UserRecharge = $data['UserRecharge'];
                        $now_money = $data['now_money'];
                        //站内信
                        $SystemMsg->sendMsg($UserRecharge['uid'], ['refund_price' => $datas['refund_price'], 'order_id' => $UserRecharge['order_id'], 'price' => $UserRecharge['price']]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendRechargeRefundStatus($UserRecharge['uid'], $datas, $UserRecharge);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendRechargeSuccess($UserRecharge['uid'], $UserRecharge, $now_money);
                        break;
                    //积分
                    case 'integral_accout':
                        $order = $data['order'];
                        //站内信
                        $SystemMsg->sendMsg($order['uid'], ['order_id' => $order['order_id'], 'store_name' => $data['storeTitle'], 'pay_price' => $order['pay_price'], 'gain_integral' => $data['give_integral'], 'integral' => $data['integral']]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendUserIntegral($order['uid'], $order, $data);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendUserIntegral($order['uid'], $data['order'], $data['storeTitle'], $data['give_integral'], $data['integral']);
                        break;
                    //佣金
                    case 'order_brokerage':
                        $brokeragePrice = $data['brokeragePrice'];
                        $goodsName = $data['goodsName'];
                        $goodsPrice = $data['goodsPrice'];
                        $add_time = $data['add_time'];
                        $spread_uid = $data['spread_uid'];
                        //站内信
                        $SystemMsg->sendMsg($spread_uid, ['goods_name' => $goodsName, 'goods_price' => $goodsPrice, 'brokerage_price' => $brokeragePrice]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderBrokerageSuccess($spread_uid, $brokeragePrice, $add_time);
                        break;
                    //砍价成功
                    case 'bargain_success':
                        $uid = $data['uid'];
                        $bargainInfo = $data['bargainInfo'];
                        $bargainUserInfo = $data['bargainUserInfo'];
                        //站内信
                        $SystemMsg->sendMsg($uid, ['title' => $bargainInfo['title'], 'min_price' => $bargainInfo['min_price']]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendBargainSuccess($uid, $bargainInfo, $bargainUserInfo, $uid);
                        //模板消息小程序订阅消息
                        $bargainInfo['title'] = Str::substrUTf8($bargainInfo['title'], 20, 'UTF-8', '');
                        $RoutineTemplateList->sendBargainSuccess($uid, $bargainInfo, $bargainUserInfo, $uid);
                        break;
                    //开团成功
                    case 'open_pink_success':
                        $orderInfo = $data['orderInfo'];
                        $title = $data['title'];
                        $pink = $data['pink'];
                        $nickname = $UserServices->value(['uid' => $orderInfo['uid']], 'nickname');
                        //站内信
                        $SystemMsg->sendMsg($orderInfo['uid'], ['title' => $title, 'nickname' => $nickname, 'count' => $pink['people'], 'pink_time' => date('Y-m-d H:i:s', $pink['add_time'])]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderPinkOpenSuccess($orderInfo['uid'], $pink, $title);
                        break;
                    //参团成功
                    case 'can_pink_success':
                        $orderInfo = $data['orderInfo'];
                        $title = $data['title'];
                        $pink = $data['pink'];
                        $nickname = $UserServices->value(['uid' => $orderInfo['uid']], 'nickname');
                        //站内信
                        $SystemMsg->sendMsg($orderInfo['uid'], ['title' => $title, 'nickname' => $nickname, 'count' => $pink['people'], 'pink_time' => date('Y-m-d H:i:s', $pink['add_time'])]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderPinkUseSuccess($orderInfo['uid'], $orderInfo, $title);
                        break;
                    //拼团成功
                    case 'order_user_groups_success':
                        $list = $data['list'];
                        $title = $data['title'];
                        $url = '/pages/users/order_details/index?order_id=' . $list['order_id'];
                        //站内信
                        $SystemMsg->sendMsg($list['uid'], ['title' => $title, 'nickname' => $list['nickname'], 'count' => $list['people'], 'pink_time' => date('Y-m-d H:i:s', $list['add_time'])]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderPinkSuccess($list['uid'], $list, $title);
                        //模板消息小程序订阅消息
                        $title = Str::substrUTf8($title, 20, 'UTF-8', '');
                        $RoutineTemplateList->sendPinkSuccess($list['uid'], $title, $list['nickname'], $list['add_time'], $list['people'], $url);
                        break;
                    //取消拼团
                    case 'send_order_pink_clone':
                        $uid = $data['uid'];
                        $pink = $data['pink'];
                        //站内信
                        $SystemMsg->sendMsg($uid, ['title' => $pink->title, 'count' => $pink->people]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderPinkClone($uid, $pink, $pink->title);
                        break;
                    //拼团失败
                    case 'send_order_pink_fial':
                        $uid = $data['uid'];
                        $pink = $data['pink'];
                        //站内信
                        $SystemMsg->sendMsg($uid, ['title' => $pink->title, 'count' => $pink->people]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendOrderPinkFail($uid, $pink, $pink->title);
                        break;
                    //提现成功
                    case 'user_extract':
                        $extractNumber = $data['extractNumber'];
                        $nickname = $data['nickname'];
                        $uid = $data['uid'];
                        //站内信
                        $SystemMsg->sendMsg($uid, ['extract_number' => $extractNumber, 'nickname' => $nickname, 'date' => date('Y-m-d H:i:s', time())]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendUserExtract($uid, $extractNumber);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendExtractSuccess($uid, $extractNumber, $nickname);
                        break;
                    //提现失败
                    case 'user_balance_change':
                        $extract_number = $data['extract_number'];
                        $message = $data['message'];
                        $uid = $data['uid'];
                        $nickname = $data['nickname'];
                        //站内信
                        $SystemMsg->sendMsg($uid, ['extract_number' => $extract_number, 'nickname' => $nickname, 'date' => date('Y-m-d H:i:s', time()), 'message' => $message]);
                        //模板消息公众号模版消息
                        $WechatTemplateList->sendExtractFail($uid, $extract_number, $message);
                        //模板消息小程序订阅消息
                        $RoutineTemplateList->sendExtractFail($uid, $message, $extract_number, $nickname);
                        break;
                    //提醒付款给用户
                    case 'order_pay_false':
                        $order = $data['order'];
                        $order_id = $order['order_id'];
                        $order['storeName'] = $orderInfoServices->getCarIdByProductTitle((int)$order['id']);
                        //短信
                        $NoticeSms->sendSms($order['user_phone'], compact('order_id'));
                        //站内信
                        $SystemMsg->sendMsg($order['uid'], ['order_id' => $order_id]);
                        $WechatTemplateList->sendOrderPayFalse($order['uid'], $order);
                        break;
                    //新订单给客服
                    case 'admin_pay_success_code':
                        $order = $data;
                        //站内信
                        $SystemMsg->kefuSystemSend(['order_id' => $order['order_id']]);
                        //短信
                        $NoticeSms->sendAdminPaySuccess($order);
                        //公众号小程序
                        $storeName = $orderInfoServices->getCarIdByProductTitle((int)$order['id']);
                        $title = '亲，来新订单啦！';
                        $status = '新订单';
                        $link = '/pages/admin/orderDetail/index?id=' . $order['order_id'];
                        $WechatTemplateList->sendAdminOrder($order['order_id'], $storeName, $title, $status, $link);
                        //企业微信通知
                        EnterpriseWechatJob::dispatch(['order_id' => $order['order_id']]);
                        break;
                    //确认收货给客服
                    case 'send_admin_confirm_take_over':
                        $order = $data['order'];
                        $storeTitle = $data['storeTitle'];
                        //站内信
                        $SystemMsg->kefuSystemSend(['storeTitle' => $storeTitle, 'order_id' => $order['order_id']]);
                        //短信
                        $NoticeSms->sendAdminConfirmTakeOver($order);
                        //公众号
                        $storeName = $orderInfoServices->getCarIdByProductTitle((int)$order['id']);
                        $title = '亲，用户已经收到货物啦！';
                        $status = '订单收货';
                        $link = '/pages/admin/orderDetail/index?id=' . $order['order_id'];
                        $WechatTemplateList->sendAdminOrder($order['order_id'], $storeName, $title, $status, $link);
                        //企业微信通知
                        EnterpriseWechatJob::dispatch(['storeTitle' => $storeTitle, 'order_id' => $order['order_id']]);
                        break;
                    //申请退款给客服发消息
                    case 'send_order_apply_refund':
                        $order = $data['order'];
                        //站内信
                        $SystemMsg->kefuSystemSend(['order_id' => $order['order_id']]);
                        //短信
                        $NoticeSms->sendAdminRefund($order);
                        //企业微信通知
                        EnterpriseWechatJob::dispatch(['order_id' => $order['order_id']]);
                        //公众号
                        $storeName = $orderInfoServices->getCarIdByProductTitle((int)$order['id']);
                        $title = '亲，您有个退款订单待处理！';
                        $status = '订单退款';
                        $link = '/pages/admin/orderDetail/index?id=' . $order['refund_no'] . '&types=-3';
                        $WechatTemplateList->sendAdminOrder($order['refund_no'], $storeName, $title, $status, $link);
                        break;
                    //提现申请给客服
                    case 'kefu_send_extract_application':
                        //站内信
                        $SystemMsg->kefuSystemSend($data);
                        //企业微信通知
                        EnterpriseWechatJob::dispatch($data);
                        break;
                }

            }

        } catch (\Throwable $e) {

        }
    }

}
