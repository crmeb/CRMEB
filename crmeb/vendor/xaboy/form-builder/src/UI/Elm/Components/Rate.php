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

namespace FormBuilder\UI\Elm\Components;


use FormBuilder\Driver\FormComponent;
use FormBuilder\Factory\Elm;

/**
 * 评分组件
 * Class Rate
 *
 * @method $this max(float $max) 最大分值, 默认值: 5
 * @method $this disabled(bool $disabled) 是否为只读, 默认值: false
 * @method $this allowHalf(bool $allowHalf) 是否允许半选, 默认值: false
 * @method $this lowThreshold(float $lowThreshold) 低分和中等分数的界限值，值本身被划分在低分中, 默认值: 2
 * @method $this highThreshold(float $highThreshold) 高分和中等分数的界限值，值本身被划分在高分中, 默认值: 4
 * @method $this colors(array $colors) icon 的颜色。若传入数组，共有 3 个元素，为 3 个分段所对应的颜色；若传入对象，可自定义分段，键名为分段的界限值，键值为对应的颜色, 默认值: ['#F7BA2A', '#F7BA2A', '#F7BA2A']
 * @method $this voidColor(string $voidColor) 未选中 icon 的颜色, 默认值: #C6D1DE
 * @method $this disabledVoidColor(string $disabledVoidColor) 只读时未选中 icon 的颜色, 默认值: #EFF2F7
 * @method $this iconClasses(array $iconClasses) icon 的类名。若传入数组，共有 3 个元素，为 3 个分段所对应的类名；若传入对象，可自定义分段，键名为分段的界限值，键值为对应的类名, 默认值: ['el-icon-star-on', 'el-icon-star-on', 'el-icon-star-on']
 * @method $this voidIconClass(string $voidIconClass) 未选中 icon 的类名, 默认值: el-icon-star-off
 * @method $this disabledVoidIconClass(string $disabledVoidIconClass) 只读时未选中 icon 的类名, 默认值: el-icon-star-on
 * @method $this showText(bool $showText) 是否显示辅助文字，若为真，则会从 texts 数组中选取当前分数对应的文字内容, 默认值: false
 * @method $this showScore(bool $showScore) 是否显示当前分数，show-score 和 show-text 不能同时为真, 默认值: false
 * @method $this textColor(string $textColor) 辅助文字的颜色, 默认值: #1F2D3D
 * @method $this texts(array $texts) 辅助文字数组, 默认值: ['极差', '失望', '一般', '满意', '惊喜']
 * @method $this scoreTemplate(string $scoreTemplate) 分数显示模板, 默认值: {value}
 *
 */
class Rate extends FormComponent
{
    protected $selectComponent = true;

    protected static $propsRule = [
        'max' => 'float',
        'disabled' => 'bool',
        'allowHalf' => 'bool',
        'lowThreshold' => 'float',
        'highThreshold' => 'float',
        'colors' => 'array',
        'voidColor' => 'string',
        'disabledVoidColor' => 'string',
        'iconClasses' => 'array',
        'voidIconClass' => 'string',
        'disabledVoidIconClass' => 'string',
        'showText' => 'bool',
        'showScore' => 'bool',
        'textColor' => 'string',
        'texts' => 'array',
        'scoreTemplate' => 'string',
    ];

    public function createValidate()
    {
        return Elm::validateNum();
    }

}