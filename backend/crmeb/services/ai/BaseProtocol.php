<?php
declare(strict_types=1);

namespace crmeb\services\ai;

/**
 * AI 协议适配器基类
 *
 * AI 能力为系统内置功能（不再走 driver/ 可插拔驱动目录），按「协议族」而非「厂商」拆分实现：
 * 同一协议族内的不同厂商只需在后台「AI Agent → 模型管理」新增一条模型记录（填 base_url / api_key / model），
 * 零代码即可接入；仅当出现新的协议族（如 Anthropic、Gemini）时才需要新增本类的子类。
 *
 * 本类只负责配置的读取与消息结构组装，不访问任何数据表：
 * 模型记录（eb_ai_model）的解析由调用方（modules/aiagent 的模型服务）完成后以键值对注入。
 */
abstract class BaseProtocol implements AiInterface
{
    /**
     * 模型配置（由调用方从模型记录解析后注入）
     * @var array
     */
    protected array $config = [];

    /**
     * 注入模型配置
     * @param array $config 配置键值对（api_key / base_url / model / temperature / max_tokens / timeout）
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * 读取单个配置项
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    protected function getConfigField(string $key, $default = '')
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * 读取待调用的模型标识
     * @return string
     */
    protected function model(): string
    {
        return trim((string) $this->getConfigField('model'));
    }

    /**
     * 读取采样温度（缺省 0.70）
     * @return float
     */
    protected function temperature(): float
    {
        $value = (float) $this->getConfigField('temperature', 0.7);
        // 温度夹在 OpenAI 兼容协议通用区间内，避免脏数据导致上游直接报错
        return max(0, min(2, $value));
    }

    /**
     * 读取单次回复最大 token 数（0 表示不限制、不下发该参数）
     * @return int
     */
    protected function maxTokens(): int
    {
        return max(0, (int) $this->getConfigField('max_tokens', 0));
    }

    /**
     * 读取接口超时秒数（缺省 120）
     * @return int
     */
    protected function timeout(): int
    {
        $timeout = (int) $this->getConfigField('timeout', 120);
        return $timeout > 0 ? $timeout : 120;
    }

    /**
     * 组装 OpenAI 兼容消息结构
     *
     * prompts（提示词一维数组）作为 system 消息前置，其后为当轮用户消息。
     *
     * @param string $message 用户消息内容
     * @param array $options 附加选项（prompts: 提示词一维数组）
     * @return array messages 消息数组
     */
    protected function buildMessages(string $message, array $options = []): array
    {
        $messages = [];
        foreach ((array) ($options['prompts'] ?? []) as $prompt) {
            $prompt = trim((string) $prompt);
            if ($prompt !== '') {
                $messages[] = ['role' => 'system', 'content' => $prompt];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];
        return $messages;
    }

    /**
     * 安全编码上游请求体。
     *
     * Skill、MCP 返回值或历史消息可能含有非 UTF-8 字节。原先直接 json_encode()
     * 在这种情况下会返回 false，curl 会把它当作空请求体发出，导致上游报 JSON EOF。
     * 统一替换非法字节并显式抛错，确保不会静默发送空 body。
     * @param array $data 请求数据
     * @return string
     */
    protected function encodeRequestBody(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false || $json === '') {
            throw new \RuntimeException('AI请求参数编码失败：' . json_last_error_msg());
        }
        return $json;
    }

    /**
     * 剥离响应体中可能的多层 data 包装，直至定位到 choices 结构
     * @param array $result 已解码的响应数组
     * @return array
     */
    protected function unwrapChoices(array $result): array
    {
        $data = $result;
        while (is_array($data) && !isset($data['choices']) && isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }
        return is_array($data) ? $data : [];
    }

    /**
     * 从 choices 结构中提取标准对话结果
     * @param array $data 已剥离包装的响应数组
     * @param string $fallbackModel 响应未回传模型名时使用的兜底模型标识
     * @return array ['content' => string, 'reasoning' => string, 'model' => string, 'usage' => array, 'raw' => array]
     */
    protected function parseChatResult(array $data, string $fallbackModel = ''): array
    {
        $message = $data['choices'][0]['message'] ?? [];
        return [
            'content' => (string) ($message['content'] ?? ''),
            // 推理模型（DeepSeek R1 / GLM-Zero 等）的思维过程字段
            'reasoning' => trim((string) ($message['reasoning_content'] ?? $message['reasoning'] ?? '')),
            'model' => (string) ($data['model'] ?? $fallbackModel),
            'usage' => (array) ($data['usage'] ?? []),
            'raw' => $data,
        ];
    }
}
