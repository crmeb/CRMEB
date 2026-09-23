<?php
declare(strict_types=1);

namespace crmeb\plugin;

/**
 * 全系统 plugin.json 的唯一发现入口。
 */
class PluginManifestRepository
{
    private string $rootPath;

    /** @var array<string, PluginManifest>|null */
    private ?array $manifests = null;

    /** @var array<string, string[]> legacy key => canonical ids */
    private array $legacyIndex = [];

    public function __construct(string $rootPath)
    {
        $this->rootPath = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /** @return array<string, PluginManifest> canonical id => manifest */
    public function all(): array
    {
        if ($this->manifests !== null) {
            return $this->manifests;
        }

        $files = array_merge(
            glob($this->rootPath . 'modules/*/plugin.json') ?: [],
            glob($this->rootPath . 'modules/*/*/plugin.json') ?: [],
            glob($this->rootPath . 'modules/*/*/*/plugin.json') ?: [],
            glob($this->rootPath . 'driver/*/*/plugin.json') ?: []
        );
        sort($files);

        $this->manifests = [];
        foreach ($files as $file) {
            try {
                $manifest = PluginManifest::fromFile($file, $this->rootPath);
            } catch (\Throwable $e) {
                // 运行时扫描不能因单个可选驱动清单损坏而阻断其他驱动接口。
                // 插件安装/升级入口仍直接调用 PluginManifest::fromFile，保持严格校验。
                error_log($e->getMessage());
                continue;
            }
            $id = $manifest->identity->canonicalId();
            if (isset($this->manifests[$id])) {
                throw new \RuntimeException("插件复合标识重复: {$id}");
            }
            $this->manifests[$id] = $manifest;
            $this->legacyIndex[$manifest->identity->legacyKey][] = $id;
        }

        return $this->manifests;
    }

    /** @return array<string, PluginManifest> */
    public function modules(): array
    {
        return array_filter($this->all(), static fn (PluginManifest $item) => $item->identity->kind === 'module');
    }

    /** @return array<string, PluginManifest> */
    public function drivers(?string $category = null): array
    {
        return array_filter($this->all(), static function (PluginManifest $item) use ($category): bool {
            return $item->identity->kind === 'driver'
                && ($category === null || $item->identity->category === strtolower($category));
        });
    }

    public function get(string $canonicalId): ?PluginManifest
    {
        return $this->all()[$canonicalId] ?? null;
    }

    /**
     * 旧 key 可能跨驱动类型重名，因此返回数组而不是静默选一个。
     *
     * @return PluginManifest[]
     */
    public function findByLegacyKey(string $legacyKey): array
    {
        $this->all();
        $result = [];
        foreach ($this->legacyIndex[$legacyKey] ?? [] as $id) {
            $result[] = $this->manifests[$id];
        }
        return $result;
    }
}
