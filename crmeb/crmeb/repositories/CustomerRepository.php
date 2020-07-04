<?php
namespace crmeb\repositories;

use app\models\store\StoreBargain;
use app\models\store\StoreCart;
use app\models\store\StoreCombination;
use app\models\store\StoreProduct;
use app\models\store\StoreSeckill;
use app\models\store\StoreService;
use app\models\user\User;
use app\models\user\WechatUser;
use think\facade\Log;
use crmeb\services\WechatService;

/**
 * 客服消息推送
 * Class CustomerService
 * @package crmeb\services
 */
class CustomerRepository
{

    /**
     * 订单支付成功后给客服发送客服消息
     * @param $order
     * @param int $type  1 公众号 0 小程序
     * @return string
     */
    public static function sendOrderPaySuccessCustomerService($order,$type = 0)
    {
        $serviceOrderNotice = StoreService::getStoreServiceOrderNotice();
        if(count($serviceOrderNotice)){
            foreach ($serviceOrderNotice as $key=>&$item){
                $userInfo = WechatUser::get($item);
                if($userInfo){
                    $userInfo = $userInfo->toArray();
                    if($userInfo['subscribe'] && $userInfo['openid']){
                        $orderStatus = StoreService::orderServiceStatus($userInfo['uid']);
                        if($orderStatus){
                            // 统计管理开启  推送图文消息
                            $head = '订单提醒 订单号：'.$order['order_id'];
                            $url = sys_config('site_url') . '/customer/orderdetail/'.$order['order_id'];
                            $description = '';
                            $image = sys_config('site_logo');
                            if(isset($order['seckill_id']) && $order['seckill_id'] > 0){
                                $description .= '秒杀产品：'.StoreSeckill::getProductField($order['seckill_id'], 'title');
                                $image = StoreSeckill::getProductField($order['seckill_id'], 'image');
                            }else if(isset($order['combination_id']) && $order['combination_id'] > 0){
                                $description .= '拼团产品：'.StoreCombination::getCombinationField($order['combination_id'], 'title');
                                $image = StoreCombination::getCombinationField($order['combination_id'], 'image');
                            }else if(isset($order['bargain_id']) && $order['bargain_id'] > 0){
                                $description .= '砍价产品：'.StoreBargain::getBargainField($order['bargain_id'], 'title');
                                $image = StoreBargain::getBargainField($order['bargain_id'], 'image');
                            }else{
                                $productIds = StoreCart::getCartIdsProduct((array)$order['cart_id']);
                                $storeProduct = StoreProduct::getProductStoreNameOrImage($productIds);
                                if(count($storeProduct)){
                                    foreach ($storeProduct as $value){
                                        $description .= $value['store_name'].'  ';
                                        $image = $value['image'];
                                    }
                                }
                            }
                            $message = WechatService::newsMessage($head, $description, $url, $image);
                            try {
                                WechatService::staffService()->message($message)->to($userInfo['openid'])->send();
                            } catch (\Exception $e) {
                                Log::error($userInfo['nickname'] . '发送失败' . $e->getMessage());
                            }
                        }else{
                            // 推送文字消息
                            $head = "客服提醒：亲,您有一个新订单 \r\n订单单号:{$order['order_id']}\r\n支付金额：￥{$order['pay_price']}\r\n备注信息：{$order['mark']}\r\n订单来源：小程序";
                            if($type) $head = "客服提醒：亲,您有一个新订单 \r\n订单单号:{$order['order_id']}\r\n支付金额：￥{$order['pay_price']}\r\n备注信息：{$order['mark']}\r\n订单来源：公众号";
                            try {
                                WechatService::staffService()->message($head)->to($userInfo['openid'])->send();
                            } catch (\Exception $e) {
                                Log::error($userInfo['nickname'] . '发送失败' . $e->getMessage());
                            }
                         }
                    }
                }

            }
        }
    }

    /**
     * 提取管理员权限
     * @param callable $callable 回调函数
     */
    public static function getAdminNoticeAuth(callable $callable)
    {
        $serviceOrderNotice = StoreService::getStoreServiceOrderNotice();
        if (count($serviceOrderNotice)) {
            foreach ($serviceOrderNotice as $uid) {
                $userInfo = User::where('uid', $uid)->find();
                if ($userInfo && is_callable($callable)) $callable($userInfo);
            }
        }
    }
}