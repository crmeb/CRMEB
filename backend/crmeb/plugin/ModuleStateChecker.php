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

namespace crmeb\plugin;

use think\facade\Db;

/**
 * 模块安装状态检查器
 *
 * 批量查询 eb_module_install_log 中 status=1 AND enabled=1 的 module_key 集合，
 * 供 ModuleRegistry 在调用模块 register()/boot() 前判断是否应加载。
 *
 * 设计要点：
 * - 进程内静态缓存，一次请求只查一次库
 * - 表不存在/查询异常时兜底放行（返回 true），避免迁移未跑导致系统崩溃
 * - 提供 reload() 供安装/卸载/启用/禁用后清理缓存
 *
 * 位于 crmeb/ 层，仅依赖 think\facade\Db，不调任何 app/ 类，符合三层架构依赖铁律。
 */
class ModuleStateChecker
{
    /**
     * 活跃模块 key 集合（status=1 AND enabled=1）
     * null 表示尚未初始化，空数组表示已初始化但无活跃模块
     * @var array<string>|null
     */
    protected static ?array $activeKeys = null;

    /**
     * 安装日志表是否存在
     * null 表示尚未检测
     * @var bool|null
     */
    protected static ?bool $tableExists = null;

    /**
     * 路由文件路径 → module_key 映射缓存
     * value 为 null 表示该路径未能推导出 key（兜底放行）
     * @var array<string, string|null>
     */
    protected static array $routeFileKeyMap = [];

    /**
     * 判断模块是否活跃（已安装且已启用）
     *
     * 表不存在或查询异常时返回 true（兜底放行），避免迁移未跑导致系统崩溃。
     * 只有表存在且明确查到该 key 的 status=1 AND enabled=1 记录时才返回 true。
     *
     * @param string $moduleKey 模块标识（plugin.json 的 key / Installer::getKey()）
     * @return bool
     */
    public static function isActive(string $moduleKey): bool
    {
        // 表不存在时兜底放行，保证系统在迁移未跑的情况下仍可启动
        if (!self::tableExists()) {
            return true;
        }

        // 懒加载活跃 key 集合
        if (self::$activeKeys === null) {
            self::loadActiveKeys();
        }

        return in_array($moduleKey, self::$activeKeys, true);
    }

    /**
     * 判断驱动插件是否启用。
     *
     * 驱动目录只代表插件代码可被发现，不能代表插件已经安装。
     * 驱动与普通模块统一以 module_install_log 中 status=1、enabled=1 为唯一运行依据。
     */
    public static function isDriverActive(string $pluginId): bool
    {
        return self::isActive($pluginId);
    }

    /**
     * 判断数据归属方（owner_plugin_id）是否允许投放/生效
     *
     * 用于渠道、通知等按归属登记的数据：归属为空或 core = 核心数据直接放行；
     * 其余按 canonicalId 解析插件清单并校验模块启用状态，
     * 防止已禁用/已卸载插件登记的渠道类名（adapter_class）仍被调度发送。
     *
     * @param string $ownerPluginId owner_plugin_id 字段值（空/core/插件 canonicalId）
     * @return bool
     */
    public static function isOwnerActive(string $ownerPluginId): bool
    {
        if ($ownerPluginId === '' || $ownerPluginId === 'core') {
            return true;
        }
        $manifest = (new PluginManifestRepository(root_path()))->get($ownerPluginId);
        if (!$manifest) {
            return false;
        }
        return self::isActive($manifest->identity->legacyKey);
    }

    /**
     * 获取活跃模块 key 只读快照（status=1 AND enabled=1）
     *
     * 供上层（如 DIY 组件可用状态汇总）复用进程内缓存，
     * 同一请求内多次调用不会重复查库。
     *
     * @return array<string> 活跃模块 key 集合；安装日志表不存在时返回空集合
     */
    public static function activeKeys(): array
    {
        // 表不存在时按空集合处理（与 isActive 的兜底放行语义区分：
        // 快照用于能力判定，无表时无活跃模块，由调用方决定兜底策略）
        if (!self::tableExists()) {
            return [];
        }

        if (self::$activeKeys === null) {
            self::loadActiveKeys();
        }

        return self::$activeKeys ?? [];
    }

    /**
     * 清理缓存，供安装/卸载/启用/禁用后调用
     * 下次 isActive() 调用时会重新查库
     * @return void
     */
    public static function reload(): void
    {
        self::$activeKeys = null;
        self::$routeFileKeyMap = [];
        // tableExists 不清理：表结构不会在运行时变化，避免重复探测
    }

    /**
     * 判断路由文件对应的模块是否应加载路由（已安装且已启用）
     *
     * 通过路由文件路径反查模块根目录（dirname(dirname($routeFile))），
     * 读取 plugin.json 获取 key，再调用 isActive($key) 校验状态。
     *
     * 兜底放行（返回 true）的场景：
     * - 路径不符合 modules/{...}/route/{xxx}.php 结构（dirname 后无 plugin.json）
     * - plugin.json 不存在或解析失败
     * - plugin.json 无 key 字段
     * - isActive 内部表不存在时也会兜底放行
     *
     * 只有表存在、key 推导成功、且该 key 明确不在活跃集合时才返回 false。
     *
     * @param string $routeFile 路由文件绝对路径（如 /.../modules/product/normal/route/user.php）
     * @return bool
     */
    public static function isModuleRouteActive(string $routeFile): bool
    {
        // 缓存命中：key 为 null 表示推导失败，按兜底放行
        if (array_key_exists($routeFile, self::$routeFileKeyMap)) {
            $cachedKey = self::$routeFileKeyMap[$routeFile];
            return $cachedKey === null ? true : self::isActive($cachedKey);
        }

        // 推导模块根目录：路由文件路径 = {moduleRoot}/route/{xxx}.php
        // dirname($routeFile) = {moduleRoot}/route
        // dirname(dirname($routeFile)) = {moduleRoot}
        $moduleRoot = dirname(dirname($routeFile));
        $pluginJson = $moduleRoot . DIRECTORY_SEPARATOR . 'plugin.json';
        $key = null;
        if (is_file($pluginJson)) {
            $meta = json_decode((string)file_get_contents($pluginJson), true);
            if (is_array($meta) && !empty($meta['key'])) {
                $key = (string)$meta['key'];
            }
        }
        self::$routeFileKeyMap[$routeFile] = $key;

        // key 推导失败时放行，避免误伤未规范配置 plugin.json 的模块
        if ($key === null) {
            return true;
        }
        return self::isActive($key);
    }

    /**
     * 批量加载活跃模块 key
     * 一次性查出所有 status=1 AND enabled=1 的 module_key，避免逐模块查库
     * 查询失败时按空集合处理（严格白名单下未查到的模块不加载）
     * @return void
     */
    protected static function loadActiveKeys(): void
    {
        try {
            self::$activeKeys = Db::name('module_install_log')
                ->where('status', 1)
                ->where('enabled', 1)
                ->column('module_key');
        } catch (\Throwable $e) {
            // 查询失败按空集合处理
            self::$activeKeys = [];
        }
    }

    /**
     * 检测安装日志表是否存在
     * 用 limit 1 的轻量查询探测，异常即视为表不存在
     * @return bool
     */
    protected static function tableExists(): bool
    {
        if (self::$tableExists === null) {
            try {
                Db::name('module_install_log')->limit(1)->select();
                self::$tableExists = true;
            } catch (\Throwable $e) {
                self::$tableExists = false;
            }
        }
        return self::$tableExists;
    }
}
