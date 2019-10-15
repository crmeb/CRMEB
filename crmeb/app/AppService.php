<?php

namespace app;

use crmeb\utils\Json;
use think\facade\Db;
use think\Service;

class AppService extends Service
{

    public $bind = [
        'json' => Json::class
    ];

    public function boot()
    {

    }
}
