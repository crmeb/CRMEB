<?php
declare(strict_types=1);

namespace app\dao\pay;

use app\model\pay\PayRecord;
use crmeb\basic\BaseDao;

/**
 * 支付记录数据访问。
 */
class PayRecordDao extends BaseDao
{
    /**
     * 设置模型。
     * @return string
     */
    protected function setModel(): string
    {
        return PayRecord::class;
    }

    /**
     * 按订单号读取并可加行锁。
     * @param string $orderNo 订单号
     * @param bool $lock 是否加锁
     * @return mixed
     */
    public function byOrder(string $orderNo, bool $lock = false)
    {
        return $this->getModel()->where('order_no', $orderNo)->lock($lock)->find();
    }

}
