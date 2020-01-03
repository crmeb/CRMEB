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
     * @param string $config_name 配置名称
     * @param int $limit 获取条数
     * @param bool $isCaChe 是否读取缓存
     * @return array
     */
    public static function getGroupData(string $config_name, $limit = 0, bool $isCaChe = false): array
    {
        $callable = function () use ($config_name, $limit) {
            try {
                $data = SystemGroupData::getGroupData($config_name, $limit);
                if (is_object($data))
                    $data = $data->toArray();
                return $data;
            } catch (\Exception $e) {
                return [];
            }
        };
        try {
            $cacheName = $limit ? "group_data_{$config_name}_{$limit}" : "data_{$config_name}";

            if ($isCaChe)
                return $callable();

            return CacheService::get($cacheName, $callable);

        } catch (\Throwable $e) {
            return $callable();
        }
    }

    /**
     * 获取单个值
     * @param string $config_name 配置名称
     * @param int $limit 截取多少条
     * @param bool $isCaChe 是否读取缓存
     * @return array
     */
    public static function getData(string $config_name, int $limit = 0, bool $isCaChe = false): array
    {
        $callable = function () use ($config_name, $limit) {
            try {
                $data = SystemGroupData::getAllValue($config_name, $limit);
                if (is_object($data))
                    $data = $data->toArray();
                return $data;
            } catch (\Exception $e) {
                return [];
            }
        };
        try {
            $cacheName = $limit ? "data_{$config_name}_{$limit}" : "data_{$config_name}";

            if ($isCaChe)
                return $callable();

            return CacheService::get($cacheName, $callable);

        } catch (\Throwable $e) {
            return $callable();
        }
    }

    /**
     * 根据id 获取单个值
     * @param int $id
     * @param bool $isCaChe 是否读取缓存
     * @return array
     */
    public static function getDataNumber(int $id, bool $isCaChe = false): array
    {
        $callable = function () use ($id) {
            try {
                $data = SystemGroupData::getDateValue($id);
                if (is_object($data))
                    $data = $data->toArray();
                return $data;
            } catch (\Exception $e) {
                return [];
            }
        };
        try {
            $cacheName = "data_number_{$id}";

            if ($isCaChe)
                return $callable();

            return CacheService::get($cacheName, $callable);

        } catch (\Throwable $e) {
            return $callable();
        }
    }
}