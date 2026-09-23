<?php

namespace think\captcha\facade;

use think\Facade;

/**
 * Class Captcha
 * @package think\captcha\facade
 * @mixin \think\captcha\Captcha
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \think\captcha\Captcha::class;
    }
}
