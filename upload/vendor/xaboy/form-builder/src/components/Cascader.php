<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;
use FormBuilder\Helper;

/**
 * 多级联动组件
 * Class Cascader
 *
 * @package FormBuilder\components
 * @method $this type(String $type) 数据类型, 支持 city_area(省市区三级联动), city (省市二级联动), other (自定义)
 * @method $this disabled(Boolean $bool) 是否禁用选择器
 * @method $this clearable(Boolean $bool) 是否支持清除
 * @method $this placeholder(String $placeholder)
 * @method $this trigger(String $trigger) 次级菜单展开方式，可选值为 click 或 hover
 * @method $this changeOnSelect(Boolean $bool) 当此项为 true 时，点选每级菜单选项值都会发生变化, 默认为 false
 * @method $this size(String $size) 输入框大小，可选值为large和small或者不填
 * @method $this filterable(Boolean $bool) 是否支持搜索
 * @method $this notFoundText(String $text) 当搜索列表为空时显示的内容
 * @method $this transfer(Boolean $bool) /是否将弹层放置于 body 内，在 Tabs、带有 fixed 的 Table 列内使用时，建议添加此属性，它将不受父级样式影响，从而达到更好的效果
 */
class Cascader extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'cascader';

    /**
     *
     */
    const TYPE_CITY_AREA = 'city_area';
    /**
     *
     */
    const TYPE_CITY = 'city';
    /**
     *
     */
    const TYPE_OTHER = 'other';

    /**
     * @var array
     */
    protected $props = [
        'type' => self::TYPE_OTHER,
        'data' => []
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'type' => 'string',
        'disabled' => 'boolean',
        'clearable' => 'boolean',
        'changeOnSelect' => 'boolean',
        'filterable' => 'boolean',
        'transfer' => 'boolean',
        'placeholder' => 'string',
        'trigger' => 'string',
        'size' => 'string',
        'notFoundText' => 'string',
    ];

    protected function init()
    {
        $this->placeholder($this->getPlaceHolder());
    }

    /**
     * @param array $value
     * @return $this
     */
    public function value($value)
    {
        if (!is_array($value)) $value = [];
        $this->value = $value;
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
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        if (!is_array($this->props['data'])) $this->props['data'] = [];
        $this->props['data'] = array_merge($this->props['data'], $data);
        return $this;
    }

    /**
     * @param $var
     * @return $this
     */
    public function jsData($var)
    {
        $this->props['data'] = 'js.' . $var;
        return $this;
    }

    /**
     * 获取组件类型
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->props['type'];
    }

    /**
     * @return Validate
     */
    public function getValidateHandler()
    {
        return Validate::arr();
    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'type' => $this->name,
            'field' => $this->field,
            'title' => $this->title,
            'value' => $this->value,
            'props' => (object)$this->props,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }
}