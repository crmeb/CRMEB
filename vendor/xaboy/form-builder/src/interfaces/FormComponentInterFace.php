<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\interfaces;


/**
 * Interface FormComponentInterFace
 *
 * @package FormBuilder\interfaces
 */
interface FormComponentInterFace
{

    /**
     * 获取组件的生成规则
     *
     * @return array
     */
    public function build();
}