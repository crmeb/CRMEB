<?php
declare(strict_types=1);

namespace crmeb\services\ai;

/**
 * AI 协议适配器接口契约
 *
 * 所有内置协议适配器（OpenAI 兼容、一号通）必须实现本接口，
 * 由 crmeb\services\ai\AiProtocol 工厂按模型记录的 protocol 字段创建实例。
 */
interface AiInterface
{
    /**
     * 注入模型配置（由调用方从 eb_ai_model 记录解析后的键值对）
     * @param array $config 配置键值对
     */
    public function setConfig(array $config): void;

    /**
     * AI 模型对话
     * @param string $message 对话内容
     * @param array $options 附加选项（prompts: 提示词一维数组；stream: 0=直接返回 1=流式响应）
     * @return array 返回 ['content' => string, 'model' => string, 'usage' => array, 'raw' => array]
     */
    public function chat(string $message, array $options = []): array;
}
