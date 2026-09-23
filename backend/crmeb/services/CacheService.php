<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services;

use think\facade\Cache as CacheThink;
use think\facade\Config;
use think\cache\TagSet;
use crmeb\utils\Lock;

/**
 * CRMEB 缓存服务
 * 封装 think\facade\Cache，统一附带 'crmeb' 标签以便批量清理
 * Class CacheService
 * @package crmeb\services
 */
class CacheService
{
    /**
     * 过期时间
     * @var int
     */
    protected static $expire;

    /**
     * 写入缓存
     * @param string $name 缓存名称
     * @param mixed $value 缓存值
     * @param int|null $expire 缓存时间，为0读取系统缓存时间
     */
    public static function set(string $name, $value, int $expire = 0, string $tag = 'crmeb')
    {
        try {
            return CacheThink::tag($tag)->set($name, $value, $expire);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 如果不存在则写入缓存
     * @param string $name
     * @param mixed $default
     * @param int|null $expire
     * @param string $tag
     * @return mixed|string|null
     */
    public static function remember(string $name, $default = '', int $expire = 0, string $tag = 'crmeb')
    {
        try {
            return CacheThink::tag($tag)->remember($name, $default, $expire);
        } catch (\Throwable $e) {
            try {
                if (is_callable($default)) {
                    return $default();
                } else {
                    return $default;
                }
            } catch (\Throwable $e) {
                return null;
            }
        }
    }

    /**
     * 读取缓存
     * @param string $name
     * @param mixed $default
     * @return mixed|string
     */
    public static function get(string $name, $default = '')
    {
        return CacheThink::get($name) ?? $default;
    }

    /**
     * 删除缓存
     * @param string $name
     * @return bool
     */
    public static function delete(string $name)
    {
        return CacheThink::delete($name);
    }

    /**
     * 清空缓存池
     *
     * 插件代码不得清理共享缓存池；插件自有缓存应使用 PluginCacheService。
     *
     * @param string $tag 缓存标签
     * @return bool
     */
    public static function clear(string $tag = 'crmeb')
    {
        self::assertGlobalClearAllowed();
        return CacheThink::tag($tag)->clear();
    }

    /**
     * 清空全部缓存
     *
     * @return bool
     */
    public static function clearAll()
    {
        self::assertGlobalClearAllowed();
        return CacheThink::clear();
    }

    /**
     * 清理经 PluginCacheService 验证的插件独立标签
     *
     * @param string $tag 插件缓存标签
     * @return bool
     */
    public static function clearPluginTag(string $tag): bool
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? '';
        if ($caller !== PluginCacheService::class || !str_starts_with($tag, 'plugin:')) {
            throw new \LogicException('插件缓存标签只能通过 PluginCacheService 清理');
        }
        return CacheThink::tag($tag)->clear();
    }

    /**
     * 禁止插件代码清理共享缓存池
     *
     * PHP 插件与核心运行在同一进程，无法形成操作系统级沙箱；这里对公开缓存契约
     * 做运行期故障域隔离，覆盖插件直接调用及经核心服务间接调用的场景。
     *
     * @return void
     */
    protected static function assertGlobalClearAllowed(): void
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            $class = ltrim((string)($frame['class'] ?? ''), '\\');
            if (str_starts_with($class, 'modules\\')
                || str_starts_with($class, 'driver\\')
                || self::isPluginFile((string)($frame['file'] ?? ''))) {
                throw new \LogicException('插件禁止清空全站缓存，请使用 PluginCacheService 管理插件自有缓存');
            }
        }
    }

    /**
     * 判断文件是否位于带 plugin.json 的插件目录
     *
     * @param string $file 调用文件
     * @return bool
     */
    protected static function isPluginFile(string $file): bool
    {
        if ($file === '') {
            return false;
        }
        $backendRoot = dirname(__DIR__, 2);
        $directory = dirname($file);
        // 只检查 backend 内的目录，避免核心调用向上越过 open_basedir 边界。
        while (str_starts_with($directory, $backendRoot . DIRECTORY_SEPARATOR)) {
            if (is_file($directory . DIRECTORY_SEPARATOR . 'plugin.json')) {
                return true;
            }
            if (in_array(basename($directory), ['modules', 'driver'], true)) {
                return false;
            }
            $directory = dirname($directory);
        }
        return false;
    }

    /**
     * 检查缓存是否存在
     * @param string $key
     * @return bool
     */
    public static function has(string $key)
    {
        try {
            return CacheThink::has($key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 指定缓存类型
     * @param string $type
     * @param string $tag
     * @return TagSet
     */
    public static function store(string $type = 'file', string $tag = 'crmeb')
    {
        return CacheThink::store($type)->tag($tag);
    }

    /**
     * 检查锁
     * @param string $key
     * @param int $timeout
     * @return bool
     */
    public static function setMutex(string $key, int $timeout = 10): bool
    {
        $curTime = time();
        $readMutexKey = "redis:mutex:{$key}";
        $mutexRes = CacheThink::store('redis')->handler()->setnx($readMutexKey, $curTime + $timeout);
        if ($mutexRes) {
            return true;
        }
        //就算意外退出，下次进来也会检查key，防止死锁
        $time = CacheThink::store('redis')->handler()->get($readMutexKey);
        if ($curTime > $time) {
            CacheThink::store('redis')->handler()->del($readMutexKey);
            return CacheThink::store('redis')->handler()->setnx($readMutexKey, $curTime + $timeout);
        }
        return false;
    }

    /**
     * 删除锁
     * @param string $key
     */
    public static function delMutex(string $key)
    {
        $readMutexKey = "redis:mutex:{$key}";
        CacheThink::store('redis')->handler()->del($readMutexKey);
    }

    /**
     * 数据库锁
     * @param $key
     * @param $fn
     * @param int $ex
     * @return mixed
     */
    public static function lock($key, $fn, int $ex = 6)
    {
        if (Config::get('cache.default') == 'file') {
            return $fn();
        }
        // Lock 类位于 crmeb\utils 命名空间，需显式 use 引入
        return app()->make(Lock::class)->exec($key, $fn, $ex);
    }
}
