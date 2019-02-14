<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/15
 */

namespace service;


use app\admin\model\system\SystemGroupData;

class GroupDataService
{
    /**获取单个组数据
     * @param $config_name
     * @param int $limit
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public static function getGroupData($config_name,$limit = 0)
    {
        return SystemGroupData::getGroupData($config_name,$limit);
    }

    /**获取单个值
     * @param $config_name
     * @param int $limit
     * @return mixed
     */
    public static function getData($config_name,$limit = 0)
    {
        return SystemGroupData::getAllValue($config_name,$limit);
    }
}