<?php

namespace crmeb\plugin;

use think\App as ThinkApp;

/**
 * 统一插件管理（仅管理用途）
 *
 * modules/ 是业务扩展层目录，存放所有插件代码。
 * 通过 plugin.json 声明元数据，由 ExtensionRegistry 负责注册。
 *
 * 职责：
 *   - 扫描 modules/ 下的 plugin.json，供后台管理列表展示
 *   - 通过 ExtensionRegistry 提供插件元数据查询
 *
 * 安装/启用/禁用后调用 resetCache() 使 ExtensionRegistry 重新发现插件。
 */
class PluginManager
{
    protected static ?ExtensionRegistry $registry = null;

    /**
     * 注册扩展发现
     *
     * 扫描 modules/\*\/plugin.json 注册到 ExtensionRegistry，
     * 供后台插件管理页面使用。
     * 不加载任何插件代码，不影响系统启动。
     *
     * @param ThinkApp $app
     * @param string $pluginsDir Plugins 目录路径
     * @return void
     */
    public static function register(ThinkApp $app, string $pluginsDir): void
    {
        $registry = new ExtensionRegistry($app);
        $registry->discover($pluginsDir);
        $app->bind(ExtensionRegistry::class, function () use ($registry) {
            return $registry;
        });
        static::$registry = $registry;
    }

    /**
     * 重置插件注册缓存
     *
     * 安装/卸载/启用/禁用插件后调用，使 ExtensionRegistry 重新扫描。
     *
     * @return void
     */
    public static function resetCache(): void
    {
        static::$registry = null;
    }

    /**
     * 获取 ExtensionRegistry 实例
     *
     * @return ExtensionRegistry|null
     */
    public static function getRegistry(): ?ExtensionRegistry
    {
        return static::$registry;
    }

    /**
     * 手动注册一个插件元数据到 Registry
     *
     * @param string $key          插件标识
     * @param string $type         插件类型
     * @param string|null $handlerClass 处理器类名
     * @param string $basePath     插件目录
     * @param array $meta          元数据
     * @return void
     */
    public static function registerExtension(
        string $key,
        string $type,
        ?string $handlerClass = null,
        string $basePath = '',
        array $meta = []
    ): void {
        if (static::$registry) {
            static::$registry->register($key, $type, $handlerClass, $basePath, $meta);
        }
    }
}