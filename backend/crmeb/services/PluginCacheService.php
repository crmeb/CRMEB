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

declare(strict_types=1);

namespace crmeb\services;

use crmeb\plugin\PluginManifest;

/**
 * 插件隔离缓存服务
 *
 * 根据调用方所在插件目录读取 plugin.json，自动使用插件 canonical id 作为
 * key 命名空间和独立 tag，防止插件缓存互相覆盖或批量清理全站缓存。
 */
class PluginCacheService
{
    /** @var array<string, array{prefix: string, tag: string}> */
    protected static array $scopeCache = [];

    /**
     * 写入插件缓存
     *
     * @param string $name 缓存名称
     * @param mixed $value 缓存值
     * @param int $expire 缓存时间
     * @return mixed
     */
    public static function set(string $name, $value, int $expire = 0)
    {
        $scope = self::resolveScope();
        return CacheService::set($scope['prefix'] . self::normalizeName($name), $value, $expire, $scope['tag']);
    }

    /**
     * 插件缓存不存在时写入
     *
     * @param string $name 缓存名称
     * @param mixed $default 默认值或回源回调
     * @param int $expire 缓存时间
     * @return mixed
     */
    public static function remember(string $name, $default = '', int $expire = 0)
    {
        $scope = self::resolveScope();
        return CacheService::remember(
            $scope['prefix'] . self::normalizeName($name),
            $default,
            $expire,
            $scope['tag']
        );
    }

    /**
     * 读取插件缓存
     *
     * @param string $name 缓存名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get(string $name, $default = '')
    {
        $scope = self::resolveScope();
        return CacheService::get($scope['prefix'] . self::normalizeName($name), $default);
    }

    /**
     * 判断插件缓存是否存在
     *
     * @param string $name 缓存名称
     * @return bool
     */
    public static function has(string $name): bool
    {
        $scope = self::resolveScope();
        return CacheService::has($scope['prefix'] . self::normalizeName($name));
    }

    /**
     * 删除单个插件缓存
     *
     * @param string $name 缓存名称
     * @return bool
     */
    public static function delete(string $name): bool
    {
        $scope = self::resolveScope();
        return CacheService::delete($scope['prefix'] . self::normalizeName($name));
    }

    /**
     * 清空当前插件的全部缓存
     *
     * @return bool
     */
    public static function clear(): bool
    {
        $scope = self::resolveScope();
        return CacheService::clearPluginTag($scope['tag']);
    }

    /**
     * 从调用栈定位当前插件并生成隔离命名空间
     *
     * @return array{prefix: string, tag: string}
     */
    protected static function resolveScope(): array
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            $file = (string)($frame['file'] ?? '');
            if ($file === '') {
                continue;
            }
            $normalized = str_replace('\\', '/', $file);
            if (!str_contains($normalized, '/modules/') && !str_contains($normalized, '/driver/')) {
                continue;
            }
            if (isset(self::$scopeCache[$normalized])) {
                return self::$scopeCache[$normalized];
            }

            [$manifestFile, $rootPath] = self::findManifest($file);
            $manifest = PluginManifest::fromFile($manifestFile, $rootPath);
            $pluginId = $manifest->identity->canonicalId();
            return self::$scopeCache[$normalized] = [
                'prefix' => "plugin:{$pluginId}:",
                'tag' => "plugin:{$pluginId}",
            ];
        }

        throw new \LogicException('PluginCacheService 只能由包含有效 plugin.json 的插件代码调用');
    }

    /**
     * 向上查找调用方所属插件清单
     *
     * @param string $file 插件调用文件
     * @return array{0: string, 1: string}
     */
    protected static function findManifest(string $file): array
    {
        $directory = dirname($file);
        while ($directory !== dirname($directory)) {
            $manifestFile = $directory . DIRECTORY_SEPARATOR . 'plugin.json';
            if (is_file($manifestFile)) {
                $rootPath = self::findBackendRoot($directory);
                return [$manifestFile, $rootPath];
            }
            $directory = dirname($directory);
        }

        throw new \LogicException("插件调用文件不属于有效插件目录：{$file}");
    }

    /**
     * 根据插件目录定位 backend 根目录
     *
     * @param string $directory 插件目录
     * @return string
     */
    protected static function findBackendRoot(string $directory): string
    {
        $current = $directory;
        while ($current !== dirname($current)) {
            $name = basename($current);
            if ($name === 'modules' || $name === 'driver') {
                return rtrim(dirname($current), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }
            $current = dirname($current);
        }

        throw new \LogicException("无法定位插件 backend 根目录：{$directory}");
    }

    /**
     * 校验插件缓存名称
     *
     * @param string $name 缓存名称
     * @return string
     */
    protected static function normalizeName(string $name): string
    {
        $name = trim($name);
        if ($name === '' || preg_match('/[\x00-\x1F\x7F]/', $name)) {
            throw new \InvalidArgumentException('插件缓存名称不能为空或包含控制字符');
        }
        return $name;
    }
}
