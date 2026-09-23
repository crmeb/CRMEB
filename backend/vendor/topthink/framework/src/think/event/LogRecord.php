<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace think\event;

use DateTimeImmutable;

/**
 * LogRecord事件类
 */
class LogRecord
{
    /** @var string */
    public string $type;

    /** @var string|array */
    public $message;

    /** @var DateTimeImmutable */
    public DateTimeImmutable $time;

    public function __construct($type, $message)
    {
        $this->type    = $type;
        $this->message = $message;
        $this->time    = new DateTimeImmutable();
    }
}
