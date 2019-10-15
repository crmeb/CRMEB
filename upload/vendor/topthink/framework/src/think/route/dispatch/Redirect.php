<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\route\dispatch;

use think\Response;
use think\route\Dispatch;

/**
 * Redirect Dispatcher
 */
class Redirect extends Dispatch
{
    public function exec()
    {
        return Response::create($this->dispatch, 'redirect')->code($this->code);
    }
}
