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


use FormBuilder\UI\Iview\Components\Cascader;

trait CascaderFactoryTrait
{
    /**
     * 多级联动组件
     *
     * @param string $field
     * @param string $title
     * @param array $value
     * @param string $type
     * @return Cascader
     */
    public static function cascader($field, $title, array $value = [], $type = Cascader::TYPE_OTHER)
    {
        $cascader = new Cascader($field, $title, $value);
        $cascader->type($type);
        return $cascader;
    }


    /**
     * 省市二级联动
     *
     * @param string $field
     * @param string $title
     * @param array|string $province
     * @param string $city
     * @return Cascader
     */
    public static function city($field, $title, $province = [], $city = '')
    {
        if (is_array($province))
            $value = $province;
        else
            $value = [(string)$province, (string)$city];

        $cascader = self::cascader($field, $title, $value, Cascader::TYPE_CITY);
        $cascader->jsData('province_city');
        return $cascader;
    }


    /**
     * 省市区三级联动
     *
     * @param string $field
     * @param string $title
     * @param array|string $province
     * @param string $city
     * @param string $area
     * @return Cascader
     */
    public static function cityArea($field, $title, $province = [], $city = '', $area = '')
    {
        if (is_array($province))
            $value = $province;
        else
            $value = [(string)$province, (string)$city, (string)$area];

        $cascader = self::cascader($field, $title, $value, Cascader::TYPE_CITY_AREA);
        $cascader->jsData('province_city_area');
        return $cascader;
    }
}