<?php

namespace app\services\system;

use app\dao\system\SystemEventDataDao;
use app\services\BaseServices;

class SystemEventDataServices extends BaseServices
{
    public function __construct(SystemEventDataDao $dao)
    {
        $this->dao = $dao;
    }
}