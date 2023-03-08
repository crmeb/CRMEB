<?php

namespace app\dao\system\crontab;

use app\dao\BaseDao;
use app\model\system\crontab\SystemCrontab;

class SystemCrontabDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemCrontab::class;
    }
}