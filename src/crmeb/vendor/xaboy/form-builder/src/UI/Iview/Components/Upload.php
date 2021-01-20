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
 * 上传组件
 * Class Upload
 *
 * @method $this uploadType(string $uploadType) 上传文件类型，可选值为 image（图片上传），file（文件上传）
 * @method $this action(string $action) 上传的地址
 * @method $this multiple(bool $bool) 是否支持多选文件
 * @method $this uploadName(string $name) 上传的文件字段名
 * @method $this accept(string $accept) 接受上传的文件类型
 * @method $this maxSize(int $size) 文件大小限制，单位 kb
 * @method $this withCredentials(bool $bool) 支持发送 cookie 凭证信息, 默认为false
 * @method $this maxLength(Int $length) 最大上传文件数, 0为无限
 *
 */
class Upload extends FormComponent
{
    /**
     * file类型
     */
    const TYPE_FILE = 'file';

    /**
     * image类型
     */
    const TYPE_IMAGE = 'image';


    protected $defaultProps = [
        'maxLength' => 0,
        'type' => 'select',
        'uploadType' => self::TYPE_FILE,
        'headers' => [],
        'data' => [],
        'format' => [],
        'show-upload-list' => false
    ];

    protected static $propsRule = [
        'uploadType' => 'string',
        'action' => 'string',
        'multiple' => 'bool',
        'uploadName' => ['string', 'name'],
        'accept' => 'string',
        'maxSize' => 'int',
        'withCredentials' => 'bool',
        'maxLength' => 'int'
    ];

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
     * @param array $format
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
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        $this->props['data'] = array_merge($this->props['data'], $data);
        return $this;
    }

    protected function getPlaceHolder()
    {
        return '请上传' . $this->field;
    }

    public function createValidate()
    {
        return $this->props['maxLength'] == 1 ? Iview::validateStr() : Iview::validateArr();
    }
}