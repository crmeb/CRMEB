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

namespace FormBuilder\UI\Iview\Traits;


use FormBuilder\UI\Iview\Components\Frame;

trait FrameFactoryTrait
{
    /**
     * 框架组件
     *
     * @param string $field
     * @param string $title
     * @param string $src
     * @param string|array $value
     * @param string $type
     * @return Frame
     */
    public static function frame($field, $title, $src, $value = [], $type = Frame::TYPE_INPUT)
    {
        $length = is_array($value) ? 0 : 1;
        $frame = new Frame($field, $title, $value);
        return $frame->maxLength($length)->src($src)->type($type);
    }

    /**
     * 使用input类型显示,多选
     * value为Array类型
     *
     * @param string $field
     * @param string $title
     * @param string $src
     * @param array $value
     * @return Frame
     */
    public static function frameInputs($field, $title, $src, array $value = [])
    {
        return self::frame($field, $title, $src, $value);
    }

    /**
     * 使用文件类型显示,多选
     * value为Array类型
     *
     * @param string $field
     * @param string $title
     * @param string $src
     * @param array $value
     * @return Frame
     */
    public static function frameFiles($field, $title, $src, array $value = [])
    {
        return self::frame($field, $title, $src, $value, Frame::TYPE_FILE);
    }

    /**
     * 使用文件类型显示,多选
     * value为Array类型
     *
     * @param string $field
     * @param string $title
     * @param string $src
     * @param array $value
     * @return Frame
     */
    public static function frameImages($field, $title, $src, array $value = [])
    {
        return self::frame($field, $title, $src, $value, Frame::TYPE_IMAGE);
    }

    /**
     * 使用input类型显示,单选
     * value为string类型
     *
     * @param string $field
     * @param string $title
     * @param string $src
     * @param string $value
     * @return Frame
     */
    public static function frameInput($field, $title, $src, $value = '')
    {
        return self::frame($field, $title, $src, (string)$value);
    }

    /**
     * 使用文件类型显示,单选
     * value为string类型
     *
     * @param string $field
     * @param string $title
     * @param string $src
     * @param string $value
     * @return Frame
     */
    public static function frameFile($field, $title, $src, $value = '')
    {
        return self::frame($field, $title, $src, (string)$value, Frame::TYPE_FILE);
    }

    /**
     * 使用文件类型显示,单选
     * value为string类型
     *
     * @param string $field
     * @param string $title
     * @param string $src
     * @param string $value
     * @return Frame
     */
    public static function frameImage($field, $title, $src, $value = '')
    {
        return self::frame($field, $title, $src, (string)$value, Frame::TYPE_IMAGE);
    }
}