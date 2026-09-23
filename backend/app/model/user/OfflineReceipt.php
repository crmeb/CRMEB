<?php
declare(strict_types=1);

namespace app\model\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 线下收银记录模型
 * 对应表 eb_offline_receipt，承载扫码收款单（自填金额、无商品履约）
 */
class OfflineReceipt extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'offline_receipt';

    protected $insert = ['add_time'];

    protected $type = [
        'total_price' => 'float',
        'paid_price' => 'float',
        'refund_price' => 'float',
        'coupon_price' => 'float',
        'integral_price' => 'float',
        'give_integral' => 'float',
        'give_brokerage' => 'float',
        'give_brokerage_price' => 'float',
    ];

    protected function setAddTimeAttr()
    {
        return time();
    }

    public function searchUidAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('uid', $value);
        } else {
            $query->where('uid', $value);
        }
    }

    public function searchOrderIdAttr($query, $value)
    {
        $query->where('order_id', $value);
    }

    public function searchOrderNoAttr($query, $value)
    {
        $query->where('order_no', $value);
    }

    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    public function searchTypeAttr($query, $value)
    {
        if (is_array($value)) {
            $query->where('type', 'in', $value);
        } else {
            $query->where('type', $value);
        }
    }

    public function searchTradeTypeAttr($query, $value)
    {
        $query->where('trade_type', $value);
    }

    public function searchAddTimeAttr($query, $value)
    {
        if (empty($value) && $value !== '0') return;
        if (is_string($value)) {
            $query->whereTime('add_time', $value);
        } elseif (is_array($value) && count($value) == 2) {
            $startTime = $value[0];
            $endTime = $value[1] ?? 0;
            if ($startTime && $endTime) {
                $query->whereBetween('add_time', [$startTime, $endTime]);
            } elseif ($startTime) {
                $query->whereTime('add_time', '>=', $startTime);
            } elseif ($endTime) {
                $query->whereTime('add_time', '<', $endTime);
            }
        }
    }

    public function searchPayTimeAttr($query, $value)
    {
        if (empty($value) && $value !== '0') return;
        if (is_string($value)) {
            $query->whereTime('pay_time', $value);
        } elseif (is_array($value) && count($value) == 2) {
            $startTime = $value[0];
            $endTime = $value[1] ?? 0;
            if ($startTime && $endTime) {
                $query->whereBetween('pay_time', [$startTime, $endTime]);
            } elseif ($startTime) {
                $query->whereTime('pay_time', '>=', $startTime);
            } elseif ($endTime) {
                $query->whereTime('pay_time', '<', $endTime);
            }
        }
    }

    public function searchPaidAttr($query, $value)
    {
        $query->where('paid', $value);
    }

    public function searchRefundStatusAttr($query, $value)
    {
        $query->where('refund_status', $value);
    }
}
