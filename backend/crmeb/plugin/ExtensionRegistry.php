<?php

namespace crmeb\plugin;

use think\App;

/**
 * 统一插件注册中心
 *
 * 替代之前分散的 ProductTypeRegistry / ActivityRegistry / UserChannelRegistry 等。
 * 所有插件通过 plugin.json 声明 type，由 PluginManager::register() 调用 discover() 自动发现。
 *
 * 核心能力：
 * - discover(): 扫描目录，读取 plugin.json + 文件约定自动注册
 * - getByType(): 按类型查询插件（如 getAll product 类型）
 * - getHandler(): 按 key 获取处理器实例（懒加载）
 * - getByInterface(): 按实现的接口查询
 */
class ExtensionRegistry
{
    /** @var array<string, ExtensionInfo> key → 元数据 */
    protected array $manifest = [];

    /** @var array<string, object> key → 处理器实例缓存（懒加载） */
    protected array $instances = [];

    /** @var array<string, array<string, string>> type → [key → handlerClass] */
    protected array $typedIndex = [];

    /** @var array<string, ExtensionInfo> key → 元数据（含无 handler 的插件） */
    protected array $allManifest = [];

    /** @var App */
    protected App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 手动注册一个插件
     *
     * @param string      $key          插件唯一标识
     * @param string      $type         插件类型
     * @param string|null $handlerClass 主处理器类名（纯事件驱动型插件为 null）
     * @param string      $basePath     插件目录路径
     * @param array       $meta         plugin.json 原始元数据
     */
    public function register(
        string $key,
        string $type,
        ?string $handlerClass = null,
        string $basePath = '',
        array $meta = []
    ): void {
        $info = new ExtensionInfo($key, $type, $handlerClass, $basePath, $meta);
        $this->allManifest[$key] = $info;
        $this->manifest[$key] = $info;
        if ($handlerClass) {
            $this->typedIndex[$type][$key] = $handlerClass;
        } else {
            // 无 handler 的插件也记录到 typedIndex，但 handlerClass 为空
            $this->typedIndex[$type][$key] = '';
        }
    }

    /**
     * 通过文件约定发现并注册所有插件
     * 支持多级深度：平铺和可选分组两种布局
     *
     * 身份规则：以 plugin.json 的 key 为唯一标识（与 ModuleStateChecker 同轨），
     * key 缺失时以 path 兜底（老插件兼容）。
     * 启用过滤：禁用模块不注册（与四端路由 ModuleStateChecker::isModuleRouteActive 白名单行为一致），
     * 表不存在时 isActive 兜底放行，不影响首次安装。
     *
     * @param string $pluginsDir 插件目录路径（末尾含 /）
     */
    public function discover(string $pluginsDir): void
    {
        $rootPath = dirname(rtrim($pluginsDir, '/\\')) . DIRECTORY_SEPARATOR;
        $repository = new PluginManifestRepository($rootPath);

        foreach ($repository->modules() as $manifest) {
            $meta = $manifest->meta;
            $key = $manifest->identity->legacyKey;
            $type = $manifest->type;
            $pluginDir = $manifest->basePath;

            // 启用白名单：禁用模块不进注册表（discover 与路由加载、模块扫描三处口径一致）
            if (!ModuleStateChecker::isActive($key)) {
                continue;
            }

            // 约定：存在 Handler.php 则自动作为主处理器
            $handlerClass = null;
            if (is_file($pluginDir . '/Handler.php')) {
                // 命名空间从相对路径推导：modules/product/normal → \modules\product\normal\Handler
                $relativePath = substr($pluginDir, strlen(rtrim($pluginsDir, '/\\')) + 1);
                $namespace = '\\modules\\' . str_replace('/', '\\', $relativePath);
                $handlerClass = $namespace . '\\Handler';
            }

            $this->register($key, $type, $handlerClass, $pluginDir, $meta);
        }
    }

    /**
     * 按类型获取所有插件
     *
     * @return ExtensionInfo[]
     */
    public function getByType(string $type): array
    {
        $keys = array_keys($this->typedIndex[$type] ?? []);
        $result = [];
        foreach ($keys as $k) {
            if (isset($this->manifest[$k])) {
                $result[$k] = $this->manifest[$k];
            }
        }
        return $result;
    }

    /**
     * 按 key 获取处理器实例（懒加载 + 缓存）
     *
     * @return object
     * @throws PluginNotFoundException
     */
    public function getHandler(string $key): object
    {
        if (!isset($this->manifest[$key])) {
            throw new PluginNotFoundException("插件 [{$key}] 未注册");
        }
        if (!isset($this->instances[$key])) {
            $class = $this->manifest[$key]->handlerClass;
            if (!$class) {
                throw new PluginNotFoundException("插件 [{$key}] 没有主处理器，请直接通过 getByType() 查询");
            }
            $this->instances[$key] = $this->app->make($class);
        }
        return $this->instances[$key];
    }

    /**
     * 检查某插件是否已注册
     */
    public function has(string $key): bool
    {
        return isset($this->manifest[$key]);
    }

    /**
     * 检查某类型插件是否存在
     */
    public function hasType(string $type): bool
    {
        return !empty($this->typedIndex[$type] ?? []);
    }

    /**
     * 获取所有已注册的 type
     *
     * @return string[]
     */
    public function getAllTypes(): array
    {
        return array_keys($this->typedIndex);
    }

    /**
     * 按实现的接口查询插件
     * 通过反射检查 Handler 类是否实现了指定接口
     *
     * @return ExtensionInfo[]
     */
    public function getByInterface(string $interface): array
    {
        return array_filter($this->manifest, function (ExtensionInfo $info) use ($interface) {
            if (!$info->handlerClass) {
                return false;
            }
            return is_subclass_of($info->handlerClass, $interface);
        });
    }

    /**
     * 获取所有已注册插件的元数据
     *
     * @return ExtensionInfo[]
     */
    public function all(): array
    {
        return $this->manifest;
    }

    /**
     * 从注册表中移除一个插件
     * 用于禁用插件时清理 Registry
     */
    public function remove(string $key): void
    {
        unset($this->manifest[$key]);
        unset($this->allManifest[$key]);
        unset($this->instances[$key]);
        foreach ($this->typedIndex as $type => $keys) {
            if (isset($keys[$key])) {
                unset($this->typedIndex[$type][$key]);
                break;
            }
        }
    }
}
