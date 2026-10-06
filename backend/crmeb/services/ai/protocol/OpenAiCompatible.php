<?php
declare(strict_types=1);

namespace crmeb\services\ai\protocol;

use crmeb\services\ai\BaseProtocol;
use crmeb\services\ai\EmbeddingAiInterface;
use crmeb\services\ai\StreamAiInterface;
use crmeb\utils\Http;

/**
 * OpenAI 兼容协议适配器
 *
 * 覆盖 DeepSeek、通义千问、Kimi、智谱 GLM、Moonshot、豆包、OpenAI 官方，
 * 以及 vLLM / Ollama 等一切自建 OpenAI 兼容网关。
 * 新增该协议族内的厂商无需写代码，只需在后台「模型管理」填 base_url + api_key + model。
 *
 * 配置项：base_url（接口地址）、api_key（密钥）、model（模型标识）、
 * temperature、max_tokens、timeout。
 */
class OpenAiCompatible extends BaseProtocol implements StreamAiInterface, EmbeddingAiInterface
{
    /**
     * AI 模型对话（非流式）
     * @param string $message 对话内容
     * @param array $options 附加选项（prompts: 提示词一维数组）
     * @return array ['content' => string, 'model' => string, 'usage' => array, 'raw' => array]
     * @throws \RuntimeException 配置缺失、请求失败或平台返回错误时抛出
     */
    public function chat(string $message, array $options = []): array
    {
        if ($message === '') {
            throw new \RuntimeException('AI对话内容不能为空');
        }

        [$url, $headers, $model] = $this->prepareRequest();

        $data = [
            'model' => $model,
            'messages' => $this->buildMessages($message, $options),
            'temperature' => $this->temperature(),
            'stream' => false,
        ];
        // max_tokens 为 0 表示交由上游决定，不下发该字段
        if ($this->maxTokens() > 0) {
            $data['max_tokens'] = $this->maxTokens();
        }

        $response = Http::postRequest($url, $this->encodeRequestBody($data), $headers, $this->timeout());
        if ($response === false) {
            // 按 HTTP 状态码区分网络错误与端点错误（非 200 同样返回 false），提示更可定位
            throw new \RuntimeException('AI接口请求失败：' . $this->requestFailReason());
        }

        $result = json_decode($response, true);
        if (!is_array($result)) {
            throw new \RuntimeException('AI接口响应解析失败');
        }
        // OpenAI 兼容服务的错误结构并不完全一致：除标准 error 外，
        // 部分网关会直接返回 message / msg / detail。统一取出可读错误，避免只显示空响应。
        $choices = $this->unwrapChoices($result)['choices'] ?? [];
        if (isset($result['error']) || !is_array($choices) || $choices === []) {
            $message = $this->responseErrorMessage($response, $result);
            if ($message !== '') {
                throw new \RuntimeException('AI接口错误：' . $message);
            }
        }

        return $this->parseChatResult($this->unwrapChoices($result), $model);
    }

    /**
     * AI 模型流式对话
     *
     * 请求上游 stream=true（SSE 格式），通过 curl CURLOPT_WRITEFUNCTION
     * 边接收边解析 data: {...} 增量块，逐段回调 onChunk，实现真流式输出。
     *
     * @param string $message 对话内容
     * @param callable $onChunk 增量回调 function(array $delta): void，$delta = ['type' => 'reasoning'|'content', 'text' => string]
     * @param array $options 附加选项（prompts: 提示词一维数组）
     * @throws \RuntimeException 配置缺失或请求失败时抛出
     */
    public function chatStream(string $message, callable $onChunk, array $options = []): void
    {
        if ($message === '') {
            throw new \RuntimeException('AI对话内容不能为空');
        }

        [$url, $headers] = $this->prepareRequest();

        $data = [
            'model' => $this->model(),
            'messages' => $this->buildMessages($message, $options),
            'temperature' => $this->temperature(),
            'stream' => true,
        ];
        if ($this->maxTokens() > 0) {
            $data['max_tokens'] = $this->maxTokens();
        }

        $lineBuffer = '';   // SSE 行缓冲（一次接收的数据块可能切断行）
        $errorBody = '';    // 非 200 状态码时的错误响应体
        $httpCode = 0;      // 响应状态码
        $finishReason = ''; // 上游给出的结束原因（length/content_filter/stop 等，仅最后增量携带）
        $gotContent = false; // 本次流是否产出过正文（用于识别"零正文"截断）

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $this->encodeRequestBody($data),
            CURLOPT_HTTPHEADER => array_merge($headers, ['Accept: text/event-stream']),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $this->timeout(),
            // 上行携带 API Key 与业务数据，必须校验上游证书与主机名，防止中间人窃取篡改
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            // 边收边解析：上游每推送一块数据立即解析回调
            CURLOPT_WRITEFUNCTION => function ($ch, string $chunk) use (&$lineBuffer, &$errorBody, &$httpCode, &$finishReason, &$gotContent, $onChunk) {
                $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                if ($httpCode >= 400) {
                    // 非 200（如鉴权失败）响应为普通 JSON 错误体，先收集待请求结束后抛出
                    $errorBody .= $chunk;
                    return strlen($chunk);
                }

                $lineBuffer .= $chunk;
                while (($pos = strpos($lineBuffer, "\n")) !== false) {
                    $line = rtrim(substr($lineBuffer, 0, $pos), "\r");
                    $lineBuffer = substr($lineBuffer, $pos + 1);
                    // 按需捕获结束原因（finish_reason 仅最后增量携带，命中关键字才解析，避免重复 json_decode）
                    if ($finishReason === '' && str_starts_with($line, 'data:') && str_contains($line, 'finish_reason')) {
                        $meta = json_decode(trim(substr($line, 5)), true);
                        if (is_array($meta)) {
                            $finishReason = (string) ($meta['choices'][0]['finish_reason'] ?? '');
                        }
                    }
                    // 一行增量可能同时携带思维链与正文片段，逐段回调
                    foreach (self::streamDelta($line) as $delta) {
                        if (($delta['type'] ?? '') === 'content' && trim((string) ($delta['text'] ?? '')) !== '') {
                            $gotContent = true;
                        }
                        $onChunk($delta);
                    }
                }
                return strlen($chunk);
            },
        ]);
        curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 400) {
            $result = json_decode($errorBody, true);
            $msg = $this->responseErrorMessage($errorBody, is_array($result) ? $result : []);
            throw new \RuntimeException('AI接口错误：' . ($msg ?: ($curlError ?: 'HTTP ' . $httpCode)));
        }
        if ($curlError !== '' && $httpCode === 0) {
            throw new \RuntimeException('AI接口请求失败：' . $curlError);
        }
        // 正文零输出但上游给出了明确的截断/过滤信号：抛出可定位的原因，而不是让上层只看到"未返回有效内容"
        if (!$gotContent && in_array($finishReason, ['length', 'content_filter'], true)) {
            throw new \RuntimeException($finishReason === 'length'
                ? 'AI 模型思考过程耗尽了 max_tokens 输出上限，正文被截断为空，请在「AI Agent → 模型管理」调大该模型的输出上限或更换模型'
                : 'AI 模型输出因上游内容安全策略被过滤为空，请调整提问内容或更换模型');
        }
    }

    /**
     * 文本向量化
     *
     * 调用 OpenAI 兼容 /embeddings 端点（通义 text-embedding-v4、智谱 embedding-3、
     * 硅基流动 BAAI/bge-m3 及 vLLM/Ollama 自建网关均兼容该端点），
     * 响应 data 按 index 排序后提取 embedding，保证与输入顺序一一对应。
     *
     * @param array $inputs 待向量化的文本列表
     * @return array 向量列表，与输入顺序一一对应，每项为 float 数组
     * @throws \RuntimeException 配置缺失、请求失败或平台返回错误时抛出
     */
    public function embeddings(array $inputs): array
    {
        $inputs = array_values(array_filter(array_map('strval', $inputs), fn ($item) => $item !== ''));
        if ($inputs === []) {
            throw new \RuntimeException('向量化内容不能为空');
        }

        [$url, $headers, $model] = $this->prepareRequest();

        $response = Http::postRequest($this->embeddingsUrl($url), $this->encodeRequestBody([
            'model' => $model,
            'input' => $inputs,
        ]), $headers, $this->timeout());
        if ($response === false) {
            // 按 HTTP 状态码区分网络错误与端点错误（非 200 同样返回 false），提示更可定位
            throw new \RuntimeException('AI向量接口请求失败：' . $this->requestFailReason());
        }

        $result = json_decode($response, true);
        if (!is_array($result)) {
            throw new \RuntimeException('AI向量接口响应解析失败');
        }
        if (isset($result['error'])) {
            throw new \RuntimeException('AI向量接口错误：' . $this->errorMessage($result['error']));
        }
        $data = $result['data'] ?? [];
        if (!is_array($data) || $data === []) {
            throw new \RuntimeException('AI向量接口未返回向量数据');
        }
        // 按 index 归位，保证输出顺序与输入一致（部分网关乱序返回）
        usort($data, fn ($a, $b) => ((int) ($a['index'] ?? 0)) <=> ((int) ($b['index'] ?? 0)));
        $vectors = [];
        foreach ($data as $item) {
            $vector = $item['embedding'] ?? null;
            if (!is_array($vector) || $vector === []) {
                throw new \RuntimeException('AI向量接口返回的向量结构异常');
            }
            $vectors[] = array_map('floatval', $vector);
        }
        return $vectors;
    }

    /**
     * 校验并整理请求三要素
     * @return array [接口地址, 请求头数组, 模型标识]
     * @throws \RuntimeException 配置缺失时抛出
     */
    protected function prepareRequest(): array
    {
        $baseUrl = trim((string) $this->getConfigField('base_url'));
        if ($baseUrl === '') {
            throw new \RuntimeException('缺少接口地址，请在后台「AI Agent → 模型管理」中填写 base_url');
        }
        $apiKey = trim((string) $this->getConfigField('api_key'));
        if ($apiKey === '') {
            throw new \RuntimeException('缺少 API Key，请在后台「AI Agent → 模型管理」中填写');
        }
        $model = $this->model();
        if ($model === '') {
            throw new \RuntimeException('缺少模型标识，请在后台「AI Agent → 模型管理」中填写 model');
        }

        return [$this->chatUrl($baseUrl), [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ], $model];
    }

    /**
     * 归一化对话接口地址
     *
     * 运营填写的地址可能是网关根地址（https://api.deepseek.com）、
     * 带版本前缀的地址（https://api.deepseek.com/v1）或完整接口地址，统一补全为 chat/completions；
     * 开启完整 URL 模式（full_url=1）时视为地址已是最终请求路径，直接使用不再补全。
     *
     * @param string $baseUrl 后台填写的接口地址
     * @return string 完整对话接口地址
     */
    protected function chatUrl(string $baseUrl): string
    {
        $baseUrl = rtrim($baseUrl, '/');
        // 完整 URL 模式：地址即最终请求路径
        if ((int) $this->getConfigField('full_url') === 1) {
            return $baseUrl;
        }
        // 已写到 chat/completions 结尾时直接使用，避免重复拼接
        if (str_ends_with($baseUrl, '/chat/completions')) {
            return $baseUrl;
        }
        return $baseUrl . '/chat/completions';
    }

    /**
     * 请求失败原因：按是否拿到 HTTP 响应区分端点错误与网络错误
     * @return string
     */
    protected function requestFailReason(): string
    {
        $httpCode = (int) (Http::getStatus()['http_code'] ?? 0);
        if ($httpCode > 0) {
            return 'HTTP ' . $httpCode . '，请检查模型 ID 与请求端点是否匹配';
        }
        return Http::getCurlError() ?: '网络异常，请稍后重试';
    }

    /**
     * 归一化向量接口地址
     *
     * prepareRequest() 返回的是对话地址，这里在完整 URL 模式下直接使用，
     * 其余场景按 chatUrl 同规则把地址补全为 /embeddings 端点。
     * @param string $chatUrl prepareRequest() 归一化后的对话接口地址
     * @return string 完整向量接口地址
     */
    protected function embeddingsUrl(string $chatUrl): string
    {
        // 完整 URL 模式：地址即最终请求路径
        if ((int) $this->getConfigField('full_url') === 1) {
            return $chatUrl;
        }
        // 网关根地址（不带 chat/completions 后缀）直接补 /embeddings
        $baseUrl = rtrim((string) $this->getConfigField('base_url'), '/');
        if ($baseUrl !== '' && !str_ends_with($baseUrl, '/chat/completions')) {
            return $baseUrl . '/embeddings';
        }
        return substr($chatUrl, 0, -strlen('/chat/completions')) . '/embeddings';
    }

    /**
     * 解析单行 SSE 增量文本
     * @param string $line SSE 行内容
     * @return array 带类型增量列表（思维链 reasoning / 正文 content），无增量时返回空数组
     * @throws \RuntimeException 上游推送错误载荷时抛出
     */
    protected static function streamDelta(string $line): array
    {
        if ($line === '' || !str_starts_with($line, 'data:')) {
            return [];
        }
        $payload = trim(substr($line, 5));
        if ($payload === '' || $payload === '[DONE]') {
            return [];
        }
        $json = json_decode($payload, true);
        if (!is_array($json)) {
            return [];
        }
        if (isset($json['error'])) {
            $error = $json['error'];
            throw new \RuntimeException('AI接口错误：' . (is_array($error) ? ($error['message'] ?? '未知错误') : (string) $error));
        }
        // 流式增量位于 choices[0].delta；推理模型的思维链在 delta.reasoning_content
        $delta = $json['choices'][0]['delta'] ?? [];
        $chunks = [];
        $reasoning = (string) ($delta['reasoning_content'] ?? $delta['reasoning'] ?? '');
        if ($reasoning !== '') {
            $chunks[] = ['type' => 'reasoning', 'text' => $reasoning];
        }
        $content = (string) ($delta['content'] ?? '');
        if ($content !== '') {
            $chunks[] = ['type' => 'content', 'text' => $content];
        }
        return $chunks;
    }

    /**
     * 从平台错误结构中提取可读消息
     * @param mixed $error 错误载荷（数组或字符串）
     * @return string
     */
    protected function errorMessage($error): string
    {
        if (is_array($error)) {
            return (string) ($error['message'] ?? ($error['msg'] ?? '未知错误'));
        }
        return $error === null || $error === '' ? '未知错误' : (string) $error;
    }

    /**
     * 提取 OpenAI 兼容网关的错误正文。
     *
     * 各厂商对 4xx 的字段命名并不一致，不能只识别 error.message；
     * 这里兼容标准 error、message、msg、detail 和 data 包装，并对纯文本响应做长度限制。
     * @param string $body 上游原始响应（不会写入前端以外的日志）
     * @param array $result 已解析的 JSON 响应
     * @return string
     */
    protected function responseErrorMessage(string $body, array $result): string
    {
        $candidates = [
            $result['error'] ?? null,
            $result['message'] ?? null,
            $result['msg'] ?? null,
            $result['detail'] ?? null,
            is_array($result['data'] ?? null) ? ($result['data']['error'] ?? $result['data']['message'] ?? $result['data']['msg'] ?? null) : null,
        ];
        foreach ($candidates as $candidate) {
            $message = trim($this->errorMessage($candidate));
            if ($message !== '' && $message !== '未知错误') {
                return mb_substr($message, 0, 500);
            }
        }

        // 少数自建网关会以 text/plain 或 HTML 返回 400；移除标签和多余空白后再展示。
        $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags($body)));
        return $plain === '' ? '' : mb_substr($plain, 0, 500);
    }
}
