<?php

namespace Songshenzong\Support;

use Illuminate\Support\Facades\Facade;

/**
 * Class StringsFacade
 *
 * @package Songshenzong\Support
 */
class StringsFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Strings';
    }
}
