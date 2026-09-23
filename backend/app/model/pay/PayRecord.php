<?php
declare(strict_types=1);

namespace app\model\pay;

use crmeb\basic\BaseModel;

/**
 * 微信支付原始身份及后置任务记录，对应 eb_pay_record。
 */
class PayRecord extends BaseModel
{
    /** @var string 主键 */
    protected $pk = 'id';
    /** @var string 表名 */
    protected $name = 'pay_record';
    /** @var array JSON 快照 */
    protected $type = ['context' => 'json', 'effects' => 'json', 'refund' => 'json'];
}
