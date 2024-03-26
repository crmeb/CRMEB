<?php

namespace app\dao\wechat;

use app\dao\BaseDao;
use app\model\wechat\RoutineScheme;

class RoutineSchemeDao extends BaseDao
{
    protected function setModel(): string
    {
        return RoutineScheme::class;
    }
}