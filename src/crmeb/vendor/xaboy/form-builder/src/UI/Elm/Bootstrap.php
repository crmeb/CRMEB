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

namespace FormBuilder\UI\Elm;

use FormBuilder\Contract\BootstrapInterface;
use FormBuilder\Form;

class Bootstrap implements BootstrapInterface
{

    public function init(Form $form)
    {
        $dependScript = $form->getDependScript();

        array_splice($dependScript, 2, 0, [
            '<link href="https://unpkg.com/element-ui@2.12.0/lib/theme-chalk/index.css" rel="stylesheet">',
            '<script src="https://unpkg.com/element-ui@2.12.0/lib/index.js"></script>',
            '<script src="https://unpkg.com/@form-create/element-ui@1.0.15/dist/form-create.min.js"></script>',
        ]);

        $form->setDependScript($dependScript);
    }
}