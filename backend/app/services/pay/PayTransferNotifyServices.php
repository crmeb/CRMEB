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

class PayTransferNotifyServices
{
    /**
     * 红包
     * 提现转账通知（wechatTx/sync）已随佣金提现功能下线，由分销插件自行处理。
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
