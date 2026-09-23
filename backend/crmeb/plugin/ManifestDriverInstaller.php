<?php
declare(strict_types=1);

namespace crmeb\plugin;

use crmeb\module\BaseModuleInstaller;

/**
 * 无独立迁移和菜单的驱动使用的通用生命周期安装器。
 */
class ManifestDriverInstaller extends BaseModuleInstaller
{
    private PluginManifest $manifest;

    public function __construct(PluginManifest $manifest)
    {
        $this->manifest = $manifest;
        parent::__construct();
    }

    public function getKey(): string
    {
        return $this->manifest->identity->canonicalId();
    }

    public function getName(): string
    {
        return $this->manifest->name;
    }

    public function getType(): string
    {
        return 'driver';
    }

    protected function getVersion(): string
    {
        return $this->manifest->version;
    }

    protected function getPluginManifest(): PluginManifest
    {
        return $this->manifest;
    }

    protected function runMigrate(): void
    {
    }

    protected function rollbackMigrate(): void
    {
    }

    protected function getMenuConfig(): array
    {
        return [];
    }
}
