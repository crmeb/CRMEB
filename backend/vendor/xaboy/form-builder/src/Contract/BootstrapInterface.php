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

namespace FormBuilder\Contract;


use FormBuilder\Form;

interface BootstrapInterface
{
    /**
     * 初始化
     *
     * @param Form $form
     * @return void
     */
    public function init(Form $form);
}