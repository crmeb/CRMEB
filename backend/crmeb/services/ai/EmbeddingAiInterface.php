<?php
declare(strict_types=1);

namespace crmeb\services\ai;

/**
 * AI 文本向量化能力契约
 *
 * 扩展 AiInterface：协议适配器实现本接口即具备向量化能力，
 * 调用方以 instanceof EmbeddingAiInterface 探测后调用 embeddings()。
 * 由 crmeb\services\ai\AiProtocol 工厂按模型记录的 protocol 字段创建实例。
 */
interface EmbeddingAiInterface extends AiInterface
{
    /**
     * 文本向量化
     * @param array $inputs 待向量化的文本列表（一维字符串数组）
     * @return array 向量列表，与输入顺序一一对应，每项为 float 数组
     * @throws \RuntimeException 配置缺失、请求失败或平台返回错误时抛出
     */
    public function embeddings(array $inputs): array;
}
