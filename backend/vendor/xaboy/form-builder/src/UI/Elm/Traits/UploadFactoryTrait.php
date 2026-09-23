<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\UI\Elm\Traits;


use FormBuilder\UI\Elm\Components\Upload;

trait UploadFactoryTrait
{
    /**
     * 上传组件
     *
     * @param string $field
     * @param string $title
     * @param string $action
     * @param string|array $value
     * @param string $type
     * @return Upload
     */
    public static function upload($field, $title, $action, $value = '', $type = Upload::TYPE_FILE)
    {
        $upload = new Upload($field, $title, $value);
        return $upload->uploadType($type)->action($action);
    }

    /**
     * 图片上传
     * value 为 Array类型
     *
     * @param string $field
     * @param string $title
     * @param string $action
     * @param array $value
     * @return Upload
     */
    public static function uploadImages($field, $title, $action, array $value = [])
    {
        $upload = self::upload($field, $title, $action, $value, Upload::TYPE_IMAGE);
        return $upload->accept('image/*');
    }

    /**
     * 文件上传
     * value 为 Array类型
     *
     * @param string $field
     * @param string $title
     * @param string $action
     * @param array $value
     * @return Upload
     */
    public static function uploadFiles($field, $title, $action, array $value = [])
    {
        return self::upload($field, $title, $action, $value, Upload::TYPE_FILE);
    }

    /**
     * 单图片上传
     * value 为  string类型
     *
     * @param string $field
     * @param string $title
     * @param string $action
     * @param string $value
     * @return Upload
     */
    public static function uploadImage($field, $title, $action, $value = '')
    {
        $upload = self::upload($field, $title, $action, (string)$value, Upload::TYPE_IMAGE);
        return $upload->accept('image/*')->limit(1);
    }

    /**
     * 单文件上传
     * value 为  string类型
     *
     * @param string $field
     * @param string $title
     * @param string $action
     * @param string $value
     * @return Upload
     */
    public static function uploadFile($field, $title, $action, $value = '')
    {
        $upload = self::upload($field, $title, $action, (string)$value, Upload::TYPE_FILE);
        return $upload->limit(1);
    }
}