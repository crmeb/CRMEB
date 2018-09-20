<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder;

use FormBuilder\components\Cascader;
use FormBuilder\components\FormStyle;
use FormBuilder\components\Row;
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

/**
 * Class Form
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
        FormOptionTrait;

    /**
     * 三级联动 加载省市数据
     * @var bool
     */
    protected $loadCityData = false;

    /**
     * 三级联动 加载省市区数据
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
        'jq' => '<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>',
        'vue' => '<script src="https://cdn.bootcss.com/vue/2.5.13/vue.min.js"></script>',
        //iview 版本 2.14.3
        'iview-css' => '<link href="https://cdn.jsdelivr.net/npm/iview@2.14.3/dist/styles/iview.css" rel="stylesheet">',
        'iview' => '<script src="https://cdn.jsdelivr.net/npm/iview@2.14.3/dist/iview.min.js"></script>',
        //form-create 版本 1.3.3
        'form-create' => '<script src="https://cdn.jsdelivr.net/npm/form-create@1.4.0/dist/form-create.min.js"></script>',
        'city-data' => '<script src="https://cdn.jsdelivr.net/npm/form-create/district/province_city.js"></script>',
        'city-area-data' => '<script src="https://cdn.jsdelivr.net/npm/form-create/district/province_city_area.js"></script>'
    ];

    /**
     * @var string
     */
    protected $successScript = '';

    /**
     * 网页标题
     * @var string
     */
    protected $title = 'formBuilder';

    /**
     * 提交地址
     * @var string
     */
    protected $action = '';

    /**
     * 表单id
     * @var string
     */
    protected $id = '';

    /**
     * 提交方式
     * @var string
     */
    protected $method = 'post';

    /**
     * 表单配置
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
     * @param string $action 提交地址
     * @param array $components 组件
     */
    public function __construct($action = '', array $components = [])
    {
        $this->components($components);
        $this->action = $action;
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
        foreach ($components as $component){
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
        if($key =='')
            return $this->config;
        else
            return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * 提交方式
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
     * @param FormComponentDriver $component
     * @return $this
     */
    public function append(FormComponentDriver $component)
    {
        $field = $component->getField();
        if(!isset($this->components[$field]))
            $this->fields[] = $field;
        $this->components[$field] = $component;
        $this->checkLoadData($component);
        return $this;
    }

    /**
     * 开头插入组件
     * @param FormComponentDriver $component
     * @return $this
     */
    public function prepend(FormComponentDriver $component)
    {
        $field = $component->getField();
        if(!isset($this->components[$field]))
            array_unshift($this->fields, $field);
        $this->components[$field] = $component;
        $this->checkLoadData($component);
        return $this;
    }

    /**
     * @param FormComponentDriver $component
     */
    protected function checkLoadData(FormComponentDriver $component)
    {
        if(
            $component instanceof Cascader
            && ($this->loadCityData == false || $this->loadCityAreaData == false)
        ){
            $type = $component->getType();
            if ($type == Cascader::TYPE_CITY)
                $this->loadCityData = true;
            else if ($type == Cascader::TYPE_CITY_AREA)
                $this->loadCityAreaData = true;
        }
    }

    /**
     * 获得表单规则
     * @return array
     */
    public function getRules()
    {
        $rules = [];
        foreach ($this->fields as $field) {
            $component = $this->components[$field];
            if (!($component instanceof FormComponentDriver))
                continue;
            $rules[] = $component->build();
        }
        return $rules;
    }


    /**
     * 获取表单视图
     * @return string
     */
    public function view()
    {
        ob_start();
        $form = $this;
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'form.php';
        $html = ob_get_clean();
        return $html;
    }

    /**
     * 获取表单生成器所需全部js
     * @return array
     */
    public function script()
    {
        return $this->script;
    }

    /**
     * 获取生成表单的js代码
     * @return string
     */
    public function formScript()
    {
        ob_start();
        $form = $this;
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'formScript.php';
        $script = ob_get_clean();
        return $script;

    }

    /**
     * 获取表单生成器所需js
     * @return array
     */
    public function getScript()
    {
        $_script = $this->script;
        $script = [
            $_script['iview-css'],
            $_script['jq'],
            $_script['vue'],
            $_script['iview'],
            $_script['form-create']
        ];
        if ($this->loadCityAreaData == true)
            $script[] = $_script['city-area-data'];
        if ($this->loadCityData == true)
            $script[] = $_script['city-data'];
        return $script;
    }

    /**
     * 生成表单快捷方法
     * @param string $action
     * @param array $components
     * @return Form
     */
    public static function create($action, array $components = [])
    {
        return new self($action, $components);
    }
}