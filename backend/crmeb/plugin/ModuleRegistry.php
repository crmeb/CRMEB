<?php

namespace crmeb\plugin;

use think\App as ThinkApp;

/**
 * 模块注册中心
 *
 * 负责扫描 modules/ 目录下的所有模块，调用其 register() 和 boot() 方法完成注册。
 * 由 AppService 通过依赖注入调用，不依赖任何 modules/ 下的具体模块。
 *
 * 安装状态拦截：
 * - 有 plugin.json 的模块（含 key）需通过 ModuleStateChecker::isActive() 校验
 *   （eb_module_install_log 中 status=1 AND enabled=1 才加载）
 * - 无 plugin.json 的模块（如父级骨架模块 modules/trade/App.php）一律放行
 *
 * 启动期隔离：
 * - class_exists() 触发自动加载并 include 模块 App.php，插件语法错误会抛 ParseError；
 *   类加载与 register/boot 抛出的任何 Throwable 均只记 error 日志并跳过该模块，
 *   不阻断其他模块注册与全站启动
 */
class ModuleRegistry
{
    /** @var ThinkApp */
    protected ThinkApp $app;

    /** @var array 已注册的模块列表 */
    protected array $registeredModules = [];

    public function __construct(ThinkApp $app)
    {
        $this->app = $app;
    }

    /**
     * 扫描并注册所有模块
     * 支持一级模块（modules/{name}/App.php）和二级子模块（modules/{name}/{sub}/App.php）
     *
     * @param string $modulesDir modules 目录路径（末尾含 /）
     * @return void
     */
    public function registerAll(string $modulesDir): void
    {
        if (!is_dir($modulesDir)) {
            return;
        }

        $moduleDirs = glob($modulesDir . '*', GLOB_ONLYDIR);
        if (empty($moduleDirs)) {
            return;
        }

        foreach ($moduleDirs as $moduleDir) {
            $moduleName = basename($moduleDir);
            $appFile = $moduleDir . '/App.php';

            if (is_file($appFile)) {
                $className = '\\modules\\' . $moduleName . '\\App';
                $pluginKey = $this->readPluginKey($moduleDir);
                $this->registerModule($className, $moduleName, $pluginKey);
            }

            foreach (glob($moduleDir . '/*', GLOB_ONLYDIR) as $subDir) {
                $subName = basename($subDir);
                $subAppFile = $subDir . '/App.php';

                if (is_file($subAppFile)) {
                    $className = '\\modules\\' . $moduleName . '\\' . $subName . '\\App';
                    $pluginKey = $this->readPluginKey($subDir);
                    $this->registerModule($className, $moduleName . '/' . $subName, $pluginKey);
                }

                // 支持三级子模块：modules/{name}/{sub}/{sub2}/App.php
                foreach (glob($subDir . '/*', GLOB_ONLYDIR) as $sub2Dir) {
                    $sub2Name = basename($sub2Dir);
                    $sub2AppFile = $sub2Dir . '/App.php';

                    if (!is_file($sub2AppFile)) {
                        continue;
                    }

                    $className = '\\modules\\' . $moduleName . '\\' . $subName . '\\' . $sub2Name . '\\App';
                    $pluginKey = $this->readPluginKey($sub2Dir);
                    $this->registerModule($className, $moduleName . '/' . $subName . '/' . $sub2Name, $pluginKey);
                }
            }
        }
    }

    /**
     * 注册单个模块
     * @param string $className 模块完整类名
     * @param string $moduleName 模块标识（用于日志）
     * @param string|null $pluginKey plugin.json 的 key，无 plugin.json 时为 null（父模块放行）
     */
    protected function registerModule(string $className, string $moduleName, ?string $pluginKey = null): void
    {
        // 启动期隔离：class_exists 会触发自动加载并 include 模块 App.php，
        // 插件文件语法错误会抛 ParseError，必须与 register/boot 一并纳入 try 保护
        try {
            if (!class_exists($className)) {
                return;
            }

            // 有 plugin.json key 的模块需检查安装状态（严格白名单：status=1 AND enabled=1）
            // 无 key 的模块（父级骨架模块，如 modules/trade/App.php）一律放行
            if ($pluginKey !== null && !ModuleStateChecker::isActive($pluginKey)) {
               // $this->app->log->info('模块 [' . $moduleName . '] 未安装或未启用，跳过注册');
                return;
            }

            if (method_exists($className, 'register')) {
                $className::register($this->app);
            }
            if (method_exists($className, 'boot')) {
                $className::boot($this->app);
            }
            $this->registeredModules[] = $moduleName;
        } catch (\Throwable $e) {
            // 降级行为：仅跳过该模块继续启动；记录 file/line 便于定位问题插件
            $this->app->log->error('模块 [' . $moduleName . '] 启动加载失败，已跳过：' . $e->getMessage()
                . ' @ ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    /**
     * 读取模块目录下 plugin.json 的 key
     * @param string $moduleDir 模块目录绝对路径
     * @return string|null key 值，无 plugin.json 或解析失败返回 null
     */
    protected function readPluginKey(string $moduleDir): ?string
    {
        $pluginFile = $moduleDir . '/plugin.json';
        if (!is_file($pluginFile)) {
            return null;
        }
        $content = @file_get_contents($pluginFile);
        if ($content === false) {
            return null;
        }
        $meta = json_decode($content, true);
        if (!is_array($meta) || empty($meta['key'])) {
            return null;
        }
        return (string) $meta['key'];
    }

    /**
     * 获取已注册的模块列表
     *
     * @return array
     */
    public function getRegisteredModules(): array
    {
        return $this->registeredModules;
    }
}
