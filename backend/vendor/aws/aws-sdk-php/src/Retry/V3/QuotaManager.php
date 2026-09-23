<?php
namespace Aws\Retry\V3;

/**
 * Retry-quota manager for the AWS_NEW_RETRIES_2026 opt-in path. Implements
 * the throttling-aware token-bucket model described in the specs.
 *
 * @internal
 */
class QuotaManager
{
    private int $availableCapacity;
    private readonly int $maxCapacity;
    private readonly int $noRetryIncrement;
    private readonly int $retryCost;
    private readonly int $throttlingRetryCost;

    public function __construct(array $config = [])
    {
        $initialTokens = $config['initial_retry_tokens'] ?? 500;
        $this->noRetryIncrement = $config['no_retry_increment'] ?? 1;
        $this->retryCost = $config['retry_cost'] ?? 14;
        $this->throttlingRetryCost = $config['throttling_retry_cost'] ?? 5;
        $this->maxCapacity = $initialTokens;
        $this->availableCapacity = $initialTokens;
    }

    /**
     * Attempts to acquire retry quota.
     *
     * @param bool $isThrottling Whether the error is a throttling error.
     *
     * @return int|false The capacity used, or false if insufficient quota.
     */
    public function acquireRetryQuota(bool $isThrottling): int|false
    {
        $cost = $isThrottling ? $this->throttlingRetryCost : $this->retryCost;

        if ($cost > $this->availableCapacity) {
            return false;
        }

        $this->availableCapacity -= $cost;

        return $cost;
    }

    /**
     * Releases quota back to the pool.
     *
     * @param int|null $capacityUsed The capacity to release. If null, uses
     *                               the no_retry_increment value.
     */
    public function releaseQuota(?int $capacityUsed): void
    {
        $amount = $capacityUsed ?? $this->noRetryIncrement;

        $this->availableCapacity = min(
            $this->availableCapacity + $amount,
            $this->maxCapacity
        );
    }

    public function getAvailableCapacity(): int
    {
        return $this->availableCapacity;
    }
}
