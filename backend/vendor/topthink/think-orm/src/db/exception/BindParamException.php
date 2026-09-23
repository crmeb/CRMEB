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

/**
 * PDO参数绑定异常.
 */
class BindParamException extends DbException
{
    /**
     * BindParamException constructor.
     *
     * @param string $message
     * @param array  $config
     * @param string $sql
     * @param array  $bind
     * @param int    $code
     */
    public function __construct(string $message, array $config, string $sql, array $bind, int $code = 10502)
    {
        $this->setData('Bind Param', $bind);
        parent::__construct($message, $config, $sql, $code);
    }
}
