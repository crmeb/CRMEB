<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\utils;

/**
 * 操作数组帮助类
 * Class Arr
 * @package crmeb\utils
 */
class Arr
{
    /**
     * 对数组增加默认值
     * @param array $keys
     * @return array
     */
    public static function getDefaultValue(array $keys, array $configList = [])
    {
        $value = [];
        foreach ($keys as $val) {
            if (is_array($val)) {
                $k = $val[0] ?? '';
                $v = $val[1] ?? '';
            } else {
                $k = $val;
                $v = '';
            }
            $value[$k] = $configList[$k] ?? $v;
        }
        return $value;
    }

    /**
     * 获取ivew菜单列表
     * @param array $data
     * @return array
     */
    public static function getMenuIviewList(array $data)
    {
        return Arr::toIviewUi(Arr::getTree($data));
    }

    /**
     * 转化iviewUi需要的key值
     * @param $data
     * @return array
     */
    public static function toIviewUi($data)
    {
        $newData = [];
        foreach ($data as $k => $v) {
            $temp = [];
            $temp['path'] = $v['menu_path'];
            $temp['title'] = $v['menu_name'];
            $temp['icon'] = $v['icon'];
            $temp['header'] = $v['header'];
            $temp['is_header'] = $v['is_header'];
            if ($v['is_show_path']) {
                $temp['auth'] = ['hidden'];
            }
            if (!empty($v['children'])) {
                $temp['children'] = self::toIviewUi($v['children']);
            }
            $newData[] = $temp;
        }
        return $newData;
    }

    /**
     * 获取树型菜单
     * @param $data
     * @param int $pid
     * @param int $level
     * @return array
     */
    public static function getTree($data, $pid = 0, $level = 1)
    {
        $childs = self::getChild($data, $pid, $level);
        $dataSort = array_column($childs, 'sort');
        array_multisort($dataSort, SORT_DESC, $childs);
        foreach ($childs as $key => $navItem) {
            $resChild = self::getTree($data, $navItem['id']);
            if (null != $resChild) {
                $childs[$key]['children'] = $resChild;
            }
        }
        return $childs;
    }

    /**
     * 获取子菜单
     * @param $arr
     * @param $id
     * @param $lev
     * @return array
     */
    private static function getChild(&$arr, $id, $lev)
    {
        $child = [];
        foreach ($arr as $k => $value) {
            if ($value['pid'] == $id) {
                $value['level'] = $lev;
                $child[] = $value;
            }
        }
        return $child;
    }

    /**
     * 格式化数据
     * @param array $array
     * @param $value
     * @param int $default
     * @return mixed
     */
    public static function setValeTime(array $array, $value, $default = 0)
    {
        foreach ($array as $item) {
            if (!isset($value[$item]))
                $value[$item] = $default;
            else if (is_string($value[$item]))
                $value[$item] = (float)$value[$item];
        }
        return $value;
    }

    /**
     * 获取二维数组中某个值的集合重新组成数组,并判断数组中的每一项是否为真
     * @param array $data
     * @param string $filed
     * @return array
     */
    public static function getArrayFilterValeu(array $data, string $filed)
    {
        return array_filter(array_unique(array_column($data, $filed)), function ($item) {
            if ($item) {
                return $item;
            }
        });
    }

    /**
     * 获取二维数组中最大的值
     * @param $arr
     * @param $field
     * @return int|string
     */
    public static function getArrayMax($arr, $field)
    {
        $temp = [];
        foreach ($arr as $k => $v) {
            $temp[] = $v[$field];
        }
        if (!count($temp)) return 0;
        $maxNumber = max($temp);
        foreach ($arr as $k => $v) {
            if ($maxNumber == $v[$field]) return $k;
        }
        return 0;
    }

    /**
     * 获取二维数组中最小的值
     * @param $arr
     * @param $field
     * @return int|string
     */
    public static function getArrayMin($arr, $field)
    {
        $temp = [];
        foreach ($arr as $k => $v) {
            $temp[] = $v[$field];
        }
        if (!count($temp)) return 0;
        $minNumber = min($temp);
        foreach ($arr as $k => $v) {
            if ($minNumber == $v[$field]) return $k;
        }
        return 0;
    }

    /**
     * 数组转字符串去重复
     * @param array $data
     * @return false|string[]
     */
    public static function unique(array $data)
    {
        return array_unique(explode(',', implode(',', $data)));
    }

    /**
     * 获取数组中去重复过后的指定key值
     * @param array $list
     * @param string $key
     * @return array
     */
    public static function getUniqueKey(array $list, string $key)
    {
        return array_unique(array_column($list, $key));
    }

    /**
     * 获取数组钟随机值
     * @param array $data
     * @return bool|mixed
     */
    public static function getArrayRandKey(array $data)
    {
        if (!$data) {
            return false;
        }
        $mun = rand(0, count($data));
        if (!isset($data[$mun])) {
            return self::getArrayRandKey($data);
        }
        return $data[$mun];
    }
}
