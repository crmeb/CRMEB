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
 * 颜色选择器组件
 * Class ColorPicker
 *
 * @package FormBuilder\components
 * @method $this disabled(Boolean $bool) 是否禁用
 * @method $this alpha(Boolean $bool) 是否支持透明度选择, 默认为false
 * @method $this hue(Boolean $bool) 是否支持色彩选择, 默认为true
 * @method $this recommend(Boolean $bool) 是否显示推荐的颜色预设, 默认为false
 * @method $this size(String $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this format(String $format) 颜色的格式，可选值为 hsl、hsv、hex、rgb    String    开启 alpha 时为 rgb，其它为 hex
 */
class ColorPicker extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'colorPicker';

    /**
     * @var array
     */
    protected $props = [
        'colors' => []
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'disabled' => 'boolean',
        'alpha' => 'boolean',
        'hue' => 'boolean',
        'recommend' => 'boolean',
        'size' => 'string',
        'format' => 'string',
    ];

    /**
     * 自定义颜色预设
     *
     * @param $colors
     * @return $this
     */
    public function colors($colors)
    {
        $colors = Helper::toType($colors, 'array');
        $this->props['colors'] = array_merge($this->props['colors'], $colors);
        return $this;
    }

    public function getValidateHandler()
    {
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