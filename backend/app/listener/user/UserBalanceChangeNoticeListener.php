<?php
declare(strict_types=1);

namespace app\listener\user;

use app\services\message\Notice;
use app\services\user\UserMoneyServices;

/**
 * 用户余额变更通知监听器
 * 监听 UserBalanceChange 事件，通过 Notice API 发送余额变更通知。
 */
class UserBalanceChangeNoticeListener
{
    public function handle(array $event): void
    {
        [$uid, $type, $number, $balance, $mark] = $event + [1 => '', 2 => 0, 3 => 0, 4 => ''];

        try {
            $uid = (int)$uid;
            if (!$uid) return;

            // type 落库值为英文标识（如 pay_product_refund），站内信模板 {type} 需展示中文标题
            $typeCode = trim(is_string($type) ? $type : (string)$type);
            $typeTitle = app()->make(UserMoneyServices::class)->getTypeTitle($typeCode);
            // 未命中映射时不要回退到含金额的 mark，否则会与模板中的 {extract_number} 重复
            if ($typeTitle === '' || $typeTitle === $typeCode) {
                $typeTitle = '余额变动';
            }

            // 场景：余额充值、提现、退款回账等引起账户余额变动时，通知用户
            // 扁平业务数据：各渠道模板直接用业务变量名，新增渠道无需改监听器
            Notice::to($uid)->sendMsg('user_balance_change', '余额变更', [
                'extract_number' => (string)($number ?: 0), // 变动金额（正为增加，负为扣减）
                'type'           => $typeTitle,             // 变动类型中文（如商品退款）
                'mark'           => $mark,                  // 变动备注标识
                'link'           => '/pages/users/user_bill/index', // 账单明细页链接
            ]);
        } catch (\Throwable $e) {
        }
    }
}
