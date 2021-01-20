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

namespace FormBuilder\Rule;


use FormBuilder\Contract\ColComponentInterface;

trait BaseRule
{

    /**
     * 组件类型
     *
     * @var string
     */
    protected $type;

    /**
     * 组件字段名
     *
     * @var string
     */
    protected $field;

    /**
     * 字段昵称
     *
     * @var string
     */
    protected $title;

    /**
     * 组件名称
     *
     * @var string
     */
    protected $name;

    /**
     * 组件的提示信息
     *
     * @var string
     */
    protected $info;

    /**
     * 组件 class
     *
     * @var string
     */
    protected $className;

    /**
     * 是否原样生成组件,不嵌套的FormItem中
     *
     * @var bool
     */
    protected $native;

    /**
     * 事件注入时的自定义数据
     *
     * @var mixed
     */
    protected $inject;

    /**
     * 组件布局规则
     *
     * @var ColComponentInterface|array
     */
    protected $col;

    /**
     * 组件的值
     *
     * @var mixed
     */
    protected $value = '';

    /**
     * 组件显示状态
     *
     * @var bool
     */
    protected $hidden;

    /**
     * 组件显示状态
     *
     * @var bool
     */
    protected $visibility;

    /**
     * 组件显示状态
     *
     * @param bool $hidden
     * @return $this
     */
    public function hiddenStatus($hidden = true)
    {
        $this->hidden = !!$hidden;
        return $this;
    }

    /**
     * 组件显示状态
     *
     * @param bool $visibility
     * @return $this
     */
    public function visibilityStatus($visibility = true)
    {
        $this->visibility = !!$visibility;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    protected function setRuleType($type)
    {
        $this->type = (string)$type;
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function field($field)
    {
        $this->field = (string)$field;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function title($title)
    {
        $this->title = (string)$title;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function className($className)
    {
        $this->className = (string)$className;
        return $this;
    }

    /**
     * @param bool $native
     * @return $this
     */
    public function native($native)
    {
        $this->native = !!$native;
        return $this;
    }

    /**
     * @param mixed $inject
     * @return $this
     */
    public function inject($inject)
    {
        $this->inject = $inject;
        return $this;
    }

    /**
     * @param string $info
     * @return $this
     */
    public function info($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @param array|int|ColComponentInterface $col
     * @return $this
     */
    public function col($col)
    {
        if (is_integer($col)) $col = ['span' => $col];
        $this->col = $col;
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function value($value)
    {
        if (is_null($value)) $value = '';
        $this->value = $value;
        return $this;
    }

    public function getHiddenStatus()
    {
        return $this->hidden;
    }

    public function getVisibilityStatus()
    {
        return $this->visibility;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function getNative()
    {
        return $this->native;
    }

    public function getInject()
    {
        return $this->inject;
    }

    public function getCol()
    {
        return $this->col;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected function parseCol($col)
    {
        return $col instanceof ColComponentInterface ? $col->getCol() : $col;
    }

    protected function parseBaseRule()
    {
        $rule = [
            'type' => $this->type
        ];

        if (!is_null($this->field))
            $rule['field'] = $this->field;
        if (!is_null($this->value))
            $rule['value'] = $this->value;
        if (!is_null($this->title))
            $rule['title'] = $this->title;
        if (!is_null($this->className))
            $rule['className'] = $this->className;
        if (!is_null($this->name))
            $rule['name'] = $this->name;
        if (!is_null($this->native))
            $rule['native'] = $this->native;
        if (!is_null($this->info))
            $rule['info'] = $this->info;
        if (!is_null($this->inject))
            $rule['inject'] = $this->inject;
        if (!is_null($this->hidden))
            $rule['hidden'] = $this->hidden;
        if (!is_null($this->visibility))
            $rule['visibility'] = $this->visibility;
        if (!is_null($this->col))
            $rule['col'] = $this->parseCol($this->col);

        return $rule;
    }

}