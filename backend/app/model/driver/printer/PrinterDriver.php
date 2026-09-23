<?php
declare(strict_types=1);

namespace app\model\driver\printer;

use app\model\driver\email\EmailDriver;

class PrinterDriver extends EmailDriver
{
    const DRIVER_TYPE = 'printer';

    protected $insert = ['driver_type' => self::DRIVER_TYPE];
    protected $update = ['driver_type' => self::DRIVER_TYPE];

    public function scopeDriverType($query)
    {
        $query->where('driver_type', self::DRIVER_TYPE)->where('owner_plugin_id', '<>', '');
    }
}
