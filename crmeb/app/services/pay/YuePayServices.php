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

namespace app\services\pay;

use app\services\BaseServices;
use app\services\order\OtherOrderServices;
use app\services\order\StoreOrderSuccessServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use crmeb\exceptions\ApiException;

/**
 * 余额支付
 * Class YuePayServices
 * @package app\services\pay
 */
class YuePayServices extends BaseServices
{

    /**
     * 订单余额支付
     * @param $order_id
     * @param $uid
     * @return bool
     */
    public function yueOrderPay(array $orderInfo, $uid)
    {
        if (!$orderInfo) {
            throw new ApiException(410173);
        }
        if ($orderInfo['paid']) {
            throw new ApiException(410174);
        }
        $type = 'pay_product';
        if (isset($orderInfo['member_type'])) {
            $type = 'pay_member';
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userInfo = $services->getUserInfo($uid);
        if ($userInfo['now_money'] < $orderInfo['pay_price']) {
            return ['status' => 'pay_deficiency', 'msg' => '余额不足' . floatval($orderInfo['pay_price'])];
        }

        $this->transaction(function () use ($services, $orderInfo, $userInfo, $type) {
            $res = false !== $services->bcDec($userInfo['uid'], 'now_money', $orderInfo['pay_price'], 'uid');
            switch ($type) {
                case 'pay_product'://商品余额
                    //写入余额记录
                    $now_money = bcsub((string)$userInfo['now_money'], (string)$orderInfo['pay_price'], 2);
                    $number = $orderInfo['pay_price'];
                    /** @var UserMoneyServices $userMoneyServices */
                    $userMoneyServices = app()->make(UserMoneyServices::class);
                    $res = $res && $userMoneyServices->income('pay_product', $userInfo['uid'], $number, $now_money, $orderInfo['id']);

                    /** @var StoreOrderSuccessServices $orderServices */
                    $orderServices = app()->make(StoreOrderSuccessServices::class);
                    $res = $res && $orderServices->paySuccess($orderInfo, PayServices::YUE_PAY);//余额支付成功
                    break;
                case 'pay_member'://会员卡支付
                    /** @var OtherOrderServices $OtherOrderServices */
                    $OtherOrderServices = app()->make(OtherOrderServices::class);
                    $res = $res && $OtherOrderServices->paySuccess($orderInfo, PayServices::YUE_PAY);//余额支付成功
                    break;
            }
            if (!$res) {
                throw new ApiException(410279);
            }
        });
        return ['status' => true];
    }
}
