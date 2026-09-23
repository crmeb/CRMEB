<?php
declare(strict_types=1);

namespace app\model\driver\printer;

use crmeb\basic\BaseModel;

/**
 * 小票设备提交记录，不保存凭据和小票中的个人信息。
 */
class PrinterTask extends BaseModel
{
    /**
     * 表名
     * @var string
     */
    protected $name = 'printer_task';

    /**
     * 提交状态和租约时间由服务显式维护
     * @var bool
     */
    protected $autoWriteTimestamp = false;
}
