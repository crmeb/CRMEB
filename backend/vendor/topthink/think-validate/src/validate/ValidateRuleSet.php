<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2023 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\validate;

use Closure;

/**
 * Class ValidateRuleSet
 * @package think\validate
 */
class ValidateRuleSet
{
    /**
     * 构造方法
     * @access public
     */
    public function __construct(protected array|Closure $rules = [], protected array $message = [])
    {
    }

    /**
     * 添加验证因子
     * @access public
     * @param  array    $rules  验证因子
     * @param  array    $message  错误信息
     * @return static
     */
    public static function rules(array|Closure $rules, array $message = [])
    {
        return new static($rules, $message);
    }

    /**
     * 设置错误信息
     * @access public
     * @param  array    $message  错误信息
     * @return $this
     */
    public function message(array $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * 获取验证因子
     * @access public
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * 获取错误信息
     * @access public
     * @return array
     */
    public function getMessage(): array
    {
        return $this->message;
    }
}
