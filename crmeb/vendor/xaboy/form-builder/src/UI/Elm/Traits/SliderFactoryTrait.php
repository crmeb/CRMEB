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

namespace FormBuilder\UI\Elm\Traits;


use FormBuilder\UI\Elm\Components\Slider;

trait SliderFactoryTrait
{
    /**
     * 滑块组件
     *
     * @param string $field
     * @param string $title
     * @param int|array $value
     * @return Slider
     */
    public static function slider($field, $title, $value = 0)
    {
        $slider = new Slider($field, $title, $value);
        if (is_array($value)) $slider->range(true);
        return $slider;
    }

    /**
     * 区间选择
     *
     * @param string $field
     * @param string $title
     * @param int $start
     * @param int $end
     * @return Slider
     */
    public static function sliderRange($field, $title, $start = 0, $end = 0)
    {
        $slider = self::slider($field, $title, [(int)$start, (int)$end]);
        $slider->range(true);
        return $slider;
    }
}