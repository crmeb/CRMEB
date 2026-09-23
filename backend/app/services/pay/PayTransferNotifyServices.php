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

use app\services\statistic\CapitalFlowServices;
use app\services\user\UserExtractServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use app\dao\user\UserExtractDao;
use crmeb\services\pay\Pay;
use crmeb\services\pay\PayChannelConfig;
use think\facade\Log;

class PayTransferNotifyServices
{
    /**
     * 提现
     * @param string|null $order_id 订单id
     * @return bool
     */
    public function wechatTx(?string $order_id = null, ?string $trade_no = null, ?string $state = null, ?string $fail_reason = null, array $other = [])
    {
        try {
            if (!$order_id || !$trade_no) return false;
            if (!isset($other['transfer_amount']) || empty($other['appid']) || empty($other['openid'])) return $this->sync($order_id);
            if (!in_array($state, ['ACCEPTED', 'PROCESSING', 'WAIT_USER_CONFIRM', 'TRANSFERING', 'CANCELING', 'SUCCESS', 'FAIL', 'CANCELLED'], true)) return false;
            $records = app()->make(PayRecordServices::class);
            $handler = function (array $userExtractInfo) use ($order_id, $trade_no, $state, $fail_reason, $other, $records) {
                $record = $records->find($order_id);
                $context = $record['context'] ?? [];
                $config = PayChannelConfig::get($record['channel_key'] ?? 'weixin_v3');
                if (($other['out_bill_no'] ?? '') !== $order_id || ($other['transfer_bill_no'] ?? '') !== $trade_no
                    || (!empty($userExtractInfo['transfer_bill_no']) && $userExtractInfo['transfer_bill_no'] !== $trade_no)
                    || !is_int($other['transfer_amount']) || $other['transfer_amount'] !== (int)bcmul(
                        bcsub((string)$userExtractInfo['extract_price'], (string)$userExtractInfo['extract_fee'], 2), '100', 0)
                    || ($other['mchid'] ?? '') !== ($context['mch_id'] ?? $config['pay_weixin_mchid'] ?? '')) {
                    throw new AdminException('转账通知与原单号、金额或商户不一致');
                }
                foreach (['app_id' => 'appid', 'openid' => 'openid'] as $key => $field) {
                    if (!empty($context[$key]) && $context[$key] !== ($other[$field] ?? '')) throw new AdminException('原转账应用或收款人不一致');
                }
                if ((int)$userExtractInfo['status'] === 1) return $state === 'SUCCESS';
                if ((int)$userExtractInfo['status'] === -1) return in_array($state, ['FAIL', 'CANCELLED'], true);
                $userExtractServices = app()->make(UserExtractServices::class);
                $update = ['state' => $state, 'fail_reason' => $fail_reason ?? '', 'transfer_bill_no' => $trade_no];
                if (!empty($other['package_info'])) $update['package_info'] = $other['package_info'];
                $userExtractServices->update($userExtractInfo['id'], $update);
                if ($state === 'SUCCESS') {
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
                $userExtractServices->update($userExtractInfo['id'], ['status' => 1]);
                //触发提现成功事件（消息发送已移至 UserExtractListener）
                event('UserExtract', [
                    $userExtractInfo['uid'],
                    $userExtractInfo,
                    $extractNumber,
                    0,
                    strtolower($user['user_type']),
                    $user['nickname'],
                    $user['phone'] ?? ''
                ]);
                event('UserRevenue', [$userExtractInfo['uid'], $order_id, $extractNumber, 1]);
                } elseif (in_array($state, ['FAIL', 'CANCELLED'], true)) {
                    $userExtractServices->changeFail($userExtractInfo['id'], $userExtractInfo, '提现失败：' . ($fail_reason ?: $state));
                }
                return true;
            };
            if (!in_array($state, ['SUCCESS', 'FAIL', 'CANCELLED'], true)) {
                return app()->make(UserExtractServices::class)->transaction(function () use ($order_id, $handler) {
                    $source = app()->make(UserExtractDao::class)->getDb()->where('wechat_order_id|out_bill_no', $order_id)->lock(true)->find();
                    return $source ? $handler($source->toArray()) : false;
                });
            }
            return $records->complete($order_id, 'user_extract', $trade_no, $handler);
        } catch (\Throwable $e) {
            Log::error('[PayTransfer] 转账通知待重试 order_no=' . $order_id . ' type=' . get_class($e));
            return false;
        }
    }

    /**
     * 按原商户单号查询转账终态，历史响应缺少商户号时绑定本次请求的商户。
     * @param string $orderNo 原转账单号
     * @return bool
     */
    public function sync(string $orderNo): bool
    {
        $record = app()->make(PayRecordServices::class)->find($orderNo);
        $context = $record['context'] ?? [];
        $config = PayChannelConfig::get($record['channel_key'] ?? 'weixin_v3');
        if (!empty($context['mch_id']) && $context['mch_id'] !== ($config['pay_weixin_mchid'] ?? '')) {
            throw new AdminException('转账商户已变化，请恢复原配置');
        }
        $pay = app()->make(Pay::class, [($context['mode'] ?? '') === 'transfer_v2' ? 'wechat_pay' : 'v3_wechat_pay']);
        if (($context['mode'] ?? '') === 'transfer_v2') {
            $detail = $pay->queryEnterpriseTransfer($orderNo, $context);
            if (!isset($detail['payment_amount'], $detail['openid']) || empty($detail['detail_id'])) throw new AdminException('企业付款查单缺少核验字段');
            $result = ['out_bill_no' => $orderNo, 'transfer_bill_no' => $detail['detail_id'],
                'state' => ($detail['status'] ?? '') === 'FAILED' ? 'FAIL' : ($detail['status'] ?? 'PROCESSING'),
                'transfer_amount' => ctype_digit((string)$detail['payment_amount']) ? (int)$detail['payment_amount'] : null,
                'appid' => $context['app_id'], 'openid' => $detail['openid'], 'mchid' => $context['mch_id'], 'fail_reason' => $detail['reason'] ?? ''];
        } elseif (($context['mode'] ?? '') === 'transfer_batch') {
            $detail = $pay->queryBatchTransfer($orderNo);
            if (($detail['out_batch_no'] ?? '') !== $orderNo || ($detail['out_detail_no'] ?? '') !== $orderNo
                || empty($detail['detail_id']) || !isset($detail['transfer_amount'], $detail['openid'])) {
                throw new AdminException('批次转账查单缺少核验字段或单号不一致');
            }
            $result = ['out_bill_no' => $orderNo, 'transfer_bill_no' => $detail['detail_id'],
                'state' => ($detail['detail_status'] ?? '') === 'FAIL' ? 'FAIL' : ($detail['detail_status'] ?? 'PROCESSING'),
                'appid' => $context['app_id'], 'openid' => $detail['openid'], 'transfer_amount' => $detail['transfer_amount'],
                'mchid' => $detail['mchid'] ?? $context['mch_id'], 'fail_reason' => $detail['fail_reason'] ?? ''];
        } else {
            $result = $pay->queryTransferBills($orderNo);
        }
        if (!isset($result['transfer_amount']) || !is_int($result['transfer_amount']) || $result['transfer_amount'] <= 0
            || empty($result['appid']) || empty($result['openid']) || empty($result['transfer_bill_no'])) {
            throw new AdminException('转账查单缺少有效的核验字段');
        }
        $result['mchid'] = $result['mchid'] ?? $config['pay_weixin_mchid'] ?? '';
        return $this->wechatTx($orderNo, $result['transfer_bill_no'] ?? '', $result['state'] ?? '', $result['fail_reason'] ?? '', $result);
    }

    /**
     * 红包
     * @param string|null $order_id 订单id
     * @return bool
     */
    public function wechatHb(?string $order_id = null, ?string $trade_no = null, ?string $state = null, ?string $fail_reason = null, array $other = [])
    {
        try {
            // 通过 MarketingServiceProxy 事件代理查询抽奖红包转账记录（避免直接引用 modules 层）
            $_results = event('MarketingServiceProxy', [
                'module' => 'lottery',
                'service' => 'LuckLotteryRecordServices',
                'action' => 'getOne',
                'args' => [['transfer_bill_no' => $trade_no]],
            ]);
            $info = proxy_event_result($_results, null);
            if (!$info) {
                return false;
            }
            if (($info['state'] ?? '') === 'SUCCESS') return $state === 'SUCCESS';
            $info->state = $state;
            if ($fail_reason) {
                $info->fail_reason = $fail_reason;
            }
            $info->save();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
