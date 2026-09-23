<?php
declare(strict_types=1);

namespace app\model\order;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 统一退款单模型
 */
class RefundOrder extends BaseModel
{
    use ModelTrait;

    public const STATE_PENDING = 'pending';
    public const STATE_SUBMITTING = 'submitting';
    public const STATE_CHANNEL_PROCESSING = 'channel_processing';
    public const STATE_UNKNOWN = 'unknown';
    public const STATE_CHANNEL_SUCCEEDED = 'channel_succeeded';
    public const STATE_APPLYING = 'applying';
    public const STATE_LOCAL_FAILED = 'local_failed';
    public const STATE_RETRYABLE_FAILED = 'retryable_failed';
    public const STATE_MANUAL_REVIEW = 'manual_review';
    public const STATE_COMPLETED = 'completed';

    public const TRANSITIONS = [
        self::STATE_PENDING => [self::STATE_SUBMITTING, self::STATE_CHANNEL_SUCCEEDED, self::STATE_MANUAL_REVIEW],
        self::STATE_SUBMITTING => [self::STATE_CHANNEL_PROCESSING, self::STATE_CHANNEL_SUCCEEDED, self::STATE_UNKNOWN, self::STATE_RETRYABLE_FAILED, self::STATE_MANUAL_REVIEW],
        self::STATE_CHANNEL_PROCESSING => [self::STATE_CHANNEL_SUCCEEDED, self::STATE_RETRYABLE_FAILED, self::STATE_MANUAL_REVIEW],
        self::STATE_UNKNOWN => [self::STATE_CHANNEL_PROCESSING, self::STATE_CHANNEL_SUCCEEDED, self::STATE_RETRYABLE_FAILED, self::STATE_MANUAL_REVIEW],
        self::STATE_CHANNEL_SUCCEEDED => [self::STATE_APPLYING, self::STATE_LOCAL_FAILED],
        self::STATE_APPLYING => [self::STATE_COMPLETED, self::STATE_LOCAL_FAILED],
        self::STATE_LOCAL_FAILED => [self::STATE_APPLYING, self::STATE_MANUAL_REVIEW],
        self::STATE_RETRYABLE_FAILED => [self::STATE_SUBMITTING, self::STATE_MANUAL_REVIEW],
        self::STATE_MANUAL_REVIEW => [],
        self::STATE_COMPLETED => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    protected $pk = 'id';
    protected $name = 'refund_order';

    public function getChannelResponseAttr($value): array
    {
        return $this->decodeJson($value);
    }

    public function setChannelResponseAttr($value): string
    {
        return json_encode(is_array($value) ? $value : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getContextAttr($value): array
    {
        return $this->decodeJson($value);
    }

    public function setContextAttr($value): string
    {
        return json_encode(is_array($value) ? $value : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function decodeJson($value): array
    {
        if (is_object($value) && method_exists($value, 'value')) {
            $value = $value->value();
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = is_string($value) ? json_decode($value, true) : null;
        return is_array($decoded) ? $decoded : [];
    }
}
