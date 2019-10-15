<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder;

use FormBuilder\components\Cascader;
use FormBuilder\components\FormStyle;
use FormBuilder\components\Hidden;
use FormBuilder\components\Row;
use FormBuilder\exception\FormBuilderException;
use FormBuilder\traits\form\FormCascaderTrait;
use FormBuilder\traits\form\FormCheckBoxTrait;
use FormBuilder\traits\form\FormColorPickerTrait;
use FormBuilder\traits\form\FormDatePickerTrait;
use FormBuilder\traits\form\FormFrameTrait;
use FormBuilder\traits\form\FormHiddenTrait;
use FormBuilder\traits\form\FormInputNumberTrait;
use FormBuilder\traits\form\FormInputTrait;
use FormBuilder\traits\form\FormRadioTrait;
use FormBuilder\traits\form\FormRateTrait;
use FormBuilder\traits\form\FormSelectTrait;
use FormBuilder\traits\form\FormSliderTrait;
use FormBuilder\traits\form\FormStyleTrait;
use FormBuilder\traits\form\FormSwitchesTrait;
use FormBuilder\traits\form\FormTimePickerTrait;
use FormBuilder\traits\form\FormTreeTrait;
use FormBuilder\traits\form\FormUploadTrait;
use FormBuilder\traits\form\FormOptionTrait;
use FormBuilder\traits\form\FormValidateTrait;

/**
 * Class Form
 *
 * @package FormBuilder
 */
class Form
{
    use FormColorPickerTrait,
        FormFrameTrait,
        FormInputNumberTrait,
        FormRadioTrait,
        FormRateTrait,
        FormSelectTrait,
        FormSwitchesTrait,
        FormUploadTrait,
        FormCheckBoxTrait,
        FormDatePickerTrait,
        FormInputTrait,
        FormSliderTrait,
        FormCascaderTrait,
        FormHiddenTrait,
        FormTimePickerTrait,
        FormTreeTrait,
        FormStyleTrait,
        FormOptionTrait,
        FormValidateTrait;

    /**
     * 三级联动 加载省市数据
     *
     * @var bool
     */
    protected $loadCityData = false;

    /**
     * 三级联动 加载省市区数据
     *
     * @var bool
     */
    protected $loadCityAreaData = false;

    /**
     * @var array
     */
    protected $components = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $script = [
        'jq' => '<script src="https://unpkg.com/jquery@3.3.1/dist/jquery.min.js"></script>',
        'vue' => '<script src="https://unpkg.com/vue@2.5.13/dist/vue.min.js"></script>',
        //iview 版本 2.14.3
        'iview-css' => '<link href="https://unpkg.com/iview@2.14.3/dist/styles/iview.css" rel="stylesheet">',
        'iview' => '<script src="https://unpkg.com/iview@2.14.3/dist/iview.min.js"></script>',
        //form-create 版本 1.6.1
        'form-create' => '<script src="https://unpkg.com/form-create@1.6.1/dist/form-create.min.js"></script>',
        'city-data' => '<script src="https://unpkg.com/form-create@1.6.1/district/province_city.js"></script>',
        'city-area-data' => '<script src="https://unpkg.com/form-create@1.6.1/district/province_city_area.js"></script>'
    ];

    /**
     * 加载 jquery
     *
     * @var bool
     */
    protected $linkJq = true;

    /**
     * 加载 vue
     *
     * @var bool
     */
    protected $linkVue = true;

    /**
     * 加载 iview
     *
     * @var bool
     */
    protected $linkIview = true;

    /**
     * @var string
     */
    protected $successScript = '';

    /**
     * 网页标题
     *
     * @var string
     */
    protected $title = 'formBuilder';

    /**
     * 提交地址
     *
     * @var string
     */
    protected $action = '';

    /**
     * 表单id
     *
     * @var string
     */
    protected $id = '';

    /**
     * 提交方式
     *
     * @var string
     */
    protected $method = 'post';

    protected $resetBtn = false;

    protected $submitBtn = true;

    /**
     * 表单配置
     *
     * @var array|mixed
     */
    protected $config = [
        'form' => [
            'inline' => false,
            'labelPosition' => 'right',
            'labelWidth' => 125,
            'showMessage' => true,
            'autocomplete' => 'off'
        ],
        'row' => []
    ];

    /**
     * Form constructor.
     *
     * @param string $action 提交地址
     * @param array $components 组件
     */
    public function __construct($action = '', array $components = [])
    {
        $this->components($components);
        $this->action = $action;
    }

    public static function json()
    {
        return new Json();
    }

    /**
     * @param bool $linkJq
     */
    public function setLinkJq($linkJq)
    {
        $this->linkJq = (bool)$linkJq;
    }

    /**
     * @param bool $linkVue
     */
    public function setLinkVue($linkVue)
    {
        $this->linkVue = (bool)$linkVue;
    }

    /**
     * @param bool $linkIview
     */
    public function setLinkIview($linkIview)
    {
        $this->linkIview = (bool)$linkIview;
    }

    /**
     * @return bool
     */
    public function isLoadCityData()
    {
        return $this->loadCityData;
    }

    /**
     * @return bool
     */
    public function isLoadCityAreaData()
    {
        return $this->loadCityAreaData;
    }

    /**
     * @param array $components
     * @return $this
     */
    public function components(array $components = [])
    {
        foreach ($components as $component) {
            $this->append($component);
        }
        return $this;
    }

    /**
     * @param Row $row
     * @return $this
     */
    public function formRow(Row $row)
    {
        $this->config['row'] = $row->build();
        return $this;
    }

    /**
     * @param FormStyle $formStyle
     * @return $this
     */
    public function formStyle(FormStyle $formStyle)
    {
        $this->config['form'] = $formStyle->build();
        return $this;
    }

    /**
     * @return string
     */
    public function getSuccessScript()
    {
        return $this->successScript;
    }

    /**
     * 表单提交后成功执行的js地址
     * formCreate.formSuccess(formData,$f)
     *
     * @param string $successScript
     * @return $this
     */
    public function setSuccessScript($successScript)
    {
        $this->successScript = $successScript;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * 提交地址
     *
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $key
     * @return array|mixed|null
     */
    public function getConfig($key = '')
    {
        if ($key == '')
            return $this->config;
        else
            return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * 提交方式
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * 标题
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }


    /**
     * 追加组件
     *
     * @param FormComponentDriver $component
     * @return $this
     */
    public function append(FormComponentDriver $component)
    {
        $field = $component->getField();
        if (!isset($this->components[$field]))
            $this->fields[] = $field;
        $this->components[$field] = $component;
        $this->checkLoadData($component);
        return $this;
    }

    /**
     * 开头插入组件
     *
     * @param FormComponentDriver $component
     * @return $this
     */
    public function prepend(FormComponentDriver $component)
    {
        $field = $component->getField();
        if (!isset($this->components[$field]))
            array_unshift($this->fields, $field);
        $this->components[$field] = $component;
        $this->checkLoadData($component);
        return $this;
    }

    /**
     * 是否需要引入省市区数据
     *
     * @param FormComponentDriver $component
     */
    protected function checkLoadData(FormComponentDriver $component)
    {
        if (
            $component instanceof Cascader
            && ($this->loadCityData == false || $this->loadCityAreaData == false)
        ) {
            $type = $component->getType();
            if ($type == Cascader::TYPE_CITY)
                $this->loadCityData = true;
            else if ($type == Cascader::TYPE_CITY_AREA)
                $this->loadCityAreaData = true;
        }
    }

    /**
     * 获得表单规则
     *
     * @return array
     * @throws FormBuilderException
     */
    public function getRules()
    {
        $rules = [];
        $fields = [];
        foreach ($this->fields as $field) {
            $component = $this->components[$field];
            if (!($component instanceof FormComponentDriver))
                continue;
            $field = $component->getField();
            if (in_array($field, $fields))
                throw new FormBuilderException($field . '字段已重复,请保证组件 field 无重复');

            $fields[] = $field;
            $rule = $component->build();
            if ($component->info)
                $rule['info'] = $component->info;
            if (!$component instanceof Hidden)
                $rule['validate'] = array_merge(isset($rule['validate']) ? $rule['validate'] : [], $component->validate()->build());
            $rules[] = $rule;
        }
        return $rules;
    }


    /**
     * 获取表单视图
     *
     * @return string
     */
    public function view()
    {
        ob_start();
        $form = $this;
        require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'form.php';
        $html = ob_get_clean();
        return $html;
    }

    /**
     * 获取表单生成器所需全部js
     *
     * @return array
     */
    public function script()
    {
        return $this->script;
    }

    /**
     * 获取生成表单的js代码
     *
     * @return string
     */
    public function formScript()
    {
        ob_start();
        $form = $this;
        require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'formScript.php';
        $script = ob_get_clean();
        return $script;

    }

    /**
     * 获取表单生成器所需js
     *
     * @return array
     */
    public function getScript()
    {
        $_script = $this->script;
        $script = [
            $_script['form-create']
        ];
        if ($this->loadCityAreaData == true)
            $script[] = $_script['city-area-data'];
        if ($this->loadCityData == true)
            $script[] = $_script['city-data'];
        if ($this->linkJq)
            $script[] = $_script['jq'];
        if ($this->linkIview) {
            $script[] = $_script['iview'];
            $script[] = $_script['iview-css'];
        }
        if ($this->linkVue)
            $script[] = $_script['vue'];
        return array_reverse($script);
    }

    /**
     * 是否隐藏提交按钮(默认显示)
     *
     * @param bool $isShow
     * @return Form
     */
    public function hiddenSubmitBtn($isShow = false)
    {
        $this->submitBtn = !(bool)$isShow;

        return $this;
    }

    /**
     * 是否隐藏重置按钮(默认隐藏)
     *
     * @param bool $isShow
     * @return Form
     */
    public function hiddenResetBtn($isShow = false)
    {
        $this->resetBtn = !(bool)$isShow;

        return $this;
    }

    /**
     * @return bool
     */
    public function isResetBtn()
    {
        return $this->resetBtn;
    }

    /**
     * @return bool
     */
    public function isSubmitBtn()
    {
        return $this->submitBtn;
    }

    /**
     * 生成表单快捷方法
     *
     * @param string $action
     * @param array $components
     * @return Form
     */
    public static function create($action, array $components = [])
    {
        return new self($action, $components);
    }
}