<?php
declare(strict_types=1);

namespace app\dao\driver\express;

use app\model\driver\express\ExpressSetting;
use crmeb\basic\BaseDao;

class ExpressSettingDao extends BaseDao
{
    protected function setModel(): string
    {
        return ExpressSetting::class;
    }
}
