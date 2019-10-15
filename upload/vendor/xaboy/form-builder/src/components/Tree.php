<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;

/**
 * 树型组件
 * Class Tree
 *
 * @package FormBuilder\components
 * @method $this type(String $type) 类型，可选值为 checked、selected
 * @method $this multiple(Boolean $bool) 是否支持多选, 当`type=selected`并且`multiple=false`, 默认为false, 值为String或Number类型，其他情况为Array类型
 * @method $this showCheckbox(Boolean $bool) 是否显示多选框, 默认为false
 * @method $this emptyText(String $emptyText) 没有数据时的提示, 默认为'暂无数据'
 */
class Tree extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'tree';

    /**
     *
     */
    const TYPE_SELECTED = 'selected';
    /**
     *
     */
    const TYPE_CHECKED = 'checked';

    /**
     * @var array
     */
    protected $props = [
        'type' => self::TYPE_CHECKED,
        'data' => [],
        'multiple' => true
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'type' => 'string',
        'multiple' => 'boolean',
        'showCheckbox' => 'boolean',
        'emptyText' => 'string',
    ];

    /**
     * @param array $treeData
     * @return $this
     */
    public function data(array $treeData)
    {
        if (!is_array($this->props['data'])) $this->props['data'] = [];
        foreach ($treeData as $child) {
            $this->props['data'][] = $child instanceof TreeData
                ? $child->build()
                : $child;
        }
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
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = (string)$v;
            }
        } else {
            $value = (string)$value;
        }
        $this->value = $value;
        return $this;
    }

    public function getValidateHandler()
    {
        if ($this->props['multiple'])
            return Validate::arr();
        else
            return Validate::str();
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