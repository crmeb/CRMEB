<?php
/**
 * PHP表单生成器
 *
 * @package  FormBuilder
 * @author   xaboy <xaboy2005@qq.com>
 * @version  2.0
 * @license  MIT
 * @link     https://github.com/xaboy/form-builder
 * @document http://php.form-create.com
 */

namespace FormBuilder\Factory;

use FormBuilder\Exception\FormBuilderException;
use FormBuilder\Form;
use FormBuilder\UI\Iview\Components\Button;
use FormBuilder\UI\Iview\Components\Option;
use FormBuilder\UI\Iview\Components\Poptip;
use FormBuilder\UI\Iview\Components\Tooltip;
use FormBuilder\UI\Iview\Config;
use FormBuilder\UI\Iview\Traits\CascaderFactoryTrait;
use FormBuilder\UI\Iview\Traits\CheckBoxFactoryTrait;
use FormBuilder\UI\Iview\Traits\ColorPickerFactoryTrait;
use FormBuilder\UI\Iview\Traits\DatePickerFactoryTrait;
use FormBuilder\UI\Iview\Traits\FrameFactoryTrait;
use FormBuilder\UI\Iview\Traits\FormStyleFactoryTrait;
use FormBuilder\UI\Iview\Traits\GroupFactoryTrait;
use FormBuilder\UI\Iview\Traits\HiddenFactoryTrait;
use FormBuilder\UI\Iview\Traits\InputFactoryTrait;
use FormBuilder\UI\Iview\Traits\InputNumberFactoryTrait;
use FormBuilder\UI\Iview\Traits\RadioFactoryTrait;
use FormBuilder\UI\Iview\Traits\RateFactoryTrait;
use FormBuilder\UI\Iview\Traits\SelectFactoryTrait;
use FormBuilder\UI\Iview\Traits\SliderFactoryTrait;
use FormBuilder\UI\Iview\Traits\SwitchesFactoryTrait;
use FormBuilder\UI\Iview\Traits\TimePickerFactoryTrait;
use FormBuilder\UI\Iview\Traits\TreeFactoryTrait;
use FormBuilder\UI\Iview\Traits\UploadFactoryTrait;
use FormBuilder\UI\Iview\Traits\ValidateFactoryTrait;

abstract class Iview extends Base
{
    use CascaderFactoryTrait;
    use CheckBoxFactoryTrait;
    use ColorPickerFactoryTrait;
    use DatePickerFactoryTrait;
    use FrameFactoryTrait;
    use HiddenFactoryTrait;
    use InputNumberFactoryTrait;
    use InputFactoryTrait;
    use RadioFactoryTrait;
    use RateFactoryTrait;
    use SliderFactoryTrait;
    use SelectFactoryTrait;
    use FormStyleFactoryTrait;
    use SwitchesFactoryTrait;
    use TimePickerFactoryTrait;
    use TreeFactoryTrait;
    use UploadFactoryTrait;
    use ValidateFactoryTrait;
    use GroupFactoryTrait;

    /**
     * 获取选择类组件 option 类
     *
     * @param string|number $value
     * @param string $label
     * @param bool $disabled
     * @return Option
     */
    public static function option($value, $label = '', $disabled = false)
    {
        return new Option($value, $label, $disabled);
    }

    /**
     * @param array $rule
     * @return \FormBuilder\UI\Iview\Style\FormStyle
     * @author xaboy
     * @day 2020/5/22
     */
    public static function formStyle(array $rule = [])
    {
        return self::style($rule);
    }

    /**
     * 全局配置
     *
     * @param array $config
     * @return Config
     */
    public static function config(array $config = [])
    {
        return new Config($config);
    }

    /**
     * 组件提示消息配置 Poptip
     *
     * @return Poptip
     */
    public static function poptip()
    {
        return new Poptip();
    }

    /**
     * 组件提示消息配置 Tooltip
     *
     * @return Tooltip
     */
    public static function tooltip()
    {
        return new Tooltip();
    }

    /**
     * 按钮组件
     *
     * @return Button
     */
    public static function button()
    {
        return new Button();
    }


    /**
     * 创建表单
     *
     * @param string $action
     * @param array $rule
     * @param array $config
     * @return Form
     * @throws FormBuilderException
     */
    public static function createForm($action = '', $rule = [], $config = [])
    {
        return Form::iview($action, $rule, $config);
    }


    /**
     * 创建表单 v4版本
     *
     * @param string $action
     * @param array $rule
     * @param array $config
     * @return Form
     * @throws FormBuilderException
     */
    public static function createFormV4($action = '', $rule = [], $config = [])
    {
        return Form::iview4($action, $rule, $config);
    }

    /**
     * 组件分组
     *
     * @param array $children
     * @return \FormBuilder\Driver\CustomComponent
     */
    public static function item($children = [])
    {
        return self::createComponent('row')->children($children);
    }
}