<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/05
 */

namespace crmeb\services;

use think\facade\Cache as CacheStatic;

/**
 * crmeb 缓存类
 * Class CacheService
 * @package crmeb\services
 */
class CacheService
{
    /**
     * 标签名
     * @var string
     */
    protected static $globalCacheName = '_cached_1515146130';

    /**
     * 写入缓存
     * @param string $name 缓存名称
     * @param mixed $value 缓存值
     * @param int $expire 缓存时间，为0读取系统缓存时间
     * @return bool
     */
    public static function set(string $name, $value, int $expire = null): bool
    {
        //这里不要去读取缓存配置，会导致死循环
        $expire = !is_null($expire) ? $expire : SystemConfigService::get('cache_config', null, true);
        if (!is_int($expire))
            $expire = (int)$expire;
        return self::handler()->set($name, $value, $expire);
    }

    /**
     * 如果不存在则写入缓存
     * @param string $name
     * @param bool $default
     * @return mixed
     */
    public static function get(string $name, $default = false, int $expire = null)
    {
        //这里不要去读取缓存配置，会导致死循环
        $expire = !is_null($expire) ? $expire : SystemConfigService::get('cache_config', null, true);
        if (!is_int($expire))
            $expire = (int)$expire;
        return self::handler()->remember($name, $default, $expire);
    }

    /**
     * 删除缓存
     * @param string $name
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function delete(string $name)
    {
        return CacheStatic::delete($name);
    }

    /**
     * 缓存句柄
     *
     * @return \think\cache\TagSet|CacheStatic
     */
    public static function handler()
    {
        return CacheStatic::tag(self::$globalCacheName);
    }

    /**
     * 清空缓存池
     * @return bool
     */
    public static function clear()
    {
        return self::handler()->clear();
    }
}