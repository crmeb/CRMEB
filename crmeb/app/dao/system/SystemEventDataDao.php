<?php

namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\SystemEventData;

class SystemEventDataDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return SystemEventData::class;
    }
}