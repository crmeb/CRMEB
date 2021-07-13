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


trait PropsRule
{
    /**
     * 组件的props
     *
     * @var array
     */
    protected $props = [];

    /**
     * 组件普通的 HTML 特性
     *
     * @var array
     */
    protected $attrs = [];

    /**
     * 组件的样式
     * @var string|array
     */
    protected $style;

    /**
     * 组件的 DOM 属性
     *
     * @var array
     */
    protected $domProps = [];

    /**
     * 设置组件的 style 属性
     * @param string|array $style
     * @return $this
     */
    public function style($style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @return array|string
     */
    public function getStyle()
    {
        return $this->style;
    }

    public function prop($name, $value)
    {
        $this->props[$name] = $value;
        return $this;
    }

    public function props(array $props)
    {
        $this->props = array_merge($this->props, $props);
        return $this;
    }

    public function attr($name, $value)
    {
        $this->attrs[$name] = $value;
        return $this;
    }

    public function attrs(array $attrs)
    {
        $this->attrs = array_merge($this->attrs, $attrs);
        return $this;
    }

    public function domProp($name, $value)
    {
        $this->domProps[$name] = $value;
        return $this;
    }

    public function domProps(array $domProps)
    {
        $this->domProps = array_merge($this->domProps, $domProps);
        return $this;
    }

    public function getProps()
    {
        return $this->props;
    }

    public function getProp($name)
    {
        return isset($this->props[$name]) ? $this->props[$name] : null;
    }

    public function getAttrs()
    {
        return $this->attrs;
    }

    public function getDomProps()
    {
        return $this->domProps;
    }

    public function parsePropsRule()
    {
        $rule = ['props' => (object)$this->props];
        if (count($this->attrs))
            $rule['attrs'] = $this->attrs;
        if (count($this->domProps))
            $rule['domProps'] = $this->domProps;
        if (!is_null($this->style))
            $rule['style'] = $this->style;

        return $rule;
    }
}