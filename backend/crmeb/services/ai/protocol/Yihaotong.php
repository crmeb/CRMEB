<?php
declare(strict_types=1);

namespace crmeb\services\ai\protocol;

use crmeb\services\ai\BaseProtocol;
use crmeb\services\ai\StreamAiInterface;
use crmeb\services\yihaotong\AccessTokenServeService;
use crmeb\utils\Http;

/**
 * 一号通 AI 协议适配器
 *
 * 对接一号通平台对话接口（POST v2/chat/conversation）。
 * 与 OpenAI 兼容协议的差异在于凭证：一号通 appid / appsecret 由「一号通登录」页
 * 一次性写入 eb_system_config，全站（短信/物流/AI）共享，因此模型记录中无需再填 api_key。
 */
class Yihaotong extends BaseProtocol implements StreamAiInterface
{
    /**
     * 一号通对话接口（相对网关地址）
     */
    const API_CHAT = 'v2/chat/conversation';

    /**
     * @var AccessTokenServeService|null 一号通API服务（延迟初始化，token 自动缓存）
     */
    protected ?AccessTokenServeService $accessToken = null;

    /**
     * AI 模型对话（非流式）
     * @param string $message 对话内容
     * @param array $options 附加选项（prompts: 提示词一维数组；stream: 0=直接返回 1=流式响应）
     * @return array ['content' => string, 'model' => string, 'usage' => array, 'raw' => array]
     * @throws \RuntimeException 凭证缺失、请求失败或平台返回错误时抛出
     */
    public function chat(string $message, array $options = []): array
    {
        if ($message === '') {
            throw new \RuntimeException('AI对话内容不能为空');
        }

        $service = $this->getAccessToken();
        $data = [
            'message' => $message,
            'stream' => (int) ($options['stream'] ?? 0),
        ];
        // 提示词以 assistant_message 下发（与 OpenAI 兼容协议的 system 消息语义一致）
        if (!empty($options['prompts'])) {
            $data['assistant_message'] = array_values(array_map('strval', (array) $options['prompts']));
        }
        if ($this->model() !== '') {
            $data['model'] = $this->model();
        }

        $response = Http::postRequest(
            $service->get(self::API_CHAT),
            $this->encodeRequestBody($data),
            ['Authorization:Bearer-' . $service->getToken(), 'Content-Type: application/json'],
            $this->timeout()
        );
        if ($response === false) {
            // 瞬时网络抖动透明重试一次，仍失败才按请求失败处理
            $response = Http::postRequest(
                $service->get(self::API_CHAT),
                $this->encodeRequestBody($data),
                ['Authorization:Bearer-' . $service->getToken(), 'Content-Type: application/json'],
                $this->timeout()
            );
        }
        if ($response === false) {
            throw new \RuntimeException('一号通AI接口请求失败：' . (Http::getCurlError() ?: '网络异常，请稍后重试'));
        }

        $result = json_decode($response, true);
        if (!is_array($result)) {
            throw new \RuntimeException('一号通AI接口响应解析失败');
        }
        // 兼容平台 code / status 两种成功标识
        $code = (int) ($result['code'] ?? $result['status'] ?? 0);
        if ($code !== 200) {
            throw new \RuntimeException('一号通AI接口错误：' . ($result['msg'] ?? '未知错误'));
        }

        return $this->parseChatResult($this->unwrapChoices($result), $this->model());
    }

    /**
     * AI 模型流式对话
     *
     * 请求一号通 stream=1，通过 curl CURLOPT_WRITEFUNCTION 边接收边解析增量：
     * 兼容 SSE（data: {...} JSON）与纯文本增量两种上游推送格式，逐段回调 onChunk。
     *
     * @param string $message 对话内容
     * @param callable $onChunk 增量回调 function(array $delta): void，$delta = ['type' => 'reasoning'|'content', 'text' => string]
     * @param array $options 附加选项（prompts: 提示词一维数组）
     * @throws \RuntimeException 凭证缺失或请求失败时抛出
     */
    public function chatStream(string $message, callable $onChunk, array $options = []): void
    {
        if ($message === '') {
            throw new \RuntimeException('AI对话内容不能为空');
        }

        $service = $this->getAccessToken();
        $data = [
            'message' => $message,
            'stream' => 1,
        ];
        if (!empty($options['prompts'])) {
            $data['assistant_message'] = array_values(array_map('strval', (array) $options['prompts']));
        }
        if ($this->model() !== '') {
            $data['model'] = $this->model();
        }

        $lineBuffer = '';   // 行缓冲（一次接收的数据块可能切断行）
        $errorBody = '';    // 非 200 状态码时的错误响应体
        $httpCode = 0;      // 响应状态码
        $finishReason = ''; // 上游给出的结束原因（length/content_filter/stop 等，仅最后增量携带）
        $gotContent = false; // 本次流是否产出过正文（用于识别"零正文"截断）
        $curlError = '';    // curl 层错误（TLS 断连等）
        $pushed = false;    // 是否已向调用方推送过任何增量（断连重试时防止内容重复推送）

        $wrappedOnChunk = function (array $delta) use (&$pushed, $onChunk) {
            $pushed = true;
            $onChunk($delta);
        };

        // 一号通网关在部分网络环境下会异常断开 TLS 连接（握手期报 connect error、
        // 读取期静默 EOF）：未推送过任何增量时自动重试两次（含短间隔），重试仍失败再按请求失败抛出
        $attempt = 0;
        do {
            $attempt++;
            if ($attempt > 1) {
                usleep(200000); // 重试前短暂等待，给链路/网关恢复时间
            }
            $lineBuffer = '';
            $errorBody = '';
            $httpCode = 0;
            $finishReason = '';
            $gotContent = false;

            $ch = curl_init($service->get(self::API_CHAT));
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $this->encodeRequestBody($data),
                CURLOPT_HTTPHEADER => ['Authorization:Bearer-' . $service->getToken(), 'Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => $this->timeout(),
                // 上行携带一号通凭证与业务数据，必须校验网关证书与主机名，防止中间人窃取篡改
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                // 一号通流式网关在部分网络环境下会提前断开 HTTP/2 的 TLS 长连接；
                // 固定 HTTP/1.1 后仍为 SSE 流式传输，并可避免该协商问题。
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                // 边收边解析：上游每推送一块数据立即解析回调
                CURLOPT_WRITEFUNCTION => function ($ch, string $chunk) use (&$lineBuffer, &$errorBody, &$httpCode, &$finishReason, &$gotContent, $wrappedOnChunk) {
                    $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                    if ($httpCode >= 400) {
                        // 非 200 响应为普通 JSON 错误体，先收集待请求结束后抛出
                        $errorBody .= $chunk;
                        return strlen($chunk);
                    }

                    $lineBuffer .= $chunk;
                    while (($pos = strpos($lineBuffer, "\n")) !== false) {
                        $line = rtrim(substr($lineBuffer, 0, $pos), "\r");
                        $lineBuffer = substr($lineBuffer, $pos + 1);
                        if ($line === '') {
                            continue;
                        }
                        // 按需捕获结束原因（finish_reason 仅最后增量携带，命中关键字才解析，避免重复 json_decode）
                        if ($finishReason === '' && str_starts_with($line, 'data:') && str_contains($line, 'finish_reason')) {
                            $meta = json_decode(trim(substr($line, 5)), true);
                            if (is_array($meta)) {
                                $finishReason = (string) ($meta['choices'][0]['finish_reason'] ?? '');
                            }
                        }
                        foreach (self::parseStreamLine($line) as $delta) {
                            if (($delta['type'] ?? '') === 'content' && trim((string) ($delta['text'] ?? '')) !== '') {
                                $gotContent = true;
                            }
                            $wrappedOnChunk($delta);
                        }
                    }
                    return strlen($chunk);
                },
            ]);
            curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);
        } while ($curlError !== '' && !$pushed && $attempt < 3);

        if ($httpCode >= 400) {
            $result = json_decode($errorBody, true);
            $msg = is_array($result) ? ($result['msg'] ?? $result['error']['message'] ?? '') : '';
            throw new \RuntimeException('一号通AI接口错误：' . ($msg ?: ($curlError ?: 'HTTP ' . $httpCode)));
        }
        // 断连且零正文（含响应头 200 已发出、正文传输中途被网关掐断的场景）不能静默当作正常结束，
        // 否则上层只会看到"未返回任何内容"，无法定位真实原因
        if ($curlError !== '' && ($httpCode === 0 || !$gotContent)) {
            throw new \RuntimeException('一号通AI接口请求失败：' . $curlError);
        }
        // 正文零输出但上游给出了明确的截断/过滤信号：抛出可定位的原因，而不是让上层只看到"未返回有效内容"
        if (!$gotContent && in_array($finishReason, ['length', 'content_filter'], true)) {
            throw new \RuntimeException($finishReason === 'length'
                ? 'AI 模型输出已达长度上限被截断为空，请在「AI Agent → 模型管理」调大该模型的输出上限或更换模型'
                : 'AI 模型输出因内容安全策略被过滤为空，请调整提问内容或更换模型');
        }
        // 行缓冲残留（上游最后一行未带换行符）：作为最后一段增量输出
        $lastLine = trim($lineBuffer, "\r\n");
        if ($lastLine !== '') {
            foreach (self::parseStreamLine($lastLine) as $delta) {
                $onChunk($delta);
            }
        }
    }

    /**
     * 获取一号通API服务（延迟初始化）
     *
     * 凭证优先取全局系统配置（eb_system_config：yihaotong_appid / yihaotong_appsecret），
     * 模型记录中显式填写的 appid / appsecret 仅作兜底。
     *
     * @return AccessTokenServeService
     * @throws \RuntimeException 凭证缺失时抛出
     */
    protected function getAccessToken(): AccessTokenServeService
    {
        if (!$this->accessToken) {
            $account = sys_config('yihaotong_appid', '') ?: (string) $this->getConfigField('appid');
            $secret = sys_config('yihaotong_appsecret', '') ?: (string) $this->getConfigField('appsecret');
            if ($account === '' || $secret === '') {
                throw new \RuntimeException('缺少一号通凭证，请先在后台完成一号通登录/设置');
            }
            $this->accessToken = new AccessTokenServeService($account, $secret);
        }
        return $this->accessToken;
    }

    /**
     * 解析单行流式载荷
     *
     * 一号通可能推送 SSE 格式（data: {...} / data: 纯文本）或纯文本增量流，
     * 统一归一化为带类型增量数组返回（思维链 reasoning / 正文 content）。
     *
     * @param string $line 单行内容
     * @return array 增量列表（0~2 项），$delta = ['type' => 'reasoning'|'content', 'text' => string]
     * @throws \RuntimeException 载荷为平台错误时抛出
     */
    protected static function parseStreamLine(string $line): array
    {
        if (str_starts_with($line, 'data:')) {
            $payload = trim(substr($line, 5));
            if ($payload === '' || $payload === '[DONE]') {
                return [];
            }
            self::throwIfErrorPayload($payload);
            $json = json_decode($payload, true);
            if (!is_array($json)) {
                // data: 后跟纯文本增量
                return [['type' => 'content', 'text' => $payload]];
            }
            // 兼容多种增量字段结构（OpenAI delta / 一号通 content），思维链字段同步提取
            $delta = $json['choices'][0]['delta']
                ?? $json['choices'][0]['message']
                ?? $json['data']
                ?? $json;
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

        if (preg_match('/^(event|id|retry|:)/', $line)) {
            // SSE 控制行，忽略
            return [];
        }
        // 非 SSE 格式：纯文本增量流，先检测是否为错误载荷再透传（保留换行符）
        self::throwIfErrorPayload($line);
        return [['type' => 'content', 'text' => $line . "\n"]];
    }

    /**
     * 检测响应载荷是否为平台错误（如 {"status":400,"msg":"..."} / {"code":400,"msg":"..."}），是则抛出异常
     * @param string $text 响应载荷文本
     * @throws \RuntimeException 平台返回错误时抛出
     */
    protected static function throwIfErrorPayload(string $text): void
    {
        $text = trim($text);
        if ($text !== '' && str_starts_with($text, '{')) {
            $json = json_decode($text, true);
            // 带 choices 的正常增量不会被判为错误；兼容平台 status / code 两种错误标识，
            // 避免错误载荷被当作零增量静默吞掉、上层只看到"未返回有效内容"
            if (is_array($json) && !isset($json['choices'])) {
                $status = (int) ($json['status'] ?? $json['code'] ?? 0);
                if ($status !== 0 && $status !== 200) {
                    throw new \RuntimeException('一号通AI接口错误：' . ($json['msg'] ?? $json['message'] ?? '未知错误'));
                }
            }
        }
    }
}
