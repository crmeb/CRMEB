<?php

namespace app\dao\system\timer;

use app\dao\BaseDao;
use app\model\system\timer\SystemTimer;

class SystemTimerDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemTimer::class;
    }
}