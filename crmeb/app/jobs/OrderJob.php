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

namespace app\jobs;

use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\combination\StoreCombinationServices;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\kefu\service\StoreServiceServices;
use app\services\message\notice\SmsService;
use app\services\order\OutStoreOrderServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderEconomizeServices;
use app\services\order\StoreOrderServices;
use app\services\product\product\StoreProductServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserLabelRelationServices;
use app\services\user\UserLevelServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\basic\BaseJobs;
use crmeb\services\app\WechatService;
use crmeb\services\workerman\ChannelService;
use crmeb\traits\QueueTrait;
use think\exception\ValidateException;
use think\facade\Log;

/**
 * 订单消息队列
 * Class OrderJob
 * @package crmeb\jobs
 */
class OrderJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 执行订单支付成功发送消息
     * @param $order
     * @return bool
     */
    public function doJob($order)
    {
        //计算商品节省金额
        try {
            $this->setEconomizeMoney($order);
        } catch (\Throwable $e) {
            Log::error('计算节省金额,失败原因:' . $e->getMessage());
        }
        //更新用户支付订单数量
        try {
            $this->setUserPayCountAndPromoter($order);
        } catch (\Throwable $e) {
            Log::error('更新用户订单数失败,失败原因:' . $e->getMessage());
        }
        //增加用户标签
        try {
            $this->setUserLabel($order);
        } catch (\Throwable $e) {
            Log::error('用户标签添加失败,失败原因:' . $e->getMessage());
        }
        try {
            if (in_array($order['is_channel'], [0, 2])) {//公众号发送模板消息
                $this->sendOrderPaySuccessCustomerService($order, 1);
            } else if (in_array($order['is_channel'], [1, 2])) {//小程序发送模板消息
                $this->sendOrderPaySuccessCustomerService($order, 0);
            }
        } catch (\Exception $e) {
            throw new ValidateException('发送客服消息,短信消息失败,失败原因:' . $e->getMessage());
        }


        //打印小票
//        $switch = sys_config('pay_success_printing_switch') ? true : false;
//        if ($switch) {
//            try {
//                /** @var StoreOrderServices $orderServices */
//                $orderServices = app()->make(StoreOrderServices::class);
//                $orderServices->orderPrint($order, $order['cart_id']);
//            } catch (\Throwable $e) {
//                Log::error('打印小票发生错误,错误原因:' . $e->getMessage());
//            }
//        }

        //检测会员等级
        try {
            /** @var UserLevelServices $levelServices */
            $levelServices = app()->make(UserLevelServices::class);
            $levelServices->detection((int)$order['uid']);
        } catch (\Throwable $e) {
            Log::error('会员等级升级失败,失败原因:' . $e->getMessage());
        }
        //向后台发送新订单消息
        try {
            ChannelService::instance()->send('NEW_ORDER', ['order_id' => $order['order_id']]);
        } catch (\Throwable $e) {
            Log::error('向后台发送新订单消息失败,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 设置用户购买次数和检测时候成为推广人
     * @param $order
     */
    public function setUserPayCountAndPromoter($order)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->get($order['uid']);
        if ($userInfo) {
            $userInfo->pay_count = $userInfo->pay_count + 1;
            if (!$userInfo->is_promoter) {
                /** @var StoreOrderServices $orderServices */
                $orderServices = app()->make(StoreOrderServices::class);
                $price = $orderServices->sum(['paid' => 1, 'refund_status' => 0, 'uid' => $userInfo['uid']], 'pay_price');
                $status = is_brokerage_statu($price);
                if ($status) {
                    $userInfo->is_promoter = 1;
                }
            }
            $userInfo->save();
        }
    }

    /**
     * 设置用户购买的标签
     * @param $order
     */
    public function setUserLabel($order)
    {
        /** @var StoreOrderCartInfoServices $cartInfoServices */
        $cartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $productIds = $cartInfoServices->getCartColunm(['oid' => $order['id']], 'product_id', '');
        /** @var StoreProductServices $productServices */
        $productServices = app()->make(StoreProductServices::class);
        $label = $productServices->getColumn([['id', 'in', $productIds]], 'label_id');
        $labelIds = array_unique(explode(',', implode(',', $label)));
        /** @var UserLabelRelationServices $labelServices */
        $labelServices = app()->make(UserLabelRelationServices::class);
        $where = [
            ['label_id', 'in', $labelIds],
            ['uid', '=', $order['uid']]
        ];
        $data = [];
        $userLabel = $labelServices->getColumn($where, 'label_id');
        foreach ($labelIds as $item) {
            if (!in_array($item, $userLabel)) {
                $data[] = ['uid' => $order['uid'], 'label_id' => $item];
            }
        }
        $re = true;
        if ($data) {
            $re = $labelServices->saveAll($data);
        }
        return $re;
    }


    /**
     * 订单支付成功后给客服发送客服消息
     * @param $order
     * @param int $type 1 公众号 0 小程序
     * @return string
     */
    public function sendOrderPaySuccessCustomerService($order, $type = 0)
    {
        /** @var StoreServiceServices $services */
        $services = app()->make(StoreServiceServices::class);
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $serviceOrderNotice = $services->getStoreServiceOrderNotice();
        if (count($serviceOrderNotice)) {
            /** @var StoreProductServices $services */
            $services = app()->make(StoreProductServices::class);
            /** @var StoreSeckillServices $seckillServices */
            $seckillServices = app()->make(StoreSeckillServices::class);
            /** @var StoreCombinationServices $pinkServices */
            $pinkServices = app()->make(StoreCombinationServices::class);
            /** @var StoreBargainServices $bargainServices */
            $bargainServices = app()->make(StoreBargainServices::class);
            /** @var StoreOrderCartInfoServices $cartInfoServices */
            $cartInfoServices = app()->make(StoreOrderCartInfoServices::class);
            foreach ($serviceOrderNotice as $item) {
                $userInfo = $wechatUserServices->getOne(['uid' => $item['uid'], 'user_type' => 'wechat']);
                if ($userInfo) {
                    $userInfo = $userInfo->toArray();
                    if ($userInfo['subscribe'] && $userInfo['openid']) {
                        if ($item['customer']) {
                            // 统计管理开启  推送图文消息
                            $head = '订单提醒 订单号：' . $order['order_id'];
                            $url = sys_config('site_url') . '/pages/admin/orderDetail/index?id=' . $order['order_id'];
                            $description = '';
                            $image = sys_config('site_logo');
                            if (isset($order['seckill_id']) && $order['seckill_id'] > 0) {
                                $description .= '秒杀商品：' . $seckillServices->value(['id' => $order['seckill_id']], 'title');
                                $image = $seckillServices->value(['id' => $order['seckill_id']], 'image');
                            } else if (isset($order['combination_id']) && $order['combination_id'] > 0) {
                                $description .= '拼团商品：' . $pinkServices->value(['id' => $order['combination_id']], 'title');
                                $image = $pinkServices->value(['id' => $order['combination_id']], 'image');
                            } else if (isset($order['bargain_id']) && $order['bargain_id'] > 0) {
                                $title = $bargainServices->value(['id' => $order['bargain_id']], 'title');
                                $description .= '砍价商品：' . $title;
                                $image = $bargainServices->value(['id' => $order['bargain_id']], 'image');
                            } else {
                                $productIds = $cartInfoServices->getCartIdsProduct($order['id']);
                                $storeProduct = $services->getProductArray([['id', 'in', $productIds]], 'image,store_name', 'id');
                                if (count($storeProduct)) {
                                    foreach ($storeProduct as $value) {
                                        $description .= $value['store_name'] . '  ';
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
                        } else {
                            // 推送文字消息
                            $head = "客服提醒：亲,您有一个新订单 \r\n订单单号:{$order['order_id']}\r\n支付金额：￥{$order['pay_price']}\r\n备注信息：{$order['mark']}\r\n订单来源：小程序";
                            if ($type) $head = "客服提醒：亲,您有一个新订单 \r\n订单单号:{$order['order_id']}\r\n支付金额：￥{$order['pay_price']}\r\n备注信息：{$order['mark']}\r\n订单来源：公众号";
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
     * 计算节约金额
     * @param $order
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setEconomizeMoney($order)
    {
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        /** @var StoreOrderCartInfoServices $cartInfoService */
        $cartInfoService = app()->make(StoreOrderCartInfoServices::class);
        /** @var StoreCouponUserServices $couponService */
        $couponService = app()->make(StoreCouponUserServices::class);
        /** @var StoreOrderEconomizeServices $economizeService */
        $economizeService = app()->make(StoreOrderEconomizeServices::class);
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        $getOne = $economizeService->getOne(['order_id' => $order['order_id']]);
        if ($getOne) return false;
        //看是否是会员
        $userInfo = $userService->getUserInfo($order['uid']);
        if ($userInfo && $userInfo['is_money_level'] > 0) {
            $save = [];
            $save['order_type'] = 1;
            $save['add_time'] = time();
            $save['pay_price'] = $order['pay_price'];
            $save['order_id'] = $order['order_id'];
            $save['uid'] = $order['uid'];
            //计算商品节约金额
            $isOpenVipPrice = $memberCardService->isOpenMemberCard('vip_price');
            if ($isOpenVipPrice) {
                $cartInfo = $cartInfoService->getOrderCartInfo($order['id']);
                $memberPrice = 0.00;
                if ($cartInfo) {
                    foreach ($cartInfo as $k => $item) {
                        foreach ($item as $value) {
                            if (isset($value['price_type']) && $value['price_type'] == 'member') $memberPrice += bcmul($value['vip_truePrice'], $value['cart_num'] ?: 1, 2);
                        }
                    }
                }
                $save['member_price'] = $memberPrice;
            }
            //计算邮费节约金额
            $isOpenExpress = $memberCardService->isOpenMemberCard('express');
            if ($isOpenExpress) {
                $expressTotalMoney = bcdiv($order['total_postage'], bcdiv($isOpenExpress, 100, 2), 2);
                $save['postage_price'] = bcsub($expressTotalMoney, $order['total_postage'], 2);
            }

            //计算会员券节省金额
            if ($order['coupon_id']) {
                $couponMoney = $couponService->get($order['coupon_id'], ['*'], ['issue']);
                if ($couponMoney && $couponMoney['receive_type']) {
                    $save['coupon_price'] = $couponMoney['coupon_price'];
                }
            }
            return $economizeService->addEconomize($save);
        }
        return false;

    }
}
