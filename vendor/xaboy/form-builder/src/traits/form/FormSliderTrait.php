<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Slider;

/**
 * Class FormSliderTrait
 *
 * @package FormBuilder\traits\form
 */
trait FormSliderTrait
{
    /**
     * 滑块组件
     *
     * @param     $field
     * @param     $title
     * @param int $value
     * @return Slider
     */
    public static function slider($field, $title, $value = 0)
    {
        return new Slider($field, $title, $value);
    }

    /**
     * 区间选择
     *
     * @param     $field
     * @param     $title
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