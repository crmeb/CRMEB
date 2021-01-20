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
 * col栅格规则
 * Class Col
 *
 * @package FormBuilder\components
 * @method $this span(Number $span) 栅格的占位格数，可选值为0~24的整数，为 0 时，相当于display:none
 * @method $this order(Number $order) 栅格的顺序，在flex布局模式下有效
 * @method $this offset(Number $offset) 栅格左侧的间隔格数，间隔内不可以有栅格
 * @method $this push(Number $push) 栅格向右移动格数
 * @method $this pull(Number $pull) 栅格向左移动格数
 * @method $this labelWidth(Number $labelWidth) 表单域标签的的宽度, 默认150px
 * @method $this className(String $className) 自定义的class名称
 * @method $this xs(Number | Col $span) <768px 响应式栅格，可为栅格数或一个包含其他属性的对象
 * @method $this sm(Number | Col $span) ≥768px 响应式栅格，可为栅格数或一个包含其他属性的对象
 * @method $this md(Number | Col $span) ≥992px 响应式栅格，可为栅格数或一个包含其他属性的对象
 * @method $this lg(Number | Col $span) ≥1200px 响应式栅格，可为栅格数或一个包含其他属性的对象
 */
class Col implements FormComponentInterFace
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
        'span' => 'number',
        'order' => 'number',
        'offset' => 'number',
        'push' => 'number',
        'pull' => 'number',
        'labelWidth' => 'number',
        'className' => 'number',
        'xs' => '',
        'sm' => '',
        'md' => '',
        'lg' => '',
    ];

    /**
     * @var array
     */
    protected static $model = ['xs', 'sm', 'md', 'lg'];

    /**
     * Col constructor.
     *
     * @param int $span
     */
    public function __construct($span = 24)
    {
        $this->props['span'] = $span;
    }

    /**
     * @return array
     */
    public function build()
    {
        foreach (self::$model as $m) {
            if (isset($this->props[$m]) && $this->props[$m] instanceof Col) {
                $this->props[$m] = $this->props[$m]->build();
            }
        }
        return $this->props;
    }

}