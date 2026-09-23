<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\ai;

use app\adminapi\controller\AuthController;
use app\services\ai\AiModelServices;
use crmeb\services\ai\AiProtocol;

/**
 * AI 对话
 *
 * 提供后台管理端统一 AI 对话接口：显式传入 model_id 时使用「AI Agent → 模型管理」
 * 登记的模型（eb_ai_model），未传时默认调用系统内置一号通 AI（复用全局一号通凭证），
 * 协议适配由 crmeb\services\ai\AiProtocol 内置完成。
 * Class AiChatController
 * @package app\adminapi\controller\ai
 */
class AiChatController extends AuthController
{
    /**
     * AI 流式对话（SSE）
     *
     * 以 text/event-stream 逐块推送，事件载荷为 JSON：
     * - {event: start}             开始生成
     * - {event: delta, content}    增量文本
     * - {event: done}              生成完成
     * - {event: error, msg}        生成异常
     *
     * 入参：prompt（必填）、prompts（系统提示词数组）、model_id（0=默认调用一号通 AI）
     *
     * @return void 直接输出 SSE 响应流
     */
    public function chatStream()
    {
        [$prompt, $prompts, $modelId] = $this->request->postMore([
            ['prompt', ''],
            ['prompts', []],
            ['model_id', 0],
        ], true);

        if (trim((string) $prompt) === '') {
            return app('json')->fail('请输入AI提示词');
        }
        $prompts = array_values(array_filter(array_map('strval', (array) $prompts), fn ($p) => trim($p) !== ''));

        // SSE 响应头（X-Accel-Buffering: no 关闭 nginx 代理缓冲，保证逐块推送）
        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache, private');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        // 清空全部输出缓冲并取消脚本超时限制，保证流式持续输出
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        @ini_set('zlib.output_compression', '0');
        @set_time_limit(0);

        // SSE 事件推送
        $send = function (array $payload): void {
            echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
            if (ob_get_level() > 0) {
                @ob_flush();
            }
            flush();
        };

        try {
            // 未指定 model_id 时默认走内置一号通 AI
            $handler = app()->make(AiModelServices::class)->getChatHandler((int) $modelId);
            $send(['event' => 'start']);
            AiProtocol::chatStream($handler, (string) $prompt, function (array $delta) use ($send) {
                // 思维链与正文分别以独立事件推送（reasoning 为推理模型思维链增量）
                $event = ($delta['type'] ?? 'content') === 'reasoning' ? 'reasoning' : 'delta';
                $send(['event' => $event, 'content' => (string) ($delta['text'] ?? '')]);
            }, ['prompts' => $prompts]);
            $send(['event' => 'done']);
        } catch (\Throwable $e) {
            $send(['event' => 'error', 'msg' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * AI 对话（非流式，普通 JSON 返回）
     *
     * 适用于无需流式展示的场景，同步等待生成完成后一次性返回结果。
     *
     * @return mixed
     */
    public function chat()
    {
        [$prompt, $prompts, $modelId] = $this->request->postMore([
            ['prompt', ''],
            ['prompts', []],
            ['model_id', 0],
        ], true);

        if (trim((string) $prompt) === '') {
            return app('json')->fail('请输入AI提示词');
        }
        $prompts = array_values(array_filter(array_map('strval', (array) $prompts), fn ($p) => trim($p) !== ''));

        $handler = app()->make(AiModelServices::class)->getChatHandler((int) $modelId);
        $result = $handler->chat((string) $prompt, ['prompts' => $prompts, 'stream' => 0]);
        return app('json')->success([
            'content' => (string) ($result['content'] ?? ''),
            'model' => (string) ($result['model'] ?? ''),
        ]);
    }
}
