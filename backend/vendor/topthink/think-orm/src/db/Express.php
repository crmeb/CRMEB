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

namespace think\db;

/**
 * SQL Express.
 */
class Express
{
    /**
     * 创建一个SQL运算表达式.
     *
     * @param string $type
     * @param float $value
     * @param int   $lazyTime
     *
     * @return void
     */
    public function __construct(protected string $type, protected float $step = 1, protected int $lazyTime = 0)
    {
    }

    public function getStep()
    {
        return $this->step;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLazyTime()
    {
        return $this->lazyTime;
    }

    /**
     * 获取表达式.
     *
     * @return string
     */
    public function getValue(): string
    {
        return match ($this->type) {
            '+' => ' + ' . $this->step,
            '-' => ' - ' . $this->step,
            '*' => ' * ' . $this->step,
            '/' => ' / ' . $this->step,
            default => ' + 0',
        };
    }
}
