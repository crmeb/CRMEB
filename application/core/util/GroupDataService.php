<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/15
 */

namespace app\core\util;


use app\admin\model\system\SystemGroupData;
use think\Cache;

class GroupDataService
{
    protected static $isCaChe=true;

    protected static $expire=60;

    /**获取单个组数据
     * @param $config_name
     * @param int $limit
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public static function getGroupData($config_name,$limit = 0)
    {
        $cacheName=$limit ? $config_name.'_'.$limit : $config_name;
        if(Cache::has($cacheName)){
            return Cache::get($cacheName);
        }else {
            $data=SystemGroupData::getGroupData($config_name, $limit);
            if(self::$isCaChe) Cache::set($cacheName,$data,self::$expire);
            return $data;
        }
    }

    /**获取单个值
     * @param $config_name
     * @param int $limit
     * @return mixed
     */
    public static function getData($config_name,$limit = 0)
    {
        $cacheName=$limit ? $config_name.'_'.$limit : $config_name;
        if(Cache::has($cacheName)){
            return Cache::get($cacheName);
        }else{
            $data=SystemGroupData::getAllValue($config_name,$limit);
            if(self::$isCaChe) Cache::set($cacheName,$data,self::$expire);
            return $data;
        }
    }

    /**
     * TODO 获取单个值 根据id
     * @param $id
     * @return mixed
     */
    public static function getDataNumber($id,$cacheA='eb_data_')
    {
        $cacheName=$cacheA.$id;
        if(Cache::has($cacheName)){
            return Cache::get($cacheName);
        }else {
            $data=SystemGroupData::getDateValue($id);
            if(self::$isCaChe) Cache::set($cacheName,$data,self::$expire);
            return $data;
        }
    }
}