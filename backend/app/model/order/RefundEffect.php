<?php
declare(strict_types=1);

namespace app\model\order;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 退款本地副作用幂等日志模型
 */
class RefundEffect extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';
    protected $name = 'refund_effect';

    public function getPayloadAttr($value): array
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

    public function setPayloadAttr($value): string
    {
        return json_encode(is_array($value) ? $value : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

