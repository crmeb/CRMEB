<?php

namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 数据缓存
 * Class Express
 * @package app\models\system
 */
class Cache extends BaseModel
{
    use ModelTrait;

    const EXPIRE = 0;
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'cache';

    /**
     * 获取数据缓存
     * @param string $key
     * @param $default 默认值不存在则写入
     * @return mixed|null
     */
    public static function getDbCache(string $key, $default, int $expire = self::EXPIRE)
    {
        self::delectDeOverdueDbCache();
        $result = self::where('key', $key)->value('result');
        if ($result) {
            return json_decode($result, true);
        } else {
            if ($default instanceof \Closure) {
                // 获取缓存数据
                $value = $default();
                if ($value) {
                    self::setDbCache($key, $value, $expire);
                    return $value;
                }
            } else {
                self::setDbCache($key, $default, $expire);
                return $default;
            }
            return null;
        }

    }

    /**
     * 设置数据缓存存在则更新，没有则写入
     * @param string $key
     * @param string | array $result
     * @param int $expire
     * @return void
     */
    public static function setDbCache(string $key, $result, $expire = self::EXPIRE)
    {
        self::delectDeOverdueDbCache();
        $addTime = $expire ? time() + $expire : 0;
        if (self::be(['key' => $key])) {
            return self::where(['key' => $key])->update([
                'result' => json_encode($result),
                'expire_time' => $addTime,
                'add_time' => time()
            ]);
        } else {
            return self::create([
                'key' => $key,
                'result' => json_encode($result),
                'expire_time' => $addTime,
                'add_time' => time()
            ]);
        }
    }

    /**
     * 删除失效缓存
     */
    public static function delectDeOverdueDbCache()
    {
        self::where('expire_time', '<>', 0)->where('expire_time', '<', time())->delete();
    }

    /**
     * 删除某个缓存
     * @param string $key
     */
    public static function delectDbCache(string $key = '')
    {
        if ($key)
            return self::where('key', $key)->delete();
        else
            return self::delete();
    }

}