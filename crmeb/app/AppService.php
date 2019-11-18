<?php

namespace app;

use crmeb\services\SystemConfigService;
use crmeb\services\GroupDataService;
use crmeb\utils\Json;
use think\facade\Db;
use think\Service;

class AppService extends Service
{

    public $bind = [
        'json' => Json::class,
        'sysConfig' => SystemConfigService::class,
        'sysGroupData' => GroupDataService::class
    ];

    public function boot()
    {

    }
}
