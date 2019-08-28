<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\interfaces\FormComponentInterFace;
use FormBuilder\traits\component\CallPropsTrait;

/**
 * row栅格规则
 * Class Row
 *
 * @package FormBuilder\components
 * @method $this gutter(Number $gutter) 栅格间距，单位 px，左右平分
 * @method $this type(String $type) 栅格的顺序，在flex布局模式下有效
 * @method $this align(String $align) flex 布局下的垂直对齐方式，可选值为top、middle、bottom
 * @method $this justify(String $justify) flex 布局下的水平排列方式，可选值为start、end、center、space-around、space-between
 * @method $this className(String $className) 自定义的class名称
 */
class Row implements FormComponentInterFace
{
    use CallPropsTrait;

    /**
     * @var array
     */
    protected $props;

    /**
     * @var array
     */
    protected static $propsRule = [
        'gutter' => 'number',
        'type' => 'string',
        'align' => 'string',
        'justify' => 'string',
        'className' => 'string',
    ];

    /**
     * Row constructor.
     *
     * @param int    $gutter
     * @param string $type
     * @param string $align
     * @param string $justify
     * @param string $className
     */
    public function __construct($gutter = 0, $type = '', $align = '', $justify = '', $className = '')
    {
        $this->props = compact('gutter', 'type', 'align', 'justify', 'className');
    }

    /**
     * @return array
     */
    public function build()
    {
        return $this->props;
    }

}