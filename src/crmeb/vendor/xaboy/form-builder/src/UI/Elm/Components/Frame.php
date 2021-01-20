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
 * 框架组件
 * Class Frame
 *
 * @method $this type(string $type) frame类型, 有input, file, image, 默认为input
 * @method $this src(string $src) iframe地址
 * @method $this maxLength(int $length) value的最大数量, 默认无限制
 * @method $this icon(string $icon) 打开弹出框的按钮图标
 * @method $this height(string $height) 弹出框高度
 * @method $this width(string $width) 弹出框宽度
 * @method $this spin(bool $bool) 是否显示加载动画, 默认为 true
 * @method $this frameTitle(string $title) 弹出框标题
 * @method $this modal(array $modalProps) 弹出框props
 * @method $this handleIcon(bool $bool) 操作按钮的图标, 设置为false将不显示, 设置为true为默认的预览图标, 类型为file时默认为false, image类型默认为true
 * @method $this allowRemove(bool $bool) 是否可删除, 设置为false是不显示删除按钮
 * @method $this disabled(bool $bool) 是否禁用
 *
 *
 */
class Frame extends FormComponent
{
    /**
     * 图片类型
     */
    const TYPE_IMAGE = 'image';
    /**
     * 文件类型
     */
    const TYPE_FILE = 'file';
    /**
     * input 类型
     */
    const TYPE_INPUT = 'input';


    protected $selectComponent = true;

    protected $defaultProps = [
        'type' => self::TYPE_INPUT,
        'maxLength' => 0
    ];

    protected static $propsRule = [
        'type' => 'string',
        'src' => 'string',
        'maxLength' => 'int',
        'icon' => 'string',
        'height' => 'string',
        'width' => 'string',
        'spin' => 'bool',
        'modal' => 'array',
        'frameTitle' => ['string', 'title'],
        'handleIcon' => 'bool',
        'allowRemove' => 'bool',
    ];

    public function createValidate()
    {
        return Elm::validateArr();
    }

    protected function init()
    {
        $this->frameTitle($this->getPlaceHolder());
    }
}