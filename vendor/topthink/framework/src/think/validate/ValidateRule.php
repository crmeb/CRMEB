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

namespace think\validate;

/**
 * Class ValidateRule
 * @package think\validate
 * @method ValidateRule confirm(mixed $rule, string $msg = '') static 验证是否和某个字段的值一致
 * @method ValidateRule different(mixed $rule, string $msg = '') static 验证是否和某个字段的值是否不同
 * @method ValidateRule egt(mixed $rule, string $msg = '') static 验证是否大于等于某个值
 * @method ValidateRule gt(mixed $rule, string $msg = '') static 验证是否大于某个值
 * @method ValidateRule elt(mixed $rule, string $msg = '') static 验证是否小于等于某个值
 * @method ValidateRule lt(mixed $rule, string $msg = '') static 验证是否小于某个值
 * @method ValidateRule eg(mixed $rule, string $msg = '') static 验证是否等于某个值
 * @method ValidateRule in(mixed $rule, string $msg = '') static 验证是否在范围内
 * @method ValidateRule notIn(mixed $rule, string $msg = '') static 验证是否不在某个范围
 * @method ValidateRule between(mixed $rule, string $msg = '') static 验证是否在某个区间
 * @method ValidateRule notBetween(mixed $rule, string $msg = '') static 验证是否不在某个区间
 * @method ValidateRule length(mixed $rule, string $msg = '') static 验证数据长度
 * @method ValidateRule max(mixed $rule, string $msg = '') static 验证数据最大长度
 * @method ValidateRule min(mixed $rule, string $msg = '') static 验证数据最小长度
 * @method ValidateRule after(mixed $rule, string $msg = '') static 验证日期
 * @method ValidateRule before(mixed $rule, string $msg = '') static 验证日期
 * @method ValidateRule expire(mixed $rule, string $msg = '') static 验证有效期
 * @method ValidateRule allowIp(mixed $rule, string $msg = '') static 验证IP许可
 * @method ValidateRule denyIp(mixed $rule, string $msg = '') static 验证IP禁用
 * @method ValidateRule regex(mixed $rule, string $msg = '') static 使用正则验证数据
 * @method ValidateRule token(mixed $rule='__token__', string $msg = '') static 验证表单令牌
 * @method ValidateRule is(mixed $rule, string $msg = '') static 验证字段值是否为有效格式
 * @method ValidateRule isRequire(mixed $rule = null, string $msg = '') static 验证字段必须
 * @method ValidateRule isNumber(mixed $rule = null, string $msg = '') static 验证字段值是否为数字
 * @method ValidateRule isArray(mixed $rule = null, string $msg = '') static 验证字段值是否为数组
 * @method ValidateRule isInteger(mixed $rule = null, string $msg = '') static 验证字段值是否为整形
 * @method ValidateRule isFloat(mixed $rule = null, string $msg = '') static 验证字段值是否为浮点数
 * @method ValidateRule isMobile(mixed $rule = null, string $msg = '') static 验证字段值是否为手机
 * @method ValidateRule isIdCard(mixed $rule = null, string $msg = '') static 验证字段值是否为身份证号码
 * @method ValidateRule isChs(mixed $rule = null, string $msg = '') static 验证字段值是否为中文
 * @method ValidateRule isChsDash(mixed $rule = null, string $msg = '') static 验证字段值是否为中文字母及下划线
 * @method ValidateRule isChsAlpha(mixed $rule = null, string $msg = '') static 验证字段值是否为中文和字母
 * @method ValidateRule isChsAlphaNum(mixed $rule = null, string $msg = '') static 验证字段值是否为中文字母和数字
 * @method ValidateRule isDate(mixed $rule = null, string $msg = '') static 验证字段值是否为有效格式
 * @method ValidateRule isBool(mixed $rule = null, string $msg = '') static 验证字段值是否为布尔值
 * @method ValidateRule isAlpha(mixed $rule = null, string $msg = '') static 验证字段值是否为字母
 * @method ValidateRule isAlphaDash(mixed $rule = null, string $msg = '') static 验证字段值是否为字母和下划线
 * @method ValidateRule isAlphaNum(mixed $rule = null, string $msg = '') static 验证字段值是否为字母和数字
 * @method ValidateRule isAccepted(mixed $rule = null, string $msg = '') static 验证字段值是否为yes, on, 或是 1
 * @method ValidateRule isEmail(mixed $rule = null, string $msg = '') static 验证字段值是否为有效邮箱格式
 * @method ValidateRule isUrl(mixed $rule = null, string $msg = '') static 验证字段值是否为有效URL地址
 * @method ValidateRule activeUrl(mixed $rule, string $msg = '') static 验证是否为合格的域名或者IP
 * @method ValidateRule ip(mixed $rule, string $msg = '') static 验证是否有效IP
 * @method ValidateRule fileExt(mixed $rule, string $msg = '') static 验证文件后缀
 * @method ValidateRule fileMime(mixed $rule, string $msg = '') static 验证文件类型
 * @method ValidateRule fileSize(mixed $rule, string $msg = '') static 验证文件大小
 * @method ValidateRule image(mixed $rule, string $msg = '') static 验证图像文件
 * @method ValidateRule method(mixed $rule, string $msg = '') static 验证请求类型
 * @method ValidateRule dateFormat(mixed $rule, string $msg = '') static 验证时间和日期是否符合指定格式
 * @method ValidateRule unique(mixed $rule, string $msg = '') static 验证是否唯一
 * @method ValidateRule behavior(mixed $rule, string $msg = '') static 使用行为类验证
 * @method ValidateRule filter(mixed $rule, string $msg = '') static 使用filter_var方式验证
 * @method ValidateRule requireIf(mixed $rule, string $msg = '') static 验证某个字段等于某个值的时候必须
 * @method ValidateRule requireCallback(mixed $rule, string $msg = '') static 通过回调方法验证某个字段是否必须
 * @method ValidateRule requireWith(mixed $rule, string $msg = '') static 验证某个字段有值的情况下必须
 * @method ValidateRule must(mixed $rule = null, string $msg = '') static 必须验证
 */
class ValidateRule
{
    // 验证字段的名称
    protected $title;

    // 当前验证规则
    protected $rule = [];

    // 验证提示信息
    protected $message = [];

    /**
     * 添加验证因子
     * @access protected
     * @param  string    $name  验证名称
     * @param  mixed     $rule  验证规则
     * @param  string    $msg   提示信息
     * @return $this
     */
    protected function addItem(string $name, $rule = null, string $msg = '')
    {
        if ($rule || 0 === $rule) {
            $this->rule[$name] = $rule;
        } else {
            $this->rule[] = $name;
        }

        $this->message[] = $msg;

        return $this;
    }

    /**
     * 获取验证规则
     * @access public
     * @return array
     */
    public function getRule(): array
    {
        return $this->rule;
    }

    /**
     * 获取验证字段名称
     * @access public
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title ?: '';
    }

    /**
     * 获取验证提示
     * @access public
     * @return array
     */
    public function getMsg(): array
    {
        return $this->message;
    }

    /**
     * 设置验证字段名称
     * @access public
     * @return $this
     */
    public function title(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function __call($method, $args)
    {
        if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
        }

        array_unshift($args, lcfirst($method));

        return call_user_func_array([$this, 'addItem'], $args);
    }

    public static function __callStatic($method, $args)
    {
        $rule = new static();

        if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
        }

        array_unshift($args, lcfirst($method));

        return call_user_func_array([$rule, 'addItem'], $args);
    }
}
