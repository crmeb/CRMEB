<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;
use FormBuilder\Helper;

/**
 * 框架组件
 * Class Frame
 *
 * @package FormBuilder\components
 * @method $this type(String $type) frame类型, 有input, file, image, 默认为input
 * @method $this src(String $src) iframe地址
 * @method $this maxLength(int $length) value的最大数量, 默认无限制
 * @method $this icon(String $icon) 打开弹出框的按钮图标
 * @method $this height(String $height) 弹出框高度
 * @method $this width(String $width) 弹出框宽度
 * @method $this spin(Boolean $bool) 是否显示加载动画, 默认为 true
 * @method $this frameTitle(String $title) 弹出框标题
 * @method $this handleIcon(Boolean $bool) 操作按钮的图标, 设置为false将不显示, 设置为true为默认的预览图标, 类型为file时默认为false, image类型默认为true
 * @method $this allowRemove(Boolean $bool) 是否可删除, 设置为false是不显示删除按钮
 *
 *
 */
class Frame extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'frame';

    /**
     *
     */
    const TYPE_IMAGE = 'image';
    /**
     *
     */
    const TYPE_FILE = 'file';
    /**
     *
     */
    const TYPE_INPUT = 'input';

    /**
     * @var array
     */
    protected $props = [
        'type' => self::TYPE_INPUT,
        'maxLength' => 0
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'type' => 'string',
        'src' => 'string',
        'maxLength' => 'int',
        'icon' => 'string',
        'height' => 'string',
        'width' => 'string',
        'spin' => 'boolean',
        'frameTitle' => ['string', 'title'],
        'handleIcon' => 'boolean',
        'allowRemove' => 'boolean',
    ];

    /**
     * @param string|array $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     *
     */
    protected function init()
    {
        $this->frameTitle($this->getPlaceHolder());
    }

    public function getValidateHandler()
    {
        return Validate::arr();
    }

    /**
     * @return array
     */
    public function build()
    {
        $value = $this->value;
        if ($this->props['maxLength'] == 1 && is_array($value))
            $value = isset($value[0]) ? $value[0] : '';

        return [
            'type' => $this->name,
            'field' => $this->field,
            'title' => $this->title,
            'value' => $value,
            'props' => (object)$this->props,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }
}