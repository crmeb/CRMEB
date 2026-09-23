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

namespace crmeb\services\schema;

use crmeb\plugin\ModuleStateChecker;

/**
 * 通用页面提供者注册中心
 *
 * 管理"插件目录名 + 页面标识"到页面提供者的映射，支持两种注册方式：
 * 1. 约定式自动发现（默认）：扫描 modules 下一级与二级模块目录中的 schema 子目录
 *    （modules/{插件}/schema/ 与 modules/{分类}/{插件}/schema/）内全部 PHP 文件，
 *    文件名要求大驼峰且以 Page.php 结尾（排除 Base/Abstract/Interface/Contract/Trait 开头），
 *    插件目录名取 schema 上级目录名（即访问 URL 中的 pluginKey）
 * 2. 手动注册：模块 App::register() 中调用 register()（可覆盖自动发现结果）
 *
 * 模块启用校验：扫描时读取模块根目录 plugin.json 的 key，经 ModuleStateChecker::isActive()
 * 校验（已安装且已启用），未启用模块的页面不注册——与模块路由加载行为保持一致。
 *
 * 使用方式：
 * ```php
 * $registry = app()->make(SchemaPageRegistry::class);
 * $provider = $registry->get('mcp', 'list'); // 返回页面提供者实例
 * ```
 *
 * 位于 crmeb/ 层，仅依赖框架函数与 crmeb/ 内类，符合三层架构依赖铁律。
 */
class SchemaPageRegistry
{
    /**
     * 已注册的提供者类名映射
     * @var array<string, class-string> 键格式：{pluginKey}/{pageKey}
     */
    protected array $handlers = [];

    /**
     * 提供者实例缓存（容器实例化，自动注入构造依赖）
     * @var array<string, SchemaPageProvider>
     */
    protected array $instances = [];

    /**
     * 是否已完成约定式扫描（每请求仅扫描一次）
     * @var bool
     */
    protected bool $discovered = false;

    /**
     * 手动注册页面提供者（同名覆盖自动发现结果）
     *
     * @param string $pluginKey 插件目录名（与访问 URL 中的 pluginKey 一致）
     * @param string $pageKey 页面标识
     * @param class-string $handlerClass 提供者类名
     * @return void
     */
    public function register(string $pluginKey, string $pageKey, string $handlerClass): void
    {
        $this->handlers[$this->buildKey($pluginKey, $pageKey)] = $handlerClass;
    }

    /**
     * 获取页面提供者实例（容器实例化，自动注入构造依赖）
     *
     * @param string $pluginKey 插件目录名
     * @param string $pageKey 页面标识
     * @return SchemaPageProvider|null 未注册时返回 null
     */
    public function get(string $pluginKey, string $pageKey): ?SchemaPageProvider
    {
        $this->discover();
        $key = $this->buildKey($pluginKey, $pageKey);
        if (!isset($this->instances[$key])) {
            if (!isset($this->handlers[$key])) {
                return null;
            }
            $this->instances[$key] = app()->make($this->handlers[$key]);
        }
        return $this->instances[$key];
    }

    /**
     * 是否已注册指定页面
     *
     * @param string $pluginKey 插件目录名
     * @param string $pageKey 页面标识
     * @return bool
     */
    public function has(string $pluginKey, string $pageKey): bool
    {
        $this->discover();
        return isset($this->handlers[$this->buildKey($pluginKey, $pageKey)]);
    }

    /**
     * 获取全部已注册页面标识列表
     *
     * @return array [['plugin_key' => 'mcp', 'page_key' => 'list', 'handler' => 类名], ...]
     */
    public function all(): array
    {
        $this->discover();
        $list = [];
        foreach ($this->handlers as $key => $class) {
            [$pluginKey, $pageKey] = explode('/', $key, 2);
            $list[] = ['plugin_key' => $pluginKey, 'page_key' => $pageKey, 'handler' => $class];
        }
        return $list;
    }

    /**
     * 执行约定式扫描（幂等，每请求仅一次）
     *
     * @return void
     */
    protected function discover(): void
    {
        if ($this->discovered) {
            return;
        }
        $this->discovered = true;

        $files = array_merge(
            glob(root_path() . 'modules/*/schema/*.php') ?: [],
            glob(root_path() . 'modules/*/*/schema/*.php') ?: []
        );
        foreach ($files as $file) {
            $this->discoverFile($file);
        }
    }

    /**
     * 处理单个候选文件：校验命名/模块状态后注册
     *
     * @param string $file schema 目录下的 PHP 文件绝对路径
     * @return void
     */
    protected function discoverFile(string $file): void
    {
        $basename = basename($file, '.php');
        // 命名约定：大驼峰 + Page.php 结尾；排除基类/接口/契约类
        if (!preg_match('/^[A-Z]/', $basename) || !str_ends_with($basename, 'Page')) {
            return;
        }
        if (preg_match('/^(Base|Abstract|Interface|Contract|Trait)/', $basename)) {
            return;
        }

        // 推导模块根目录与命名空间：{root}/modules/{a}[/{b}]/schema/{X}.php
        $schemaDir = dirname($file);
        $moduleRoot = dirname($schemaDir);
        $pluginKey = basename($moduleRoot);
        $relative = str_replace(['\\', '/'], '\\', substr($file, strlen(root_path()), -4));
        $className = trim($relative, '\\');
        if (!class_exists($className)) {
            require_once $file;
        }
        if (!class_exists($className) || !is_subclass_of($className, SchemaPageProvider::class)) {
            return;
        }

        // 模块启用校验（plugin.json 的 key），未安装/未启用的模块页面不注册
        if (!ModuleStateChecker::isModuleRouteActive(dirname($schemaDir) . '/route/schema.php')) {
            return;
        }

        try {
            $pageKey = (string)$className::pageKey();
        } catch (\Throwable) {
            return;
        }
        if ($pageKey === '') {
            return;
        }
        $this->register($pluginKey, $pageKey, $className);
    }

    /**
     * 构建注册键
     *
     * @param string $pluginKey 插件目录名
     * @param string $pageKey 页面标识
     * @return string
     */
    protected function buildKey(string $pluginKey, string $pageKey): string
    {
        return $pluginKey . '/' . $pageKey;
    }
}
