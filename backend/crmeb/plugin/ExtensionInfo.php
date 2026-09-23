<?php

namespace crmeb\plugin;

/**
 * 插件元数据值对象
 *
 * 封装插件的 key、type、handlerClass 等信息，
 * 由 ExtensionRegistry 在 discover 阶段创建。
 */
class ExtensionInfo
{
    /** @var string 插件唯一标识 */
    public string $key;

    /** @var string 全局唯一复合标识 */
    public string $id;

    /** @var string 插件类型（product / activity / channel / agent / ...） */
    public string $type;

    /** @var string|null 主处理器类名（无 Handler.php 时为 null） */
    public ?string $handlerClass = null;

    /** @var string 插件目录绝对路径 */
    public string $basePath = '';

    /** @var array plugin.json 原始元数据 */
    public array $meta = [];

    public function __construct(
        string $key,
        string $type,
        ?string $handlerClass = null,
        string $basePath = '',
        array $meta = []
    ) {
        $this->key = $key;
        $this->id = (string) ($meta['canonical_id'] ?? $key);
        $this->type = $type;
        $this->handlerClass = $handlerClass;
        $this->basePath = $basePath;
        $this->meta = $meta;
    }
}
