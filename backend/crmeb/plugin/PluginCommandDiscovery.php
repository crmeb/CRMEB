<?php
declare(strict_types=1);

namespace crmeb\plugin;

/**
 * 从插件清单发现控制台命令，核心配置无需引用任何具体插件类。
 */
class PluginCommandDiscovery
{
    /** @return array<string, class-string> */
    public static function discover(string $rootPath): array
    {
        $commands = [];
        $repository = new PluginManifestRepository($rootPath);
        foreach ($repository->modules() as $manifest) {
            $declared = $manifest->meta['backend']['commands'] ?? [];
            if (!is_array($declared)) {
                throw new \RuntimeException("插件命令声明必须是对象: {$manifest->path}");
            }
            foreach ($declared as $name => $class) {
                if (!is_string($name) || !preg_match('/^[a-z][a-z0-9_-]*(?::[a-z0-9_-]+)+$/', $name)) {
                    throw new \RuntimeException("插件命令名称非法: {$manifest->path}");
                }
                if (!is_string($class) || $class === '') {
                    throw new \RuntimeException("插件命令类无效: {$name}");
                }
                if (!class_exists($class) || !is_subclass_of($class, \think\console\Command::class)) {
                    throw new \RuntimeException("插件命令类不存在或不是控制台命令: {$name} => {$class}");
                }
                if (isset($commands[$name])) {
                    throw new \RuntimeException("插件命令重复: {$name}");
                }
                $commands[$name] = $class;
            }
        }
        return $commands;
    }
}
