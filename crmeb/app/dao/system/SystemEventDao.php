<?php

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\SystemEvent;

class SystemEventDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemEvent::class;
    }
}