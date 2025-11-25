<?php

namespace app\services\pay;

use app\services\activity\lottery\LuckLotteryRecordServices;
use app\services\statistic\CapitalFlowServices;
use app\services\user\UserExtractServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;

class PayTransferNotifyServices
{
    /**
     * 提现
     * @param string|null $order_id 订单id
     * @return bool
     */
    public function wechatTx(string $order_id = null, string $trade_no = null, string $state = null, string $fail_reason = null)
    {
        try {
            $userExtractServices = app()->make(UserExtractServices::class);
            $userExtractInfo = $userExtractServices->getOne(['transfer_bill_no' => $trade_no]);
            if (!$userExtractInfo) {
                return true;
            }
            $infoData['state'] = $state;
            if ($fail_reason) {
                $infoData['fail_reason'] = $fail_reason;
            }
            $userExtractServices->update($userExtractInfo['id'], $infoData);
            if ($state == 'SUCCESS') {
                /** @var UserServices $userService */
                $userService = app()->make(UserServices::class);
                $user = $userService->getUserInfo($userExtractInfo['uid']);
                $extractNumber = bcsub($userExtractInfo['extract_price'], $userExtractInfo['extract_fee'], 2);
                /** @var CapitalFlowServices $capitalFlowServices */
                $capitalFlowServices = app()->make(CapitalFlowServices::class);
                $capitalFlowServices->setFlow([
                    'order_id' => $order_id,
                    'uid' => $userExtractInfo['uid'],
                    'price' => bcmul('-1', $extractNumber, 2),
                    'pay_type' => $userExtractInfo['extract_type'],
                    'nickname' => $user['nickname'],
                    'phone' => $user['phone']
                ], 'extract');

                event('NoticeListener', [['uid' => $userExtractInfo['uid'], 'userType' => strtolower($user['user_type']), 'extractNumber' => $extractNumber, 'nickname' => $user['nickname']], 'user_extract']);

                //自定义通知-用户提现成功
                $userExtract['nickname'] = $user['nickname'];
                $userExtract['phone'] = $user['phone'];
                $userExtract['time'] = date('Y-m-d H:i:s');
                $userExtract['price'] = $extractNumber;
                event('CustomNoticeListener', [$userExtract['uid'], $userExtract, 'extract_success']);

                //自定义事件-用户提现成功
                event('CustomEventListener', ['admin_extract_success', [
                    'uid' => $userExtract['uid'],
                    'price' => $extractNumber,
                    'pay_type' => $userExtract['extract_type'],
                    'nickname' => $user['nickname'],
                    'phone' => $user['phone'],
                    'success_time' => date('Y-m-d H:i:s')
                ]]);
            } else {
                $userExtractServices->changeFail($userExtractInfo['id'], $userExtractInfo, '提现失败，原因：超时未领取');
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 红包
     * @param string|null $order_id 订单id
     * @return bool
     */
    public function wechatHb(string $order_id = null, string $trade_no = null, string $state = null, string $fail_reason = null)
    {
        try {
            $info = app()->make(LuckLotteryRecordServices::class)->getOne(['transfer_bill_no' => $trade_no]);
            if (!$info) {
                return true;
            }
            $info->state = $state;
            if ($fail_reason) {
                $info->fail_reason = $fail_reason;
            }
            $info->save();
        } catch (\Exception $e) {
            return false;
        }
    }
}
