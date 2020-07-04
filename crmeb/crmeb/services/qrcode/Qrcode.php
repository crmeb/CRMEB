<?php

namespace crmeb\services\qrcode;

use crmeb\services\apply\Apply;
use crmeb\traits\LogicTrait;

/**
 * Class Qrcode
 * @package crmeb\services\qrcode
 * @method $this wechatApp()
 * 调用示例：Qrcode::instance()->wechatApp()->test();
 */
class Qrcode extends Apply
{
    use LogicTrait;

}