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

namespace FormBuilder\UI\Iview;

use FormBuilder\Contract\BootstrapInterface;
use FormBuilder\Form;

class Bootstrap implements BootstrapInterface
{

    protected $version;

    /**
     * Bootstrap constructor.
     * @param int $version
     */
    public function __construct($version = 3)
    {
        $this->version = $version;
    }

    public function init(Form $form)
    {
        $dependScript = $form->getDependScript();

        if ($this->version != 4) {
            array_splice($dependScript, 2, 0, [
                '<link href="https://unpkg.com/iview@3.4.2/dist/styles/iview.css" rel="stylesheet">',
                '<script src="https://unpkg.com/iview@3.4.2/dist/iview.min.js"></script>',
                '<script src="https://unpkg.com/@form-create/iview@1.0.15/dist/form-create.min.js"></script>',
            ]);
        } else {
            array_splice($dependScript, 2, 0, [
                '<link href="https://unpkg.com/view-design@4.0.2/dist/styles/iview.css" rel="stylesheet">',
                '<script src="https://unpkg.com/view-design@4.0.2/dist/iview.min.js"></script>',
                '<script src="https://unpkg.com/@form-create/iview4@1.0.15/dist/form-create.min.js"></script>',
            ]);
        }


        $form->setDependScript($dependScript);
    }
}