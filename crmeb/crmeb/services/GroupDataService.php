<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/15
 */

namespace crmeb\services;


use app\admin\model\system\SystemGroupData;
use think\facade\Cache;

class GroupDataService
{
    /**
     * 获取单个组数据
     *
     * @param $config_name
     * @param int $limit
     * @return array|bool|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getGroupData($config_name, $limit = 0)
    {
        return SystemGroupData::getGroupData($config_name, $limit);
    }

    /**
     * 获取单个值
     *
     * @param $config_name
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getData($config_name, $limit = 0)
    {
        return SystemGroupData::getAllValue($config_name, $limit);
    }

    /**
     * 获取单个值 根据id
     *
     * @param $id
     * @param string $cacheA
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDataNumber($id)
    {
        return SystemGroupData::getDateValue($id);
    }
}