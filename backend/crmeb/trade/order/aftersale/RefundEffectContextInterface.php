<?php
declare(strict_types=1);

namespace crmeb\trade\order\aftersale;

/**
 * 产品退款本地效果上下文。
 *
 * 产品处理器必须通过 once() 执行本地副作用，以获得统一的幂等审计能力。
 */
interface RefundEffectContextInterface
{
    /**
     * 在当前退款单内最多成功执行一次本地效果。
     *
     * @param string $effectType 效果类型，例如 stock_restore/card_revoke
     * @param string $effectKey 产品业务稳定键，例如 cart_id/booking_no
     * @param array $payload 审计快照，不得包含密钥或令牌
     * @param callable $callback 必须是本地数据库动作，不得调用支付渠道
     * @return void
     */
    public function once(string $effectType, string $effectKey, array $payload, callable $callback): void;
}
