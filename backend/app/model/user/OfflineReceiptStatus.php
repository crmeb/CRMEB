<?php
declare(strict_types=1);

namespace app\model\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 线下收银状态变更模型
 * 对应表 eb_offline_receipt_status，记录收款单的状态变更历史
 */
class OfflineReceiptStatus extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'offline_receipt_status';

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    public function searchOrderIdAttr($query, $value)
    {
        $query->where('order_id', $value);
    }
}
