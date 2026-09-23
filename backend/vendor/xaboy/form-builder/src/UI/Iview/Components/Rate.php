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
 * 评分组件
 * Class Rate
 *
 * @method $this count(int $star) star 总数, 默认为 5
 * @method $this allowHalf(bool $bool) 是否允许半选, 默认为 false
 * @method $this disabled(bool $bool) 是否只读，无法进行交互, 默认为
 * @method $this showText(bool $bool) 是否显示提示文字, 默认为 false
 * @method $this clearable(bool $bool) 是否可以取消选择, 默认为 false
 *
 */
class Rate extends FormComponent
{
    protected $selectComponent = true;

    protected static $propsRule = [
        'count' => 'float',
        'allowHalf' => 'bool',
        'disabled' => 'bool',
        'showText' => 'bool',
        'clearable' => 'bool',
    ];

    public function createValidate()
    {
        return Iview::validateNum();
    }

}