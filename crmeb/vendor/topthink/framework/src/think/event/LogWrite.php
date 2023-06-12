<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\event;

/**
 * LogWrite事件类
 */
class LogWrite
{
    /** @var string */
    public $channel;

    /** @var array */
    public $log;

    public function __construct($channel, $log)
    {
        $this->channel = $channel;
        $this->log     = $log;
    }
}
