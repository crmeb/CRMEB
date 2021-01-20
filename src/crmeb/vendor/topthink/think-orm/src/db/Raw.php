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

namespace think\db;

/**
 * SQL Raw
 */
class Raw
{
    /**
     * 查询表达式
     *
     * @var string
     */
    protected $value;

    /**
     * 参数绑定
     *
     * @var array
     */
    protected $bind = [];

    /**
     * 创建一个查询表达式
     *
     * @param  string  $value
     * @param  array   $bind
     * @return void
     */
    public function __construct(string $value, array $bind = [])
    {
        $this->value = $value;
        $this->bind  = $bind;
    }

    /**
     * 获取表达式
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * 获取参数绑定
     *
     * @return string
     */
    public function getBind(): array
    {
        return $this->bind;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
