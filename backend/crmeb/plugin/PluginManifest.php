<?php
declare(strict_types=1);

namespace crmeb\plugin;

/**
 * plugin.json 的统一只读模型，同时兼容现有清单格式。
 */
class PluginManifest
{
    public PluginIdentity $identity;
    public string $name;
    public string $type;
    public string $version;
    public string $path;
    public string $file;
    public string $basePath;
    public bool $required;//安装卸载验证
    public bool $defaultInstall; //是否默认安装
    public array $dependencies;
    public array $meta;

    private function __construct()
    {
    }

    public static function fromFile(string $file, string $rootPath): self
    {
        $content = file_get_contents($file);
        if ($content === false) {
            throw new \RuntimeException("无法读取插件清单: {$file}");
        }

        try {
            $meta = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("插件清单 JSON 无效: {$file}; {$e->getMessage()}", 0, $e);
        }
        if (!is_array($meta)) {
            throw new \RuntimeException("插件清单必须是 JSON 对象: {$file}");
        }

        foreach (['key', 'name', 'type', 'version', 'path'] as $field) {
            if (!isset($meta[$field]) || !is_string($meta[$field]) || trim($meta[$field]) === '') {
                throw new \RuntimeException("插件清单缺少有效字段 {$field}: {$file}");
            }
        }

        $path = trim(str_replace('\\', '/', $meta['path']), '/');
        if (str_contains($path, '..') || !preg_match('#^(modules|driver)/[A-Za-z0-9_.-]+(?:/[A-Za-z0-9_.-]+){0,2}$#', $path)) {
            throw new \RuntimeException("插件清单 path 非法: {$path}");
        }

        $declaredDir = realpath(rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $path);
        $actualDir = realpath(dirname($file));
        if ($declaredDir === false || $actualDir === false || $declaredDir !== $actualDir) {
            throw new \RuntimeException("插件清单 path 与实际目录不一致: {$file}");
        }

        $segments = explode('/', $path);
        $kind = $segments[0] === 'driver' ? 'driver' : 'module';
        $category = $segments[1] ?? $meta['type'];
        if ($kind === 'driver') {
            $driverMeta = $meta['driver'] ?? null;
            if (!is_array($driverMeta) || empty($driverMeta['driver_key'])) {
                throw new \RuntimeException("驱动清单缺少 driver.driver_key: {$file}");
            }
            $identityKey = (string) $driverMeta['driver_key'];
        } else {
            $identityKey = count($segments) > 2
                ? implode('_', array_slice($segments, 2))
                : (string) $meta['key'];
        }

        $manifest = new self();
        $manifest->identity = new PluginIdentity($kind, $category, $identityKey, (string) $meta['key']);
        $manifest->name = trim($meta['name']);
        $manifest->type = trim($meta['type']);
        $manifest->version = trim($meta['version']);
        $manifest->path = $path;
        $manifest->file = $file;
        $manifest->basePath = dirname($file);
        $manifest->required = ($meta['required'] ?? false) === true;
        $manifest->defaultInstall = ($meta['default_install'] ?? false) === true;
        $manifest->dependencies = is_array($meta['require'] ?? null) ? $meta['require'] : [];
        $manifest->meta = $meta;
        $manifest->meta['canonical_id'] = $manifest->identity->canonicalId();
        $manifest->meta['kind'] = $manifest->identity->kind;
        $manifest->meta['category'] = $manifest->identity->category;

        return $manifest;
    }
}
