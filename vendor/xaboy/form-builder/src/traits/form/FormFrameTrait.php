<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\traits\form;


use FormBuilder\components\Frame;

/**
 * Class FormFrameTrait
 * @package FormBuilder\traits\form
 */
trait FormFrameTrait
{
    /**
     * @param $field
     * @param $title
     * @param $src
     * @param string $value
     * @param string $type
     * @return Frame
     */
    public static function frame($field, $title, $src, $value = '', $type = Frame::TYPE_INPUT)
    {
        $length = is_array($value) ? 0 : 1;
        $frame = new Frame($field, $title, $value);
        $frame->maxLength($length)->src($src)->type($type);
        return $frame;
    }

    /**
     * @param $field
     * @param $title
     * @param $src
     * @param array $value
     * @return Frame
     */
    public static function frameInputs($field, $title, $src, array $value = [])
    {
        return self::frame($field, $title, $src, $value);
    }

    /**
     * @param $field
     * @param $title
     * @param $src
     * @param array $value
     * @return Frame
     */
    public static function frameFiles($field, $title, $src, array $value = [])
    {
        return self::frame($field, $title, $src, $value, Frame::TYPE_FILE);
    }

    /**
     * @param $field
     * @param $title
     * @param $src
     * @param array $value
     * @return Frame
     */
    public static function frameImages($field, $title, $src, array $value = [])
    {
        return self::frame($field, $title, $src, $value, Frame::TYPE_IMAGE);
    }

    /**
     * @param $field
     * @param $title
     * @param $src
     * @param string $value
     * @return Frame
     */
    public static function frameInputOne($field, $title, $src, $value = '')
    {
        return self::frame($field, $title, $src, (string)$value);
    }

    /**
     * @param $field
     * @param $title
     * @param $src
     * @param string $value
     * @return Frame
     */
    public static function frameFileOne($field, $title, $src, $value = '')
    {
        return self::frame($field, $title, $src, (string)$value, Frame::TYPE_FILE);
    }

    /**
     * @param $field
     * @param $title
     * @param $src
     * @param string $value
     * @return Frame
     */
    public static function frameImageOne($field, $title, $src, $value = '')
    {
        return self::frame($field, $title, $src, (string)$value, Frame::TYPE_IMAGE);
    }
}