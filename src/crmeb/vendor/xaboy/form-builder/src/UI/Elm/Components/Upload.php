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
 * 上传组件
 * Class Upload
 *
 * @method $this uploadType(string $uploadType) 上传文件类型，可选值为 image（图片上传），file（文件上传）
 * @method $this action(string $action) 必选参数，上传的地址
 * @method $this multiple(bool $multiple) 是否支持多选文件
 * @method $this uploadName(string $name) 上传的文件字段名, 默认值: file
 * @method $this withCredentials(bool $withCredentials) 支持发送 cookie 凭证信息, 默认值: false
 * @method $this accept(string $accept) 接受上传的文件类型（thumbnail-mode 模式下此参数无效）
 * @method $this listType(string $listType) 文件列表的类型, 可选值: text/picture/picture-card, 默认值: text
 * @method $this autoUpload(bool $autoUpload) 是否在选取文件后立即进行上传, 默认值: true
 * @method $this disabled(bool $disabled) 是否禁用, 默认值: false
 * @method $this limit(float $limit) 最大允许上传个数
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
        'limit' => 0,
        'uploadType' => self::TYPE_FILE,
        'headers' => [],
        'data' => []
    ];

    protected static $propsRule = [
        'uploadType' => 'string',
        'action' => 'string',
        'multiple' => 'bool',
        'uploadName' => ['string', 'name'],
        'withCredentials' => 'bool',
        'accept' => 'string',
        'listType' => 'string',
        'autoUpload' => 'bool',
        'disabled' => 'bool',
        'limit' => 'float',
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
        return '请上传' . $this->title;
    }

    public function createValidate()
    {
        return $this->props['limit'] == 1 ? Elm::validateStr() : Elm::validateArr();
    }
}