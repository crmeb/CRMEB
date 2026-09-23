<?php
declare(strict_types=1);

namespace modules\aiagent\controller\admin;

use app\adminapi\controller\AuthController;
use modules\aiagent\service\AgentChatService;
use modules\aiagent\service\AiAgentServices;
use think\facade\Log;

/** AI Agent 后台接口。 */
class AiAgentController extends AuthController
{
    /** @var AiAgentServices Agent 会话服务 */
    private AiAgentServices $agents;

    /** @var AgentChatService Agent 对话服务 */
    private AgentChatService $chatService;

    protected function initialize()
    {
        parent::initialize();
        $this->agents = app()->make(AiAgentServices::class);
        $this->chatService = app()->make(AgentChatService::class);
    }

    /**
     * 对话基础选项（可用模型、连接器与 Skill 清单）
     * @return mixed
     */
    public function options()
    {
        return app('json')->success($this->chatService->options());
    }

    /**
     * 会话列表（当前管理员的全部会话）
     * @return mixed
     */
    public function conversations()
    {
        return app('json')->success($this->agents->conversations((int)$this->adminId));
    }

    /**
     * 会话消息明细
     * @param int $conversationId 会话 ID
     * @return mixed
     */
    public function messages(int $conversationId)
    {
        return app('json')->success($this->agents->messages($conversationId, (int)$this->adminId));
    }

    /**
     * 删除会话（仅限本人会话）
     * @param int $id 会话 ID
     * @return mixed
     */
    public function deleteConversation(int $id)
    {
        $this->agents->deleteConversation($id, (int)$this->adminId);
        return app('json')->success('删除成功');
    }

    /**
     * 修改会话（标题/置顶，仅限本人会话，title 与 is_pin 至少传一项）
     * @param int $id 会话 ID
     * @return mixed
     */
    public function updateConversation(int $id)
    {
        $title = trim((string)$this->request->param('title', ''));
        // is_pin 未传时置为 -1（不修改），避免与「取消置顶 0」混淆
        $isPin = $this->request->param('is_pin');
        $this->agents->updateConversation($id, (int)$this->adminId, $title, $isPin === null ? -1 : (int)$isPin);
        return app('json')->success('修改成功');
    }

    /**
     * 发送对话消息（会话直接挂载，支持选择模型/连接器/Skill）
     * @return mixed
     */
    public function chat()
    {
        $data = $this->request->postMore([
            ['conversation_id', 0],
            ['message', ''],
            ['model_id', 0],
            ['mcp_server_ids', []],
            ['skill_keys', []],
        ]);
        $result = $this->chatService->chat(
            (int)$this->adminId,
            (int)$data['conversation_id'],
            (string)$data['message'],
            (int)$data['model_id'],
            (array)$data['mcp_server_ids'],
            (array)$data['skill_keys']
        );
        return app('json')->success($result);
    }

    /**
     * 流式对话（SSE）：思维链与正文逐块推送
     *
     * 事件载荷为 JSON：start / reasoning / delta / tool / done / error
     * @return void 直接输出 SSE 响应流
     */
    public function chatStream()
    {
        $data = $this->request->postMore([
            ['conversation_id', 0],
            ['message', ''],
            ['model_id', 0],
            ['mcp_server_ids', []],
            ['skill_keys', []],
        ]);

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

        $send = function (array $payload): void {
            echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
            if (ob_get_level() > 0) {
                @ob_flush();
            }
            flush();
        };

        try {
            $this->chatService->chatStream(
                (int)$this->adminId,
                (int)$data['conversation_id'],
                (string)$data['message'],
                (int)$data['model_id'],
                (array)$data['mcp_server_ids'],
                (array)$data['skill_keys'],
                $send
            );
        } catch (\Throwable $e) {
            $send(['event' => 'error', 'msg' => $this->streamErrorMessage($e, 'chatStream')]);
        }
        exit;
    }

    /**
     * 流式处理交互卡片（SSE）：execute 类 approve=按快照执行 / reject=取消固定话术；
     * ask_user 类 approve=用户答案回传模型继续 / reject=以未回答结果回传
     *
     * 事件载荷与 chatStream 一致：start / reasoning / delta / tool / confirm / done / error
     * @return void 直接输出 SSE 响应流
     */
    public function confirmStream()
    {
        $data = $this->request->postMore([
            ['conversation_id', 0],
            ['message_id', 0],
            ['action', 'approve'],
            ['answers', []],
            ['custom', ''],
        ]);

        // SSE 响应头（与 chatStream 一致）
        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache, private');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        @ini_set('zlib.output_compression', '0');
        @set_time_limit(0);

        $send = function (array $payload): void {
            echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
            if (ob_get_level() > 0) {
                @ob_flush();
            }
            flush();
        };

        try {
            $this->chatService->confirmStream(
                (int)$this->adminId,
                (int)$data['conversation_id'],
                (int)$data['message_id'],
                (string)$data['action'] === 'reject' ? 'reject' : 'approve',
                is_array($data['answers']) ? array_values(array_map('strval', $data['answers'])) : [],
                (string)$data['custom'],
                $send
            );
        } catch (\Throwable $e) {
            $send(['event' => 'error', 'msg' => $this->streamErrorMessage($e, 'confirmStream')]);
        }
        exit;
    }

    /**
     * 流式接口的异常出参脱敏：业务异常（RuntimeException）消息可下发，
     * 其余异常一律返回通用话术，避免把内部路径、调用栈等敏感信息透传给客户端；
     * 任何异常全量入日志供排障
     * @param \Throwable $e 捕获的异常
     * @param string $scene 场景标识（chatStream/confirmStream）
     * @return string 可安全下发给前端的错误消息
     */
    private function streamErrorMessage(\Throwable $e, string $scene): string
    {
        Log::error('[AiAgent] 流式接口异常', [
            'scene'     => $scene,
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'code'      => $e->getCode(),
        ]);
        if ($e instanceof \RuntimeException) {
            return $e->getMessage();
        }
        return '服务异常，请稍后重试';
    }
}
