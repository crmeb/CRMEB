<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

//------------------------
// ThinkORM 助手函数
//-------------------------

use think\db\BaseQuery as Query;
use think\db\Express;
use think\db\Raw;
use think\facade\Db;

if (!function_exists('db')) {
    function db(string $name, string|array|null $connect = null): Query
    {
        if ($connect) {
            return Db::connect($connect)->name($name);
        }
        return Db::name($name);
    }
}

if (!function_exists('raw')) {
    function raw(string $value, array $bind = []): Raw
    {
        return new Raw($value, $bind);
    }
}

if (!function_exists('inc')) {
    function inc(float|int $step = 1, int $lazyTime = 0): Express
    {
        return new Express('+', $step, $lazyTime);
    }
}

if (!function_exists('dec')) {
    function dec(float|int $step = 1, int $lazyTime = 0): Express
    {
        return new Express('-', $step, $lazyTime);
    }
}
