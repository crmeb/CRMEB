<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\FormComponentDriver;
use FormBuilder\Helper;

/**
 * 上传组件
 * Class Upload
 *
 * @package FormBuilder\components
 * @method $this uploadType(String $uploadType) 上传文件类型，可选值为 image（图片上传），file（文件上传）
 * @method $this action(String $action) 上传的地址
 * @method $this multiple(Boolean $bool) 是否支持多选文件
 * @method $this name(String $name) 上传的文件字段名
 * @method $this accept(String $accept) 接受上传的文件类型
 * @method $this maxSize(int $size) 文件大小限制，单位 kb
 * @method $this withCredentials(Boolean $bool) 支持发送 cookie 凭证信息, 默认为false
 * @method $this maxLength(Int $length) 最大上传文件数, 0为无限
 *
 */
class Upload extends FormComponentDriver
{
    /**
     * @var string
     */
    protected $name = 'upload';

    /**
     * file类型
     */
    const TYPE_FILE = 'file';

    /**
     * image类型
     */
    const TYPE_IMAGE = 'image';

    /**
     * @var array
     */
    protected $props = [
        'maxLength' => 0,
        'type' => 'select',
        'uploadType' => self::TYPE_FILE,
        'headers' => [],
        'data' => [],
        'format' => [],
        'show-upload-list' => false
    ];

    /**
     * @var array
     */
    protected static $propsRule = [
        'uploadType' => 'string',
        'action' => 'string',
        'multiple' => 'boolean',
        'name' => 'string',
        'accept' => 'string',
        'maxSize' => 'int',
        'withCredentials' => 'boolean',
        'maxLength' => 'int'
    ];

    /**
     *
     */
    protected function init()
    {
        $this->name($this->field);
    }

    /**
     * 设置上传的请求头部
     *
     * @param array $headers
     * @return $this
     */
    public function headers(array $headers)
    {
        $this->props['headers'] = array_merge($this->props['headers'], $headers);
        return $this;
    }

    /**
     * 支持的文件类型，与 accept 不同的是，
     * format 是识别文件的后缀名，accept 为 input 标签原生的 accept 属性，
     * 会在选择文件时过滤，可以两者结合使用
     *
     * @param array $headers
     * @return $this
     */
    public function format(array $format)
    {
        $this->props['format'] = array_merge($this->props['format'], $format);
        return $this;
    }

    /**
     * 上传时附带的额外参数
     *
     * @param array $headers
     * @return $this
     */
    public function data(array $data)
    {
        $this->props['data'] = array_merge($this->props['data'], $data);
        return $this;
    }

    public function getPlaceHolder($pre = '请上传')
    {
        return parent::getPlaceHolder($pre);
    }

    /**
     * @param string|array $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    protected function getValidateHandler()
    {
        return Validate::arr();
    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'type' => $this->name,
            'field' => $this->field,
            'title' => $this->title,
            'value' => $this->value,
            'props' => (object)$this->props,
            'validate' => $this->validate,
            'col' => $this->col
        ];
    }
}