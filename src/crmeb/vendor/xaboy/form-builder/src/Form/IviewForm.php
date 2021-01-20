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

namespace FormBuilder\Form;


use FormBuilder\UI\Iview\Bootstrap;

class IviewForm extends \FormBuilder\Form
{
    public function __construct($action = '', $rule = [], $config = [])
    {
        parent::__construct(new Bootstrap(), $action, $rule, $config);
    }
}