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

namespace think\facade;

use think\Facade;

/**
 * @see \think\Validate
 * @mixin \think\Validate
 * @method static \think\Validate rule(mixed $name, mixed $rule = '') 添加字段验证规则
 * @method static void extend(string $type, callable $callback = null, string $message='') 注册扩展验证（类型）规则
 * @method static void setTypeMsg(mixed $type, string $msg = null) 设置验证规则的默认提示信息
 * @method static \think\Validate message(mixed $name, string $message = '') 设置提示信息
 * @method static \think\Validate scene(string $name) 设置验证场景
 * @method static bool hasScene(string $name) 判断是否存在某个验证场景
 * @method static \think\Validate batch(bool $batch = true) 设置批量验证
 * @method static \think\Validate only(array $fields) 指定需要验证的字段列表
 * @method static \think\Validate remove(mixed $field, mixed $rule = true) 移除某个字段的验证规则
 * @method static \think\Validate append(mixed $field, mixed $rule = null) 追加某个字段的验证规则
 * @method static bool confirm(mixed $value, mixed $rule, array $data = [], string $field = '') 验证是否和某个字段的值一致
 * @method static bool different(mixed $value, mixed $rule, array $data = []) 验证是否和某个字段的值是否不同
 * @method static bool egt(mixed $value, mixed $rule, array $data = []) 验证是否大于等于某个值
 * @method static bool gt(mixed $value, mixed $rule, array $data = []) 验证是否大于某个值
 * @method static bool elt(mixed $value, mixed $rule, array $data = []) 验证是否小于等于某个值
 * @method static bool lt(mixed $value, mixed $rule, array $data = []) 验证是否小于某个值
 * @method static bool eq(mixed $value, mixed $rule) 验证是否等于某个值
 * @method static bool must(mixed $value, mixed $rule) 必须验证
 * @method static bool is(mixed $value, mixed $rule, array $data = []) 验证字段值是否为有效格式
 * @method static bool ip(mixed $value, mixed $rule) 验证是否有效IP
 * @method static bool requireIf(mixed $value, mixed $rule) 验证某个字段等于某个值的时候必须
 * @method static bool requireCallback(mixed $value, mixed $rule,array $data) 通过回调方法验证某个字段是否必须
 * @method static bool requireWith(mixed $value, mixed $rule, array $data) 验证某个字段有值的情况下必须
 * @method static bool filter(mixed $value, mixed $rule) 使用 filter_var 方式验证
 * @method static bool in(mixed $value, mixed $rule) 验证是否在范围内
 * @method static bool notIn(mixed $value, mixed $rule) 验证是否不在范围内
 * @method static bool between(mixed $value, mixed $rule) between 验证数据
 * @method static bool notBetween(mixed $value, mixed $rule) 使用 notbetween 验证数据
 * @method static bool length(mixed $value, mixed $rule) 验证数据长度
 * @method static bool max(mixed $value, mixed $rule) 验证数据最大长度
 * @method static bool min(mixed $value, mixed $rule) 验证数据最小长度
 * @method static bool after(mixed $value, mixed $rule) 验证日期
 * @method static bool before(mixed $value, mixed $rule) 验证日期
 * @method static bool expire(mixed $value, mixed $rule) 验证有效期
 * @method static bool allowIp(mixed $value, mixed $rule) 验证 IP 许可
 * @method static bool denyIp(mixed $value, mixed $rule) 验证 IP 禁用
 * @method static bool regex(mixed $value, mixed $rule) 使用正则验证数据
 * @method static bool token(mixed $value, mixed $rule) 验证表单令牌
 * @method static bool dateFormat(mixed $value, mixed $rule) 验证时间和日期是否符合指定格式
 * @method static bool unique(mixed $value, mixed $rule, array $data = [], string $field = '') 验证是否唯一
 * @method static bool check(array $data, mixed $rules = []) 数据自动验证
 * @method static bool checkRule(mixed $data, mixed $rules = []) 数据手动验证
 * @method static mixed getError() 获取错误信息
 */
class Validate extends Facade
{
    /**
     * 始终创建新的对象实例
     * @var bool
     */
    protected static $alwaysNewInstance = true;

    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'think\Validate';
    }
}
