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

namespace FormBuilder\UI\Iview\Components;


use FormBuilder\Driver\FormComponent;
use FormBuilder\Factory\Iview;

/**
 * 多级联动组件
 * Class Cascader
 *
 * @method $this type(string $type) 数据类型, 支持 city_area(省市区三级联动), city (省市二级联动), other (自定义)
 * @method $this disabled(bool $bool) 是否禁用选择器
 * @method $this clearable(bool $bool) 是否支持清除
 * @method $this placeholder(string $placeholder)
 * @method $this trigger(string $trigger) 次级菜单展开方式，可选值为 click 或 hover
 * @method $this changeOnSelect(bool $bool) 当此项为 true 时，点选每级菜单选项值都会发生变化, 默认为 false
 * @method $this size(string $size) 输入框大小，可选值为large和small或者不填
 * @method $this filterable(bool $bool) 是否支持搜索
 * @method $this notFoundText(string $text) 当搜索列表为空时显示的内容
 * @method $this transfer(bool $bool) /是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果
 */
class Cascader extends FormComponent
{
    /**
     * 省市区三级联动数据
     */
    const TYPE_CITY_AREA = 'city_area';

    /**
     * 省市二级联动数据
     */
    const TYPE_CITY = 'city';

    /**
     * 自定义数据
     */
    const TYPE_OTHER = 'other';


    protected $defaultValue = [];

    protected $selectComponent = true;

    protected $defaultProps = [
        'type' => self::TYPE_OTHER,
        'data' => []
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'type' => 'string',
        'disabled' => 'bool',
        'clearable' => 'bool',
        'changeOnSelect' => 'bool',
        'filterable' => 'bool',
        'transfer' => 'bool',
        'placeholder' => 'string',
        'trigger' => 'string',
        'size' => 'string',
        'notFoundText' => 'string',
    ];

    /**
     * @param array $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = (array)$value;
        return $this;
    }

    /**
     * 可选项的数据源
     * 例如:{
     *    "value":"北京市", "label":"北京市", "children":[{
     *        "value":"东城区", "label":"东城区"
     *    }]
     *  }
     *
     * @param array|callable $data
     * @return $this
     */
    public function data($data)
    {
        $is_callable = is_callable($data);
        if (!is_array($data) && !$is_callable) return $this;

        $this->props['data'] = $is_callable ? $data($this) : $data;
        return $this;
    }

    /**
     * @param $var
     * @return $this
     */
    public function jsData($var)
    {
        $this->props['data'] = 'js.' . (string)$var;
        return $this;
    }

    public function createValidate()
    {
        return Iview::validateArr();
    }

}