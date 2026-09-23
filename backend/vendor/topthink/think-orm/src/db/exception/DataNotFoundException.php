<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://zjzit.cn>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\db\exception;

class DataNotFoundException extends DbException
{
    protected $table;

    /**
     * DbException constructor.
     *
     * @param string $message
     * @param string $table
     * @param array  $config
     */
    public function __construct(string $message, string $table = '', array $config = [])
    {
        $this->message = $message;
        $this->table = $table;

        $this->setData('Database Config', $config);
    }

    /**
     * 获取数据表名.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
}
