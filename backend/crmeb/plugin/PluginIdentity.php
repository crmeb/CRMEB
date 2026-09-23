<?php
declare(strict_types=1);

namespace crmeb\plugin;

/**
 * 插件复合身份。
 *
 * canonicalId 在模块和驱动之间全局唯一；legacyKey 保留给现有数据库、接口和路由
 * 兼容使用，后续迁移完成前不能直接删除。
 */
class PluginIdentity
{
    public string $kind;
    public string $category;
    public string $key;
    public string $legacyKey;

    public function __construct(string $kind, string $category, string $key, string $legacyKey)
    {
        $this->kind = self::normalizePart($kind);
        $this->category = self::normalizePart($category);
        $this->key = self::normalizePart($key);
        $this->legacyKey = trim($legacyKey);

        if ($this->kind === '' || $this->category === '' || $this->key === '' || $this->legacyKey === '') {
            throw new \InvalidArgumentException('插件身份 kind/category/key/legacyKey 均不能为空');
        }
        if (!in_array($this->kind, ['module', 'driver'], true)) {
            throw new \InvalidArgumentException("不支持的插件 kind: {$this->kind}");
        }
    }

    public function canonicalId(): string
    {
        return "{$this->kind}:{$this->category}:{$this->key}";
    }

    public function __toString(): string
    {
        return $this->canonicalId();
    }

    private static function normalizePart(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['\\', '/', ' '], '_', $value);
        return preg_replace('/[^a-z0-9_.-]+/', '_', $value) ?: '';
    }
}
