<?php
declare(strict_types=1);

namespace app\model\driver\express;

use crmeb\basic\BaseModel;

/** 电子面单业务设置（与驱动账号配置分离）。 */
class ExpressSetting extends BaseModel
{
    protected $name = 'express_setting';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;
}
