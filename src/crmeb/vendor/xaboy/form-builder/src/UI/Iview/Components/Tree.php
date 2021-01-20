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
 * 树型组件
 * Class Tree
 *
 * @method $this type(string $type) 类型，可选值为 checked、selected
 * @method $this multiple(bool $bool) 是否支持多选, 当`type=selected`并且`multiple=false`, 默认为false, 值为string或Number类型，其他情况为Array类型
 * @method $this showCheckbox(bool $bool) 是否显示多选框, 默认为false
 * @method $this emptyText(string $emptyText) 没有数据时的提示, 默认为'暂无数据'
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
        'data' => [],
        'multiple' => true
    ];

    protected static $propsRule = [
        'type' => 'string',
        'multiple' => 'bool',
        'showCheckbox' => 'bool',
        'emptyText' => 'string',
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
        if ($this->props['multiple'])
            return Iview::validateArr();
        else
            return Iview::validateStr();
    }
}