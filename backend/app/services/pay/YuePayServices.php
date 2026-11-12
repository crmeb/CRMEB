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
        switch ($type) {
            case 'pay_product':
                app()->make(OrderSuccessServices::class)->paySuccess($orderInfo, PayServices::YUE_PAY);
                break;
            case 'pay_member':
                app()->make(MemberOrderServices::class)->paySuccess($orderInfo, PayServices::YUE_PAY);
                break;
            case 'offline_scan':
                app()->make(OfflineReceiptServices::class)->paySuccess($orderInfo, PayServices::YUE_PAY);
                break;
        }
        return ['status' => true];
    }
}
