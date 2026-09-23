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

namespace app\services\pay;

use crmeb\basic\BaseService;
use app\services\user\OfflineReceiptServices;
use app\services\user\MemberOrderServices;
use app\services\order\OrderSuccessServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use crmeb\exceptions\ApiException;

/**
 * 余额支付
 * Class YuePayServices
 * @package app\services\pay
 */
class YuePayServices extends BaseService
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
            throw new ApiException('订单不存在');
        }
        if ($orderInfo['paid']) {
            throw new ApiException('订单已支付');
        }
        $type = 'pay_product';
        if (isset($orderInfo['member_type'])) {
            $type = 'pay_member';
        } elseif (($orderInfo['type'] ?? 0) == 3) {
            // 线下收银订单（eb_offline_receipt）
            $type = 'offline_scan';
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userInfo = $services->getUserInfo($uid);
        if ($userInfo['now_money'] < $orderInfo['pay_price']) {
            return ['status' => 'pay_deficiency', 'msg' => '余额不足' . floatval($orderInfo['pay_price'])];
        }
        $orderNo = (string)($orderInfo['order_id'] ?? $orderInfo['order_no']);
        $kind = $type === 'pay_product' ? 'product' : ($type === 'pay_member' ? 'member' : 'offline_scan');
        $records = app()->make(PayRecordServices::class);
        $records->complete($orderNo, $kind, '', function (array $source) use ($records, $orderNo, $orderInfo, $userInfo, $type, $kind) {
            if ((int)($source[$kind === 'product' ? 'pay_status' : 'paid'] ?? 0) !== 0) return true;
            $records->assertChannel($orderNo, PayServices::YUE_PAY);
            if (in_array($source['status_code'] ?? '', ['canceled', 'deleted', 'refunded'], true)) throw new ApiException('订单已取消');
            $amount = (string)$source[$kind === 'offline_scan' ? 'paid_price' : 'pay_price'];
            if (bccomp($amount, (string)$orderInfo['pay_price'], 2) !== 0) throw new ApiException('订单金额已变化');
            $orderInfo = array_replace($orderInfo, $source);
            /** @var UserMoneyServices $userMoneyServices */
            $userMoneyServices = app()->make(UserMoneyServices::class);
            // 计算扣减后的余额
            $now_money = bcsub((string)$userInfo['now_money'], (string)$orderInfo['pay_price'], 2);
            $number = $orderInfo['pay_price'];
            $res = true;
            switch ($type) {
                case 'pay_product'://商品余额
                    // income 方法已包含：增加流水记录 + 更新余额
                    $res = $res && $userMoneyServices->moneyIncome('pay_product', $userInfo['uid'], $number, $orderInfo['id']);
                    /** @var OrderSuccessServices $orderServices */
                    $orderServices = app()->make(OrderSuccessServices::class);
                    $res = $res && $orderServices->paySuccess($orderInfo, PayServices::YUE_PAY);//余额支付成功
                    break;
                case 'pay_member'://会员卡支付
                    $res = $res && $userMoneyServices->moneyIncome('pay_member', $userInfo['uid'], $number, $orderInfo['id']);
                    /** @var MemberOrderServices $MemberOrderServices */
                    $MemberOrderServices = app()->make(MemberOrderServices::class);
                    $res = $res && $MemberOrderServices->paySuccess($orderInfo, PayServices::YUE_PAY);//余额支付成功
                    break;
                case 'offline_scan'://线下收银支付
                    $res = $res && $userMoneyServices->moneyIncome('offline_scan', $userInfo['uid'], $number, $orderInfo['id']);
                    /** @var OfflineReceiptServices $offlineReceiptServices */
                    $offlineReceiptServices = app()->make(OfflineReceiptServices::class);
                    $res = $res && $offlineReceiptServices->paySuccess($orderInfo, PayServices::YUE_PAY);//余额支付成功
                    break;
            }
            if (!$res) {
                throw new ApiException('余额支付失败');
            }
            return true;
        });
        return ['status' => true];
    }
}
