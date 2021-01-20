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
 * Iview 表单生成类
 *
 * Class IviewFormHandle
 * @package FormBuilder\Factory
 */
abstract class IviewFormHandle extends FormHandle
{

    protected $version = 3;

    public function ui()
    {
        return $this->version == 4 ? 'iview4' : 'iview';
    }
}