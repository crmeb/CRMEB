<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\exception;

/**
 * 数据验证异常
 */
class ValidateException extends \RuntimeException
{
    public function __construct(protected $error, protected $key = '')
    {
        $this->message = is_array($error) ? json_encode($error) : $error;
    }

    /**
     * 获取验证错误信息
     * @access public
     * @return array|string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取验证错误字段
     * @access public
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

}
