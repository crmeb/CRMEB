<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;

/**
 * 评分组件
 * Class Rate
 *
 * @package FormBuilder\components
 * @method $this count(int $star) star 总数, 默认为 5
 * @method $this allowHalf(Boolean $bool) 是否允许半选, 默认为 false
 * @method $this disabled(Boolean $bool) 是否只读，无法进行交互, 默认为
 * @method $this showText(Boolean $bool) 是否显示提示文字, 默认为 false
 * @method $this clearable(Boolean $bool) 是否可以取消选择, 默认为 false
 *
 */
class Rate extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'rate';

    /**
     * @var array
     */
    protected static $propsRule = [
        'count' => 'float',
        'allowHalf' => 'boolean',
        'disabled' => 'boolean',
        'showText' => 'boolean',
        'clearable' => 'boolean',
    ];

    public function getValidateHandler()
    {
        return Validate::num();
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
            'value' => (float)$this->value,
            'props' => (object)$this->props,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }
}