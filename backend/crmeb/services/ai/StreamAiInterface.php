<?php
declare(strict_types=1);

namespace crmeb\services\ai;

/**
 * AI 流式对话协议适配器接口契约
 *
 * 支持流式输出（SSE）的协议适配器在实现 AiInterface 基础上额外实现本接口，
 * 由 crmeb\services\ai\AiProtocol 工厂产出的实例携带该能力；调用方检测能力后：
 * 实现本接口走真流式逐块回调，否则自动降级为非流式一次性输出。
 */
interface StreamAiInterface
{
    /**
     * AI 模型流式对话
     * @param string $message 对话内容
     * @param callable $onChunk 增量回调 function(array $delta): void
     *                          $delta 为带类型的增量片段：['type' => 'reasoning'|'content', 'text' => string]，
     *                          reasoning 为推理模型思维链增量，content 为正文增量
     * @param array $options 附加选项（prompts: 提示词一维数组）
     */
    public function chatStream(string $message, callable $onChunk, array $options = []): void;
}
