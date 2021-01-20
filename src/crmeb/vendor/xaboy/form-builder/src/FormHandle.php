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

namespace FormBuilder;


use FormBuilder\Annotation\AnnotationReader;
use FormBuilder\Contract\ConfigInterface;
use FormBuilder\Contract\FormHandleInterface;

/**
 * 表单生成类
 *
 * Class FormHandle
 * @package FormBuilder
 */
abstract class FormHandle implements FormHandleInterface
{
    protected $action = '';

    protected $method = 'POST';

    protected $title;

    protected $formContentType;

    protected $headers = [];

    protected $fieldTitles = [];

    protected $except = [];

    protected $scene;

    /**
     * 表单 UI
     *
     * @return mixed
     */
    abstract public function ui();

    final public function getExcept()
    {
        return $this->except;
    }

    /**
     * 获取表单数据
     * @return array
     */
    protected function getFormData()
    {
        return [];
    }

    public function scene($scene = null)
    {
        if (!is_null($scene)) $this->scene = $scene;
        return $this->scene;
    }

    /**
     * 获取表单配置
     *
     * @return mixed|array|ConfigInterface
     */
    protected function getFormConfig()
    {
        return;
    }

    public function getFieldTitle($field)
    {
        return isset($this->fieldTitles[$field]) ? $this->fieldTitles[$field] : null;
    }

    /**
     * 获取表单组件
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getFormRule()
    {
        $render = new AnnotationReader($this);
        return $render->render();
    }

    /**
     * 创建表单
     *
     * @return Form
     * @throws \ReflectionException
     */
    protected function createForm()
    {
        $ui = lcfirst($this->ui());
        return call_user_func_array(['FormBuilder\\Form', $ui], $this->getParams());
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected function getParams()
    {
        $params = [$this->action, $this->getFormRule()];
        $config = $this->getFormConfig();
        if (is_array($config) || $config instanceof ConfigInterface)
            $params[] = $config;

        return $params;
    }

    /**
     * 获取表单
     *
     * @return Form
     * @throws \ReflectionException
     */
    public function form()
    {
        if ($this->scene && method_exists($this, $this->scene . 'Scene'))
            $this->{$this->scene . 'Scene'}();

        $form = $this->createForm()->setMethod($this->method);
        if (!is_null($this->title)) $form->setTitle($this->title)->headers($this->headers);
        $formData = $this->getFormData();
        if (is_array($formData)) $form->formData($formData);
        if ($this->formContentType) $form->setFormContentType($this->formContentType);
        $config = $this->getFormConfig();
        if ($config) $form->setConfig($config);
        return $form;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function view()
    {
        return $this->form()->view();
    }
}