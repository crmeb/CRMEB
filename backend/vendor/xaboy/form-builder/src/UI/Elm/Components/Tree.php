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
 * 树型组件
 * Class Tree
 *
 * @method $this type(string $type) 类型，可选值为 checked、selected
 * @method $this emptyText(string $emptyText) 内容为空的时候展示的文本
 * @method $this nodeKey(string $nodeKey) 每个树节点用来作为唯一标识的属性，整棵树应该是唯一的
 * @method $this props(array $props) 配置选项，具体看下表
 * @method $this renderAfterExpand(bool $renderAfterExpand) 是否在第一次展开某个树节点后才渲染其子节点, 默认值: true
 * @method $this highlightCurrent(bool $highlightCurrent) 是否高亮当前选中节点，默认值是 false。, 默认值: false
 * @method $this defaultExpandAll(bool $defaultExpandAll) 是否默认展开所有节点, 默认值: false
 * @method $this expandOnClickNode(bool $expandOnClickNode) 是否在点击节点的时候展开或者收缩节点， 默认值为 true，如果为 false，则只有点箭头图标的时候才会展开或者收缩节点。, 默认值: true
 * @method $this checkOnClickNode(bool $checkOnClickNode) 是否在点击节点的时候选中节点，默认值为 false，即只有在点击复选框时才会选中节点。, 默认值: false
 * @method $this autoExpandParent(bool $autoExpandParent) 展开子节点的时候是否自动展开父节点, 默认值: true
 * @method $this showCheckbox(bool $showCheckbox) 节点是否可被选择, 默认值: false
 * @method $this checkStrictly(bool $checkStrictly) 在显示复选框的情况下，是否严格的遵循父子不互相关联的做法，默认为 false, 默认值: false
 * @method $this accordion(bool $accordion) 是否每次只打开一个同级树节点展开, 默认值: false
 * @method $this indent(float $indent) 相邻级节点间的水平缩进，单位为像素, 默认值: 16
 * @method $this iconClass(string $iconClass) 自定义树节点的图标
 * @method $this draggable(bool $draggable) 是否开启拖拽节点功能, 默认值: false
 */
class Tree extends FormComponent
{
    /**
     * 选中
     */
    const TYPE_SELECTED = 'selected';
    /**
     * 选择
     */
    const TYPE_CHECKED = 'checked';


    protected $selectComponent = true;

    protected $defaultProps = [
        'type' => self::TYPE_CHECKED,
        'showCheckbox' => true,
        'data' => []
    ];

    protected static $propsRule = [
        'type' => 'string',
        'emptyText' => 'string',
        'nodeKey' => 'string',
        'props' => 'array',
        'renderAfterExpand' => 'bool',
        'highlightCurrent' => 'bool',
        'defaultExpandAll' => 'bool',
        'expandOnClickNode' => 'bool',
        'checkOnClickNode' => 'bool',
        'autoExpandParent' => 'bool',
        'showCheckbox' => 'bool',
        'checkStrictly' => 'bool',
        'accordion' => 'bool',
        'indent' => 'float',
        'iconClass' => 'string',
        'draggable' => 'bool',
    ];

    /**
     * @param array $treeData
     * @return $this
     */
    public function data(array $treeData)
    {
        $this->props['data'] = [];
        foreach ($treeData as $child) {
            $this->props['data'][] = $child instanceof TreeData
                ? $child->getOption()
                : $child;
        }
        return $this;
    }

    /**
     * @param string $var
     * @return $this
     */
    public function jsData($var)
    {
        $this->props['data'] = 'js.' . (string)$var;
        return $this;
    }

    public function createValidate()
    {
        return Elm::validateArr();
    }
}