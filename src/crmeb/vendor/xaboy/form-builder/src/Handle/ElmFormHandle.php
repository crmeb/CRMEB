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

namespace FormBuilder\Handle;


use FormBuilder\FormHandle;

/**
 * ElementUI 表单生成类
 * Class ElmFormHandle
 * @package FormBuilder\Factory
 */
abstract class ElmFormHandle extends FormHandle
{

    public function ui()
    {
        return 'elm';
    }
}