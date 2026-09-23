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

namespace app\services\system\module;

use crmeb\interfaces\ModuleInstallerInterface;
use crmeb\plugin\ModuleStateChecker;
use crmeb\plugin\ManifestDriverInstaller;
use crmeb\plugin\PluginManifest;
use crmeb\plugin\PluginManifestRepository;
use crmeb\plugin\PluginDependencyResolver;
use crmeb\services\driver\DriverRegistry;
use app\services\message\pipeline\NoticeSeedServices;
use think\facade\Db;
use think\App;

/**
 * 模块安装调度器
 * 统一管理插件的安装/卸载/启用/禁用
 *
 * 扫描范围：
 * - modules 平铺布局（如 modules/distribution/Installer.php）
 * - modules 分组布局（如 modules/product/normal/Installer.php）
 *
 * 本类为模块生命周期管理器，Db::table/Db::transaction 用于操作模块安装日志、
 * 菜单等系统表，保留直接调用。
 */
class ModuleInstaller
{
    /**
     * @var App
     */
    protected App $app;

    /**
     * 安装器实例缓存，以 canonical id 为索引，避免跨类型插件使用相同 legacy key 时覆盖。
     * @var array<string, \crmeb\interfaces\ModuleInstallerInterface>
     */
    protected array $installers = [];

    /**
     * @var string 表前缀
     */
    protected string $prefix;

    /** @var array<string, string> legacy key/path/canonical id => canonical id */
    protected array $identifierMap = [];

    /** @var array<string, string[]> 存在歧义的旧标识 */
    protected array $ambiguousIdentifiers = [];

    /**
     * canonical id 到 plugin.json 元数据的映射
     * 用于快速读取插件元信息，避免重复文件IO
     * @var array<string, array>
     */
    protected array $keyToMetaMap = [];

    /**
     * 插件类型映射缓存（前端type → 后端type）
     * 从各目录 plugin.json 中动态扫描建立
     * @var array<string, string>|null
     */
    protected ?array $typeMapping = null;

    protected ?PluginDependencyResolver $dependencyResolver = null;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->prefix = (string) config('database.connections.mysql.prefix', 'eb_');
    }

    /**
     * 获取所有已注册的 Installer
     * 以 plugin.json 为入口扫描各插件目录
     * @return array<string, ModuleInstallerInterface>
     */
    public function getInstallers(): array
    {
        if (!empty($this->installers)) {
            return $this->installers;
        }

        $repository = new PluginManifestRepository($this->app->getRootPath());
        foreach ($repository->all() as $manifest) {
            $this->registerFromManifest($manifest);
        }

        return $this->installers;
    }

    /**
     * 从 plugin.json 注册插件安装器
     *
     * modules/ 目录是插件业务代码存放目录，包含完整的 Controller/Service/DAO/Model 等。
     * 加载策略：
     *   a) 通过 plugin.json 的 path 字段推导 namespace（如 modules/product/normal → \modules\product\normal\Installer）
     *   b) 反射加载 modules/{name}/Installer.php（适用于所有模块）
     *
     * @param PluginManifest $manifest 已校验清单
     */
    protected function registerFromManifest(PluginManifest $manifest): void
    {
        $meta = $manifest->meta;
        $pluginDir = $manifest->basePath;
        $installerFile = $pluginDir . '/Installer.php';

        // 方式 b：优先反射加载 plugin.json 同目录下的 Installer.php
        // Installer.php 是插件的安装/卸载入口，包含完整的安装/卸载逻辑
        if (is_file($installerFile)) {
            $instance = $this->loadInstallerByReflection($installerFile);
            if ($instance) {
                $this->registerInstaller($manifest, $instance);
                return;
            }
        }

        // 方式 a：通过 plugin.json 的 path 字段推导 namespace（fallback）
        // 当 Installer.php 不存在于插件目录时使用
        if (!empty($meta['path'])) {
            $pathClass = '\\' . str_replace('/', '\\', $meta['path']) . '\\Installer';
            if (class_exists($pathClass)) {
                $instance = new $pathClass();
                if ($instance instanceof ModuleInstallerInterface) {
                    $this->registerInstaller($manifest, $instance);
                    return;
                }
            }
        }

        // 纯配置型驱动没有独立表和菜单，仍通过通用 Installer 接入完整生命周期。
        if ($manifest->identity->kind === 'driver') {
            $this->registerInstaller($manifest, new ManifestDriverInstaller($manifest));
        }
    }

    protected function registerInstaller(PluginManifest $manifest, ModuleInstallerInterface $instance): void
    {
        $canonicalId = $manifest->identity->canonicalId();
        if (isset($this->installers[$canonicalId])) {
            throw new \RuntimeException("插件 Installer 复合标识重复: {$canonicalId}");
        }

        $this->installers[$canonicalId] = $instance;
        $this->keyToMetaMap[$canonicalId] = $manifest->meta;
        $this->registerIdentifier($canonicalId, $canonicalId);
        $this->registerIdentifier($manifest->path, $canonicalId);
        $this->registerIdentifier($manifest->identity->legacyKey, $canonicalId);
        $this->registerIdentifier($instance->getKey(), $canonicalId);
    }

    protected function registerIdentifier(string $identifier, string $canonicalId): void
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return;
        }
        if (isset($this->ambiguousIdentifiers[$identifier])) {
            if (!in_array($canonicalId, $this->ambiguousIdentifiers[$identifier], true)) {
                $this->ambiguousIdentifiers[$identifier][] = $canonicalId;
            }
            return;
        }
        if (isset($this->identifierMap[$identifier]) && $this->identifierMap[$identifier] !== $canonicalId) {
            $this->ambiguousIdentifiers[$identifier] = [$this->identifierMap[$identifier], $canonicalId];
            unset($this->identifierMap[$identifier]);
            return;
        }
        $this->identifierMap[$identifier] = $canonicalId;
    }

    /**
     * 通过反射查找实现了 ModuleInstallerInterface 且文件路径匹配的类
     *
     * `class_exists()` 触发 autoload 已加载了文件，
     * 只需遍历所有已声明类，按文件路径 + 接口实现双重校验即可找到。
     * 
     * 注意：某些插件的 Installer.php 命名空间位于 modules/ 下时，
     * class_exists() 在非正确的 autoload 路径下会触发 require，
     * 而 modules/ 下已存在的同命名空间文件会导致类重复声明错误。
     * 因此不执行 require，直接遍历已声明类。
     */
    protected function loadInstallerByReflection(string $installerFile): ?ModuleInstallerInterface
    {
        $realFile = realpath($installerFile);
        if (!$realFile) {
            return null;
        }

        // 先检查该文件是否已被加载（通过已声明的类的文件名匹配）
        foreach (get_declared_classes() as $className) {
            try {
                $ref = new \ReflectionClass($className);
                if ($ref->isAbstract() || $ref->isInterface() || $ref->isTrait()) {
                    continue;
                }
                if (realpath($ref->getFileName()) === $realFile
                    && $ref->implementsInterface(ModuleInstallerInterface::class)) {
                    /** @var ModuleInstallerInterface $instance */
                    return $ref->newInstance();
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        // 文件尚未加载，尝试 require 后再查找
        // 使用 include_once 避免类重复声明错误
        include_once $installerFile;

        // 再次遍历已声明类
        foreach (get_declared_classes() as $className) {
            try {
                $ref = new \ReflectionClass($className);
                if ($ref->isAbstract() || $ref->isInterface() || $ref->isTrait()) {
                    continue;
                }
                if (realpath($ref->getFileName()) === $realFile
                    && $ref->implementsInterface(ModuleInstallerInterface::class)) {
                    /** @var ModuleInstallerInterface $instance */
                    return $ref->newInstance();
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * 解析 moduleKey，支持通过 plugin.json 中的 path 查找
     * @return string 解析后的真实 key
     */
    protected function resolveKey(string $moduleKey): string
    {
        if (isset($this->ambiguousIdentifiers[$moduleKey])) {
            throw new \RuntimeException(
                "插件标识存在歧义，请使用复合标识: {$moduleKey}; 候选: "
                . implode(', ', $this->ambiguousIdentifiers[$moduleKey])
            );
        }
        if (isset($this->installers[$moduleKey])) {
            return $moduleKey;
        }
        if (isset($this->identifierMap[$moduleKey])) {
            return $this->identifierMap[$moduleKey];
        }
        return $moduleKey;
    }

    /**
     * 安装指定模块
     *
     * 事务说明：Installer 内部已经根据是否包含 DDL 自行处理了事务边界
     * （DDL 在事务外，DML 在事务内）。本方法不再额外包裹事务，避免外层事务
     * 与 Installer 内部的 commit/rollback 产生冲突。
     */
    public function install(string $moduleKey): array
    {
        $this->getInstallers();
        $moduleKey = $this->resolveKey($moduleKey);
        if (!isset($this->installers[$moduleKey])) {
            throw new \RuntimeException("插件不存在: {$moduleKey}");
        }

        $this->getDependencyResolver()->assertCanInstall($moduleKey);
        $result = $this->installers[$moduleKey]->install();
        // 安装状态变更后清理缓存，保证 ModuleRegistry 下次读取最新状态
        ModuleStateChecker::reload();
        DriverRegistry::resetCache();
        return $result;
    }

    /**
     * Execute the versioned migration scripts shipped with an upgrade package.
     * Files must be named upgrades/{from}_to_{to}.php and return a callable.
     * The callable is intentionally loaded from the staging directory so the
     * currently running plugin classes are not replaced during this request.
     */
    public function runUpgradeMigrations(string $moduleKey, string $upgradeDir, string $targetVersion): array
    {
        $this->getInstallers();
        $moduleKey = $this->resolveKey($moduleKey);
        if (!isset($this->installers[$moduleKey])) {
            throw new \RuntimeException("插件不存在: {$moduleKey}");
        }

        $installer = $this->installers[$moduleKey];
        $row = Db::table($this->prefix . 'module_install_log')
            ->where('module_key', $installer->getKey())
            ->where('status', 1)
            ->find();
        if (!$row) {
            throw new \RuntimeException('插件尚未安装，不能升级');
        }

        $fromVersion = (string) ($row['version'] ?? '0.0.0');
        if (!version_compare($targetVersion, $fromVersion, '>')) {
            throw new \RuntimeException("新版本 {$targetVersion} 必须高于当前版本 {$fromVersion}");
        }

        $scripts = [];
        foreach (glob(rtrim($upgradeDir, '/\\') . '/*.php') ?: [] as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (preg_match('/^(.+)_to_(.+)$/', $name, $matches)) {
                $scripts[$matches[1] . '|' . $matches[2]] = $file;
            }
        }

        $steps = [];
        $version = $fromVersion;
        $guard = 0;
        while (version_compare($version, $targetVersion, '<')) {
            if (++$guard > 100) {
                throw new \RuntimeException('插件升级路径过长或存在循环');
            }
            $next = null;
            $script = null;
            foreach ($scripts as $key => $file) {
                [$from, $to] = explode('|', $key, 2);
                if ($from === $version && version_compare($to, $version, '>')
                    && version_compare($to, $targetVersion, '<=')) {
                    if ($next === null || version_compare($to, $next, '<')) {
                        $next = $to;
                        $script = $file;
                    }
                }
            }
            if ($next === null) {
                // Code-only upgrades do not need a migration file. If migration
                // files exist but do not form a complete chain, fail explicitly.
                if ($scripts !== []) {
                    throw new \RuntimeException("缺少 {$version} 到 {$targetVersion} 的升级脚本");
                }
                break;
            }

            $migration = include $script;
            if (!is_callable($migration)) {
                throw new \RuntimeException("升级脚本必须返回可调用对象: {$script}");
            }
            $migration();
            $steps[] = basename($script);
            $version = $next;
        }

        return [
            'from_version' => $fromVersion,
            'to_version' => $targetVersion,
            'steps' => $steps,
        ];
    }

    /** 更新已安装插件的版本号。 */
    public function updateInstalledVersion(string $moduleKey, string $version): void
    {
        $this->getInstallers();
        $moduleKey = $this->resolveKey($moduleKey);
        if (!isset($this->installers[$moduleKey])) {
            throw new \RuntimeException("插件不存在: {$moduleKey}");
        }
        Db::table($this->prefix . 'module_install_log')
            ->where('module_key', $this->installers[$moduleKey]->getKey())
            ->where('status', 1)
            ->update(['version' => $version]);
    }

    /**
     * 获取全部必须随系统安装的模块。
     *
     * required 只读取 plugin.json 顶层字段，避免把驱动 config_fields 中的
     * required（表示配置项必填）误认为插件必须安装。
     *
     * @return array<string, array> [Installer::getKey() => plugin.json 元数据]
     */
    public function getRequiredPlugins(): array
    {
        $this->getInstallers();
        $required = [];
        $repository = new PluginManifestRepository($this->app->getRootPath());
        foreach ($repository->modules() as $manifest) {
            if (!$manifest->required) {
                continue;
            }
            $resolvedKey = $manifest->identity->canonicalId();
            if (!isset($this->installers[$resolvedKey])) {
                throw new \RuntimeException("必需插件缺少 Installer: {$resolvedKey} ({$manifest->path})");
            }
            if (isset($required[$resolvedKey])) {
                throw new \RuntimeException("必需插件标识重复: {$resolvedKey}");
            }
            $required[$resolvedKey] = $manifest->meta;
        }

        return $required;
    }

    /**
     * 获取新装向导的逐项安装计划，保持必需模块、默认模块、默认驱动的执行顺序。
     *
     * @return array<int, array{key: string, name: string, label: string}>
     */
    public function getFreshInstallPlan(): array
    {
        $plan = [];
        foreach (['必需插件' => $this->getRequiredPlugins(), '默认插件' => $this->getDefaultInstallPlugins()] as $label => $plugins) {
            foreach ($plugins as $key => $meta) {
                $plan[] = ['key' => $key, 'name' => $meta['name'] ?? $key, 'label' => $label];
            }
        }
        $repository = new PluginManifestRepository($this->app->getRootPath());
        foreach ($repository->drivers() as $manifest) {
            if ($manifest->defaultInstall) {
                $plan[] = ['key' => $manifest->identity->canonicalId(), 'name' => $manifest->name, 'label' => '驱动插件'];
            }
        }
        return $plan;
    }

    /**
     * 安装并启用全部 required=true 的模块。
     *
     * Installer::install() 本身幂等，安装向导中断后重试不会重复创建表、菜单或种子数据。
     *
     * @return array<string, array> [模块 key => 安装结果]
     */
    public function installRequired(): array
    {
        $results = [];
        foreach ($this->getRequiredPlugins() as $moduleKey => $meta) {
            $this->getDependencyResolver()->assertCanInstall($moduleKey);
            $result = $this->installers[$moduleKey]->install();
            $results[$moduleKey] = $result + [
                'name' => $meta['name'] ?? $moduleKey,
            ];
        }

        ModuleStateChecker::reload();
        DriverRegistry::resetCache();
        return $results;
    }

    /**
     * 获取全部默认随系统安装的模块。
     *
     * default_install 与 required 的区别：两者都在全新安装时自动安装，
     * 但 default_install 的模块允许在后台卸载/禁用，required 则为核心保护模块不可卸载。
     * 只读取 plugin.json 顶层字段。
     *
     * @return array<string, array> [Installer::getKey() => plugin.json 元数据]
     */
    public function getDefaultInstallPlugins(): array
    {
        $this->getInstallers();
        $defaults = [];
        $repository = new PluginManifestRepository($this->app->getRootPath());
        foreach ($repository->modules() as $manifest) {
            if (!$manifest->defaultInstall) {
                continue;
            }
            $resolvedKey = $manifest->identity->canonicalId();
            if (!isset($this->installers[$resolvedKey])) {
                throw new \RuntimeException("默认安装插件缺少 Installer: {$resolvedKey} ({$manifest->path})");
            }
            if (isset($defaults[$resolvedKey])) {
                throw new \RuntimeException("默认安装插件标识重复: {$resolvedKey}");
            }
            $defaults[$resolvedKey] = $manifest->meta;
        }

        return $defaults;
    }

    /**
     * 安装全部 default_install=true 的模块（默认安装、可卸载）。
     *
     * Installer::install() 本身幂等，安装向导中断后重试不会重复创建表、菜单或种子数据。
     *
     * @return array<string, array> [模块 key => 安装结果]
     */
    public function installDefault(): array
    {
        $results = [];
        foreach ($this->getDefaultInstallPlugins() as $moduleKey => $meta) {
            $this->getDependencyResolver()->assertCanInstall($moduleKey);
            $result = $this->installers[$moduleKey]->install();
            $results[$moduleKey] = $result + [
                'name' => $meta['name'] ?? $moduleKey,
            ];
        }

        ModuleStateChecker::reload();
        DriverRegistry::resetCache();
        return $results;
    }

    /**
     * 全新安装时通过真实 Installer 安装标记 default_install=true 的驱动。
     *
     * 与模块的 default_install 语义一致：默认随系统安装、可卸载。
     * 未标记 default_install 的驱动（多为需单独申请密钥的第三方服务）不默认安装，
     * 由后台按需手动安装。驱动 Installer 会同时写入生命周期日志和 eb_driver 字典。
     *
     * @return array<string, array>
     */
    public function installBundledDrivers(): array
    {
        $results = [];
        $repository = new PluginManifestRepository($this->app->getRootPath());
        foreach ($repository->drivers() as $manifest) {
            if (!$manifest->defaultInstall) {
                continue;
            }
            $pluginId = $manifest->identity->canonicalId();
            $results[$pluginId] = $this->install($pluginId) + [
                'name' => $manifest->name,
            ];
        }
        return $results;
    }

    /**
     * 卸载指定模块
     *
     * 事务说明：Installer 内部已经根据是否包含 DDL 自行处理了事务边界
     * （DDL 在事务外，DML 在事务内）。本方法不再额外包裹事务，避免外层事务
     * 与 Installer 内部的 commit/rollback 产生冲突。
     */
    public function uninstall(string $moduleKey): array
    {
        $this->getInstallers();
        $moduleKey = $this->resolveKey($moduleKey);
        if (!isset($this->installers[$moduleKey])) {
            throw new \RuntimeException("插件不存在: {$moduleKey}");
        }
        // 核心模块保护：plugin.json 中 required=true 的模块不允许卸载
        $this->assertNotRequired($moduleKey);
        $this->getDependencyResolver()->assertCanUninstall($moduleKey);

        $result = $this->installers[$moduleKey]->uninstall();
        // 卸载后清理缓存，保证 ModuleRegistry 下次读取最新状态
        ModuleStateChecker::reload();
        DriverRegistry::resetCache();
        return $result;
    }

    /**
     * 启用插件
     */
    public function enable(string $moduleKey): array
    {
        $this->getInstallers();
        $moduleKey = $this->resolveKey($moduleKey);
        if (!isset($this->installers[$moduleKey])) {
            throw new \RuntimeException("插件不存在: {$moduleKey}");
        }
        /** @var \crmeb\module\BaseModuleInstaller $installer */
        $installer = $this->installers[$moduleKey];
        if (!$installer->isInstalled()) {
            throw new \RuntimeException("插件未安装，请先安装");
        }

        $this->getDependencyResolver()->assertCanEnable($moduleKey);
        $installer->onEnable();

        Db::table($this->prefix . 'module_install_log')
            ->where('module_key', $installer->getKey())
            ->where('status', 1)
            ->update(['enabled' => 1]);

        // 启用状态变更后清理缓存，保证 ModuleRegistry 下次读取最新状态
        ModuleStateChecker::reload();
        DriverRegistry::resetCache();
        return ['status' => 'success', 'message' => '插件已启用'];
    }

    /**
     * [TEMPORARY] 以插件 Installer::getNoticeDefinitions 为准，覆盖式刷新该插件的消息模板。
     *
     * TEMPORARY-PLUGIN-NOTICE-REFRESH：临时功能——供插件列表"刷新消息模板"按钮调用，
     * 用于插件代码中调整 getNoticeDefinitions 后将定义同步落库，后续随该按钮一并移除。
     * 仅处理通知配置与渠道模板数据，不触碰菜单等其它种子，不改变插件安装/启用状态。
     *
     * @param string $moduleKey 插件标识（如 product_normal）
     * @return array{refreshed: int}
     */
    public function refreshNotices(string $moduleKey): array
    {
        $this->getInstallers();
        $moduleKey = $this->resolveKey($moduleKey);
        if (!isset($this->installers[$moduleKey])) {
            throw new \RuntimeException("插件不存在: {$moduleKey}");
        }
        $installer = $this->installers[$moduleKey];
        if (!$installer instanceof \crmeb\module\BaseModuleInstaller) {
            throw new \RuntimeException('当前插件不支持消息模板刷新');
        }
        if (!$installer->isInstalled()) {
            throw new \RuntimeException('插件未安装，请先安装');
        }

        $payload = $installer->exportNoticeSeed();
        $definitions = is_array($payload['definitions'] ?? null) ? $payload['definitions'] : [];
        if (!$definitions) {
            return ['refreshed' => 0, 'message' => '该插件未声明消息通知定义'];
        }

        $count = app()->make(NoticeSeedServices::class)->refreshDefinitions(
            (string)($payload['owner'] ?? ''),
            $definitions
        );
        return ['refreshed' => $count];
    }

    /**
     * 禁用插件
     */
    public function disable(string $moduleKey): array
    {
        $this->getInstallers();
        $moduleKey = $this->resolveKey($moduleKey);
        if (!isset($this->installers[$moduleKey])) {
            throw new \RuntimeException("插件不存在: {$moduleKey}");
        }
        // 核心模块保护：plugin.json 中 required=true 的模块不允许禁用
        $this->assertNotRequired($moduleKey);
        $this->getDependencyResolver()->assertCanDisable($moduleKey);
        /** @var \crmeb\module\BaseModuleInstaller $installer */
        $installer = $this->installers[$moduleKey];
        if (!$installer->isInstalled()) {
            throw new \RuntimeException("插件未安装，请先安装");
        }

        $installer->onDisable();

        Db::table($this->prefix . 'module_install_log')
            ->where('module_key', $installer->getKey())
            ->where('status', 1)
            ->update(['enabled' => 0]);

        // 禁用状态变更后清理缓存，保证 ModuleRegistry 下次读取最新状态
        ModuleStateChecker::reload();
        DriverRegistry::resetCache();
        return ['status' => 'success', 'message' => '插件已禁用'];
    }

    /**
     * 核心模块保护校验
     * plugin.json 中 required=true 的模块（如 product_normal）为基础模块，
     * 禁用/卸载会导致站点半残（路由在但 Handler 全缺），故直接抛异常拒绝。
     *
     * @param string $moduleKey 已 resolve 过的 canonical id
     * @throws \RuntimeException
     */
    protected function assertNotRequired(string $moduleKey): void
    {
        $meta = $this->keyToMetaMap[$moduleKey] ?? [];
        if (!empty($meta['required'])) {
            $name = $meta['name'] ?? $moduleKey;
            throw new \RuntimeException("核心模块 [{$name}] 不允许禁用/卸载");
        }
    }

    protected function getDependencyResolver(): PluginDependencyResolver
    {
        if ($this->dependencyResolver === null) {
            $repository = new PluginManifestRepository($this->app->getRootPath());
            $this->dependencyResolver = new PluginDependencyResolver($repository);
            $errors = $this->dependencyResolver->validateGraph();
            if ($errors !== []) {
                throw new \RuntimeException('插件依赖图无效: ' . implode('; ', $errors));
            }
        }
        return $this->dependencyResolver;
    }

    /**
     * 获取安装状态列表
     * @return array
     */
    public function getStatusList(): array
    {
        $list = [];
        foreach ($this->getInstallers() as $canonicalId => $installer) {
            $list[] = $this->buildStatusItem($installer, $canonicalId);
        }
        return $list;
    }

    /**
     * 按类型获取模块列表
     * 支持前端展示类型（product）和后端原始类型（product_type）
     */
    public function getStatusListByType(string $type): array
    {
        $mapping = $this->getTypeMapping();
        // 前端传 product → 转成后端 product_type 做匹配
        $backendType = $mapping[$type] ?? $type;

        $list = [];
        foreach ($this->getInstallers() as $canonicalId => $installer) {
            $meta = $this->keyToMetaMap[$canonicalId] ?? [];
            if (($meta['type'] ?? $installer->getType()) !== $backendType) {
                continue;
            }
            $list[] = $this->buildStatusItem($installer, $canonicalId);
        }
        return $list;
    }

    /**
     * 构建状态列表项
     */
    protected function buildStatusItem(ModuleInstallerInterface $installer, string $canonicalId): array
    {
        $installed = $installer->isInstalled();
        $enabled = false;

        if ($installed) {
            $row = Db::table($this->prefix . 'module_install_log')
                ->where('module_key', $installer->getKey())
                ->where('status', 1)
                ->find();
            $enabled = $row && (int)$row['enabled'] === 1;
        }

        // 读取 plugin.json 获取额外信息
        $meta = $this->keyToMetaMap[$canonicalId] ?? $this->readPluginMeta($installer);

        // type 优先使用 plugin.json 中的值（与 getTypeMapping 的数据源一致），
        // 没有 plugin.json 时 fallback 到 Installer::getType()
        $displayType = $meta['type'] ?? $installer->getType();

        // key 始终以 Installer::getKey() 为准，确保 install/uninstall/enable/disable 使用统一标识
        // path 使用 plugin.json 的值，用于前端路由和资源定位
        // online_version 为应用市场检测到的在线最新版本（未安装时无记录返回空字符串），
        // 前端对比 version 与 online_version 判断是否显示升级按钮
        return [
            'id'        => $canonicalId,
            'key'       => $installer->getKey(),
            'path'      => $meta['path'] ?? '',
            'name'      => $meta['name'] ?? $installer->getName(),
            'type'      => $displayType,
            'version'   => $meta['version'] ?? '1.0.0',
            'online_version' => (string) ($row['online_version'] ?? ''),
            'author'    => $meta['author'] ?? '',
            'description' => $meta['description'] ?? '',
            'installed' => $installed,
            'enabled'   => $enabled,
            'required'  => ($meta['required'] ?? false) === true,
            'require'   => $meta['require'] ?? [],
        ];
    }

    /**
     * 读取插件目录下的 plugin.json
     *
     * 查找顺序：
     * 1. 从缓存 $keyToMetaMap 中读取（注册时已缓存）
     * 2. 从 Installer 所在目录查找
     * 3. 从 modules/{path最后一段}/ 目录查找（适用于产品类型等模块）
     */
    protected function readPluginMeta(ModuleInstallerInterface $installer): array
    {
        $key = $installer->getKey();

        // 优先从缓存读取
        if (isset($this->keyToMetaMap[$key])) {
            return $this->keyToMetaMap[$key];
        }

        $ref = new \ReflectionClass($installer);
        $installerDir = dirname($ref->getFileName());

        // 查找顺序1：Installer 所在目录
        $pluginJson = $installerDir . '/plugin.json';
        if (is_file($pluginJson)) {
            $content = file_get_contents($pluginJson);
            $meta = json_decode($content, true);
            if (is_array($meta)) {
                $this->keyToMetaMap[$key] = $meta;
                return $meta;
            }
        }

        // 查找顺序2：从 modules/ 目录按 key 查找
        // key 格式：trade_product_normal → modules/product/normal/plugin.json
        $rootPath = $this->app->getRootPath();
        $parts = explode('_', $key);
        if (count($parts) >= 2) {
            $modulePath = 'modules/' . implode('/', $parts);
            $pluginJson = $rootPath . $modulePath . '/plugin.json';
            if (is_file($pluginJson)) {
                $content = file_get_contents($pluginJson);
                $meta = json_decode($content, true);
                if (is_array($meta)) {
                    $this->keyToMetaMap[$key] = $meta;
                    return $meta;
                }
            }
        }

        // 查找顺序3：从 modules/{key}/ 目录查找（适用于 agent 等顶层模块）
        $pluginJson = $rootPath . 'modules/' . $key . '/plugin.json';
        if (is_file($pluginJson)) {
            $content = file_get_contents($pluginJson);
            $meta = json_decode($content, true);
            if (is_array($meta)) {
                $this->keyToMetaMap[$key] = $meta;
                return $meta;
            }
        }

        return [];
    }

    /**
     * 动态扫描各目录 plugin.json，建立类型映射
     *
     * 规则：
     * - 扫描 modules/ 下所有 plugin.json
     * - 对每个 plugin.json 的 type 值，去掉 _type 后缀得到前端展示名
     * - 同时收集 Installer::getType() 作为补充（旧模块无 plugin.json）
     *
     * 例如：plugin.json 中 type=product_type → 前端筛选传 product
     *
     * @return array<string, string> 前端type → 后端type
     */
    protected function getTypeMapping(): array
    {
        if ($this->typeMapping !== null) {
            return $this->typeMapping;
        }

        $this->typeMapping = [];
        $rootPath = $this->app->getRootPath();

        // 扫描 modules/ 下所有 plugin.json（支持一至三级深度）
        $jsonFiles = array_merge(
            glob($rootPath . 'modules/*/plugin.json') ?: [],
            glob($rootPath . 'modules/*/*/plugin.json') ?: [],
            glob($rootPath . 'modules/*/*/*/plugin.json') ?: []
        );

        foreach ($jsonFiles as $file) {
            $content = file_get_contents($file);
            $meta = json_decode($content, true);
            if (empty($meta['type'])) {
                continue;
            }
            $backendType = $meta['type'];
            // 去掉 _type 后缀作为前端类型名
            // product_type → product, activity → activity
            $frontType = str_ends_with($backendType, '_type')
                ? substr($backendType, 0, -5)
                : $backendType;
            $this->typeMapping[$frontType] = $backendType;
        }

        // 补充：从 Installer::getType() 收集旧模块类型（无 plugin.json）
        foreach ($this->getInstallers() as $installer) {
            $backendType = $installer->getType();
            $frontType = str_ends_with($backendType, '_type')
                ? substr($backendType, 0, -5)
                : $backendType;
            // 仅当该前端类型尚未映射时才补充
            if (!isset($this->typeMapping[$frontType])) {
                $this->typeMapping[$frontType] = $backendType;
            }
        }

        return $this->typeMapping;
    }
}
