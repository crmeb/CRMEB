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


use FormBuilder\Contract\BootstrapInterface;
use FormBuilder\Contract\ConfigInterface;
use FormBuilder\Exception\FormBuilderException;
use FormBuilder\UI\Iview\Bootstrap as IViewBootstrap;
use FormBuilder\UI\Elm\Bootstrap as ElmBootstrap;

class Form
{
    protected $headers = [];

    protected $formContentType = 'application/x-www-form-urlencoded';

    /**
     * @var BootstrapInterface
     */
    protected $ui;

    /**
     * @var array|ConfigInterface
     */
    protected $config;

    protected $action;

    protected $method = 'POST';

    protected $title = 'FormBuilder';

    protected $rule;

    protected $formData = [];

    protected $dependScript = [
        '<script src="https://unpkg.com/jquery@3.3.1/dist/jquery.min.js"></script>',
        '<script src="https://unpkg.com/vue@2.5.13/dist/vue.min.js"></script>',
        '<script src="https://unpkg.com/@form-create/data@1.0.0/dist/province_city.js"></script>',
        '<script src="https://unpkg.com/@form-create/data@1.0.0/dist/province_city_area.js"></script>'
    ];

    protected $template;

    /**
     * Form constructor.
     * @param BootstrapInterface $ui
     * @param string $action
     * @param array $rule
     * @param array $config
     * @throws FormBuilderException
     */
    protected function __construct(BootstrapInterface $ui, $action = '', $rule = [], $config = [])
    {
        $this->action = $action;
        $this->rule = $rule;
        $this->config = $config;
        $this->ui = $ui;
        $ui->init($this);
        $this->checkFieldUnique();
        $this->template = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'form.php';
    }

    /**
     * @return string
     */
    public function getFormContentType()
    {
        return $this->formContentType;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = (string)$value;

        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function headers(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param array $formData
     * @return $this
     */
    public function formData(array $formData)
    {
        $this->formData = $formData;
        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function setValue($field, $value)
    {
        $this->formData[$field] = $value;
        return $this;
    }

    /**
     * @return false|string
     */
    public function parseHeaders()
    {
        return json_encode((object)$this->headers);
    }

    /**
     * @param $formContentType
     * @return $this
     */
    public function setFormContentType($formContentType)
    {
        $this->formContentType = (string)$formContentType;

        return $this;
    }

    public function setDependScript(array $dependScript)
    {
        $this->dependScript = $dependScript;

        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;

        return $this;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = (string)$method;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param array $rule
     * @return $this
     * @throws FormBuilderException
     */
    public function setRule(array $rule)
    {
        $this->rule = $rule;
        $this->checkFieldUnique();
        return $this;
    }

    /**
     * @param array|ConfigInterface $config
     * @return $this
     */
    public function setConfig($config)
    {
        if (is_array($config) || $config instanceof ConfigInterface)
            $this->config = $config;
        return $this;
    }

    /**
     * 追加组件
     *
     * @param $component
     * @return $this
     * @throws FormBuilderException
     */
    public function append($component)
    {
        $this->rule[] = $component;
        $this->checkFieldUnique();
        return $this;
    }

    /**
     * 开头插入组件
     *
     * @param $component
     * @return $this
     * @throws FormBuilderException
     */
    public function prepend($component)
    {
        array_unshift($this->rule, $component);
        $this->checkFieldUnique();
        return $this;
    }

    /**
     * @param $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = (string)$action;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * 提交按钮显示状态
     *
     * @param $isShow
     * @return $this
     */
    public function showSubmitBtn($isShow)
    {
        if ($this->config instanceof ConfigInterface)
            $this->config->submitBtn(!!$isShow);
        else
            $this->config['submitBtn'] = !!$isShow;
        return $this;
    }

    /**
     * 重置按钮显示状态
     *
     * @param $isShow
     * @return $this
     */
    public function showResetBtn($isShow)
    {
        if ($this->config instanceof ConfigInterface)
            $this->config->resetBtn(!!$isShow);
        else
            $this->config['resetBtn'] = !!$isShow;
        return $this;
    }

    /**
     * 设置组件全局配置
     * @param string $componentName
     * @param array $config
     * @return $this
     */
    public function componentGlobalConfig($componentName, array $config)
    {
        if ($this->config instanceof ConfigInterface)
            $this->config->componentGlobalConfig($componentName, $config);
        else {
            if (!isset($this->config['global'])) $this->config['global'] = [];
            $this->config['global'][$componentName] = $config;
        }
        return $this;
    }

    protected function parseFormComponent($rule)
    {
        if (Util::isComponent($rule)) {
            $rule = $rule->build();
        } else {
            if (isset($rule['children']) && is_array($rule['children'])) {
                foreach ($rule['children'] as $i => $child) {
                    $rule['children'][$i] = $this->parseFormComponent($child);
                }
            }
            if (isset($rule['control'])) {
                foreach ($rule['control'] as $i => $child) {
                    foreach ($child['rule'] as $k => $rule) {
                        $child['rule'][$k] = Util::isComponent($rule) ? $rule->build() : $rule;
                    }
                    $rule['control'][$i] = $child;
                }
            }
        }
        return $rule;
    }

    public function getDependScript()
    {
        return $this->dependScript;
    }

    /**
     * @param array $formData
     * @param array $rule
     * @return array
     */
    protected function deepSetFormData($formData, $rule)
    {
        if (!count($formData)) return $rule;
        foreach ($rule as $k => $item) {
            if (is_array($item)) {
                if (isset($item['field']) && isset($formData[$item['field']])) {
                    $item['value'] = $formData[$item['field']];
                }
                if (isset($item['children']) && is_array($item['children']) && count($item['children'])) {
                    $item['children'] = $this->deepSetFormData($formData, $item['children']);
                }
                if (isset($item['control']) && count($item['control'])) {
                    foreach ($item['control'] as $_k => $_rule) {
                        $item['control'][$_k]['rule'] = $this->deepSetFormData($formData, $_rule['rule']);
                    }
                }
            }
            $rule[$k] = $item;
        }

        return $rule;
    }

    /**
     * 获取表单生成规则
     *
     * @return array
     */
    public function formRule()
    {
        $rules = [];
        foreach ($this->rule as $rule) {
            $rules[] = $this->parseFormComponent($rule);
        }
        return $this->deepSetFormData($this->formData, $rules);
    }

    /**
     * @return false|string
     */
    public function parseFormRule()
    {
        return json_encode($this->formRule());
    }

    /**
     * @return false|string
     */
    public function parseFormConfig()
    {
        return json_encode((object)$this->formConfig());
    }

    /**
     * @return string
     */
    public function parseDependScript()
    {
        return implode("\r\n", $this->dependScript);
    }

    /**
     * 获取表单配置
     *
     * @return array
     */
    public function formConfig()
    {
        $config = $this->config;
        if ($config instanceof ConfigInterface) return $config->getConfig();
        foreach ($config as $k => $v) {
            $config[$k] = $this->parseFormComponent($v);
        }
        return $config;
    }


    /**
     * 获取表单创建的 js 代码
     *
     * @return false|string
     */
    public function formScript()
    {
        return $this->template(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'createScript.min.php');
    }

    /**
     * 获取表单视图
     *
     * @return string
     */
    public function view()
    {
        return $this->template($this->template);
    }

    /**
     * 自定义表单页面
     *
     * @param $templateDir
     * @return false|string
     */
    public function template($templateDir)
    {
        ob_start();
        $form = $this;
        require $templateDir;
        $html = ob_get_clean();
        return $html;
    }

    /**
     * 设置模板
     *
     * @param string $templateDir
     * @return $this
     */
    public function setTemplate($templateDir)
    {
        $this->template = $templateDir;
        return $this;
    }

    /**
     * 检查field 是否重复
     *
     * @param null $rules
     * @param array $fields
     * @return array
     * @throws FormBuilderException
     */
    protected function checkFieldUnique($rules = null, $fields = [])
    {
        if (is_null($rules)) $rules = $this->rule;

        foreach ($rules as $rule) {
            $rule = $this->parseFormComponent($rule);
            $field = isset($rule['field']) ? $rule['field'] : null;

            if (isset($rule['children']) && count($rule['children']))
                $fields = $this->checkFieldUnique($rule['children'], $fields);

            if (is_null($field) || $field === '')
                continue;
            else if (isset($fields[$field]))
                throw new FormBuilderException('组件的 field 不能重复');
            else
                $fields[$field] = true;
        }

        return $fields;
    }

    /**
     * Iview 版表单生成器
     *
     * @param string $action
     * @param array $rule
     * @param array|ConfigInterface $config
     * @return Form
     * @throws FormBuilderException
     */
    public static function iview($action = '', $rule = [], $config = [])
    {
        return new self(new IViewBootstrap(), $action, $rule, $config);
    }

    /**
     * Iview v4 版表单生成器
     *
     * @param string $action
     * @param array $rule
     * @param array|ConfigInterface $config
     * @return Form
     * @throws FormBuilderException
     */
    public static function iview4($action = '', $rule = [], $config = [])
    {
        return new self(new IViewBootstrap(4), $action, $rule, $config);
    }

    /**
     * element-ui 版表单生成器
     *
     * @param string $action
     * @param array $rule
     * @param array|ConfigInterface $config
     * @return Form
     * @throws FormBuilderException
     */
    public static function elm($action = '', $rule = [], $config = [])
    {
        return new self(new ElmBootstrap(), $action, $rule, $config);
    }
}