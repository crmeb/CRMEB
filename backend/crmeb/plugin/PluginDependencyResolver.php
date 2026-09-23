<?php
declare(strict_types=1);

namespace crmeb\plugin;

use think\facade\Db;

/**
 * 插件依赖图、版本约束和正反向生命周期保护。
 */
class PluginDependencyResolver
{
    private PluginManifestRepository $repository;

    /** @var array<string, PluginManifest> */
    private array $manifests;

    public function __construct(PluginManifestRepository $repository)
    {
        $this->repository = $repository;
        $this->manifests = $repository->all();
    }

    /** @return string[] */
    public function validateGraph(): array
    {
        $errors = [];
        foreach ($this->manifests as $id => $manifest) {
            foreach ($manifest->dependencies as $dependencyId => $constraint) {
                if (in_array($dependencyId, ['php', 'crmeb'], true)) {
                    continue;
                }
                if (!isset($this->manifests[$dependencyId])) {
                    $errors[] = "{$id} 依赖不存在的插件 {$dependencyId}";
                    continue;
                }
                if (!self::satisfies($this->manifests[$dependencyId]->version, (string) $constraint)) {
                    $errors[] = "{$id} 要求 {$dependencyId} {$constraint}，当前版本 {$this->manifests[$dependencyId]->version}";
                }
            }
        }

        $visiting = [];
        $visited = [];
        foreach (array_keys($this->manifests) as $id) {
            $this->visit($id, $visiting, $visited, $errors, []);
        }
        return array_values(array_unique($errors));
    }

    public function assertCanInstall(string $pluginId): void
    {
        $manifest = $this->requireManifest($pluginId);
        $this->assertPlatform($manifest);
        foreach ($this->pluginDependencies($manifest) as $dependency) {
            if (!$this->isInstalled($dependency)) {
                throw new \RuntimeException("请先安装依赖插件 {$dependency->identity->canonicalId()}");
            }
        }
    }

    public function assertCanEnable(string $pluginId): void
    {
        $manifest = $this->requireManifest($pluginId);
        $this->assertPlatform($manifest);
        foreach ($this->pluginDependencies($manifest) as $dependency) {
            if (!$this->isEnabled($dependency)) {
                throw new \RuntimeException("请先启用依赖插件 {$dependency->identity->canonicalId()}");
            }
        }
    }

    public function assertCanDisable(string $pluginId): void
    {
        $dependents = array_filter(
            $this->reverseDependencies($pluginId),
            fn (PluginManifest $item) => $this->isEnabled($item)
        );
        if ($dependents !== []) {
            throw new \RuntimeException('仍被已启用插件依赖: ' . implode(', ', array_keys($dependents)));
        }
    }

    public function assertCanUninstall(string $pluginId): void
    {
        $dependents = array_filter(
            $this->reverseDependencies($pluginId),
            fn (PluginManifest $item) => $this->isInstalled($item)
        );
        if ($dependents !== []) {
            throw new \RuntimeException('仍被已安装插件依赖: ' . implode(', ', array_keys($dependents)));
        }
    }

    private function assertPlatform(PluginManifest $manifest): void
    {
        $requirements = $manifest->dependencies;
        if (isset($requirements['php']) && !self::satisfies(PHP_VERSION, (string) $requirements['php'])) {
            throw new \RuntimeException("PHP 版本不满足 {$requirements['php']}，当前 " . PHP_VERSION);
        }
        if (isset($requirements['crmeb'])) {
            $version = function_exists('get_crmeb_version') ? get_crmeb_version() : '0.0.0';
            if (!self::satisfies($version, (string) $requirements['crmeb'])) {
                throw new \RuntimeException("CRMEB 版本不满足 {$requirements['crmeb']}，当前 {$version}");
            }
        }
    }

    /** @return array<string, PluginManifest> */
    private function pluginDependencies(PluginManifest $manifest): array
    {
        $result = [];
        foreach ($manifest->dependencies as $dependencyId => $constraint) {
            if (in_array($dependencyId, ['php', 'crmeb'], true)) {
                continue;
            }
            $dependency = $this->manifests[$dependencyId] ?? null;
            if (!$dependency) {
                throw new \RuntimeException("依赖插件不存在: {$dependencyId}");
            }
            if (!self::satisfies($dependency->version, (string) $constraint)) {
                throw new \RuntimeException("依赖插件 {$dependencyId} 版本不满足 {$constraint}");
            }
            $result[$dependencyId] = $dependency;
        }
        return $result;
    }

    /** @return array<string, PluginManifest> */
    private function reverseDependencies(string $pluginId): array
    {
        $result = [];
        foreach ($this->manifests as $id => $manifest) {
            if (array_key_exists($pluginId, $manifest->dependencies)) {
                $result[$id] = $manifest;
            }
        }
        return $result;
    }

    private function isInstalled(PluginManifest $manifest): bool
    {
        return Db::name('module_install_log')
            ->where('module_key', $this->storageKey($manifest))
            ->where('status', 1)
            ->count() > 0;
    }

    private function isEnabled(PluginManifest $manifest): bool
    {
        return Db::name('module_install_log')
            ->where('module_key', $this->storageKey($manifest))
            ->where('status', 1)
            ->where('enabled', 1)
            ->count() > 0;
    }

    private function storageKey(PluginManifest $manifest): string
    {
        return $manifest->identity->kind === 'driver'
            ? $manifest->identity->canonicalId()
            : $manifest->identity->legacyKey;
    }

    private function requireManifest(string $pluginId): PluginManifest
    {
        $manifest = $this->manifests[$pluginId] ?? null;
        if (!$manifest) {
            throw new \RuntimeException("插件清单不存在: {$pluginId}");
        }
        return $manifest;
    }

    private function visit(string $id, array &$visiting, array &$visited, array &$errors, array $path): void
    {
        if (isset($visited[$id])) {
            return;
        }
        if (isset($visiting[$id])) {
            $path[] = $id;
            $errors[] = '插件循环依赖: ' . implode(' -> ', $path);
            return;
        }
        $visiting[$id] = true;
        $path[] = $id;
        foreach ($this->manifests[$id]->dependencies as $dependencyId => $constraint) {
            if (isset($this->manifests[$dependencyId])) {
                $this->visit($dependencyId, $visiting, $visited, $errors, $path);
            }
        }
        unset($visiting[$id]);
        $visited[$id] = true;
    }

    public static function satisfies(string $version, string $constraint): bool
    {
        $version = self::normalizeVersion($version);
        $constraint = trim($constraint);
        if ($constraint === '' || $constraint === '*') {
            return true;
        }
        if (str_starts_with($constraint, '^')) {
            $minimum = self::normalizeVersion(substr($constraint, 1));
            $parts = array_map('intval', explode('.', $minimum));
            $upper = $parts[0] > 0
                ? ($parts[0] + 1) . '.0.0'
                : '0.' . ($parts[1] + 1) . '.0';
            return version_compare($version, $minimum, '>=') && version_compare($version, $upper, '<');
        }
        if (str_starts_with($constraint, '~')) {
            $minimum = self::normalizeVersion(substr($constraint, 1));
            $parts = array_map('intval', explode('.', $minimum));
            $upper = $parts[0] . '.' . ($parts[1] + 1) . '.0';
            return version_compare($version, $minimum, '>=') && version_compare($version, $upper, '<');
        }
        if (preg_match('/^(>=|<=|>|<|=)?\s*(.+)$/', $constraint, $matches)) {
            return version_compare($version, self::normalizeVersion($matches[2]), $matches[1] ?: '=');
        }
        return false;
    }

    private static function normalizeVersion(string $version): string
    {
        if (!preg_match('/(\d+)(?:\.(\d+))?(?:\.(\d+))?/', $version, $matches)) {
            return '0.0.0';
        }
        return sprintf('%d.%d.%d', $matches[1], $matches[2] ?? 0, $matches[3] ?? 0);
    }
}
