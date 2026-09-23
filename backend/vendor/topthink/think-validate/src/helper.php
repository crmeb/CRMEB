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

//------------------------
// ThinkPHP 助手函数
//-------------------------

use think\Validate;
use think\validate\ValidateRuleSet;

if (!function_exists('validate')) {
    /**
     * 生成验证对象
     * @param string|array $validate      验证器类名或者验证规则数组
     * @param array        $message       错误提示信息
     * @param bool         $batch         是否批量验证
     * @param bool         $failException 是否抛出异常
     * @return Validate
     */
    function validate($validate = '', array $message = [], bool $batch = false, bool $failException = true): Validate
    {
        if (is_array($validate) || '' === $validate) {
            $v = new Validate();
            if (is_array($validate)) {
                $v->rule($validate);
            }
        } else {
            if (str_contains($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }

            if (class_exists($validate)) {
                $v = new $validate();

                if (!empty($scene)) {
                    $v->scene($scene);
                }
            } else {
                $v = new Validate();
            }
        }
        return $v->message($message)->batch($batch)->failException($failException);
    }
}

if (!function_exists('rules')) {
    /**
     * 定义ValidateRuleSet规则集合
     * @param array    $rules     验证因子集
     * @return ValidateRuleSet
     */
    function rules(array $rules): ValidateRuleSet
    {
        return ValidateRuleSet::rules($rules);
    }
}

