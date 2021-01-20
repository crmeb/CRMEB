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
 * 颜色选择器组件
 * Class ColorPicker
 *
 * @method $this disabled(bool $bool) 是否禁用
 * @method $this alpha(bool $bool) 是否支持透明度选择, 默认为false
 * @method $this colors(array $colors) colors
 * @method $this hue(bool $bool) 是否支持色彩选择, 默认为true
 * @method $this recommend(bool $bool) 是否显示推荐的颜色预设, 默认为false
 * @method $this size(string $size) 尺寸，可选值为large、small、default或者不设置
 * @method $this format(string $format) 颜色的格式，可选值为 hsl、hsv、hex、rgb.开启 alpha 时为 rgb，其它为 hex
 */
class ColorPicker extends FormComponent
{
    protected $selectComponent = true;

    protected static $propsRule = [
        'disabled' => 'bool',
        'alpha' => 'bool',
        'hue' => 'bool',
        'recommend' => 'bool',
        'size' => 'string',
        'colors' => 'array',
        'format' => 'string',
    ];

    public function createValidate()
    {
        return Iview::validateStr();
    }
}