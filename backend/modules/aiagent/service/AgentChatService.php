<?php
declare(strict_types=1);

namespace modules\aiagent\service;

use app\services\ai\AiModelServices;
use crmeb\services\ai\AiProtocol;
use think\facade\Log;

/** 执行带 Skill 上下文和 MCP 工具循环的 AI 对话（会话直接挂载，不依赖具体 Agent）。 */
class AgentChatService
{
    /** 单次对话允许的最大工具调用轮数（需容纳多步确认：问询/执行可多轮交替） */
    private const MAX_STEPS = 6;

    /** 用户消息长度上限（字符）：服务端兜底校验，防止超长消息撑爆上下文与会话存储 */
    private const MAX_MESSAGE_LENGTH = 8000;

    /** Skill 按需加载虚拟工具名：上下文仅注入 Skill 目录（名称+说明），模型判断相关后调用它加载全文规则 */
    private const SKILL_LOAD_TOOL = 'crmeb_skill_load';

    /** 邮件发送工具名（内置 MCP）：无人值守场景的收件地址受 guardUnattendedEmail 约束 */
    private const EMAIL_TOOL = 'crmeb_email_send';

    /** @var AiAgentServices Agent 会话服务 */
    private AiAgentServices $agents;

    /** @var McpToolService MCP 工具调用服务 */
    private McpToolService $mcp;

    /** @var SkillRepository Skill 仓库服务 */
    private SkillRepository $skills;

    public function __construct(
        AiAgentServices $agents,
        McpToolService $mcp,
        SkillRepository $skills
    ) {
        $this->agents = $agents;
        $this->mcp = $mcp;
        $this->skills = $skills;
    }

    /**
     * 对话页选项：模型（首位为内置一号通模型）、连接器（MCP 服务粒度）、启用中的 Skill
     * @return array
     */
    public function options(): array
    {
        $models = app()->make(AiModelServices::class)->getAvailableModels();
        // 系统默认唯一：存在登记的默认模型时，一号通项退化为普通选项，不再标记默认
        $hasRegisteredDefault = (bool) array_filter($models, fn(array $model) => (int)$model['is_default'] === 1);
        array_unshift($models, [
            'id'             => 0,
            'name'           => $hasRegisteredDefault ? '一号通AI' : '一号通AI(系统内置)',
            'protocol'       => AiModelServices::DEFAULT_PROTOCOL,
            'protocol_name'  => AiProtocol::name(AiModelServices::DEFAULT_PROTOCOL),
            'provider'       => '',
            'model'          => '',
            'supports_tools' => 1,
            'is_default'     => $hasRegisteredDefault ? 0 : 1,
        ]);
        return [
            'models'        => $models,
            'mcp_available' => $this->mcp->available(),
            'mcp_servers'   => $this->mcp->serverOptions(),
            'skills'        => array_values(array_filter(
                $this->skills->all(),
                fn(array $skill) => (int)$skill['status'] === 1
            )),
        ];
    }

    /**
     * 发送对话消息（工具调用循环）
     * @param int $adminId 管理员 ID
     * @param int $conversationId 会话 ID（0 表示新建会话）
     * @param string $message 用户消息
     * @param int $modelId 模型 ID（0 = 内置一号通默认）
     * @param array $serverIds 选中的连接器（MCP Server）ID 列表
     * @param array $skillKeys 选中的 Skill 标识列表
     * @param array $runtimeOptions 运行时选项（自动化任务无人值守使用）：
     *   unattended=bool 无人值守模式；autonomy=int 自主级别（1只读/3全自动）；
     *   max_steps=int 工具调用轮数上限；deadline=int 软超时时间戳（超过后强制最终答复）
     * @return array
     */
    public function chat(
        int $adminId,
        int $conversationId,
        string $message,
        int $modelId = 0,
        array $serverIds = [],
        array $skillKeys = [],
        array $runtimeOptions = []
    ): array {
        $message = trim($message);
        if ($message === '') {
            throw new \RuntimeException('请输入对话内容');
        }
        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            throw new \RuntimeException('对话内容不能超过 ' . self::MAX_MESSAGE_LENGTH . ' 字');
        }
        $unattended = !empty($runtimeOptions['unattended']);
        $maxSteps = max(1, (int)($runtimeOptions['max_steps'] ?? self::MAX_STEPS));
        $deadline = (int)($runtimeOptions['deadline'] ?? 0);

        if ($conversationId > 0) {
            $this->agents->assertConversation($conversationId, $adminId);
        } else {
            $conversationId = $this->agents->createConversation(0, $adminId, $message);
        }
        $this->agents->addMessage($conversationId, 'user', $message);
        // 新用户消息到达：旧的待确认操作自动过期，避免旧确认卡与新上下文错位
        $this->agents->expirePendingConfirms($conversationId);

        $context = $this->prepareContext($conversationId, $serverIds, $skillKeys, $modelId, [], $runtimeOptions, $adminId);
        $toolTrace = [];
        $attempted = [];
        // 上游 200 空响应重试计数：透明重试 1 次，仍为空才报错
        $emptyRetries = 0;
        // 残缺工具调用 JSON 修复计数：输出被截断导致 JSON 无法解析时注入系统提示重试，限 1 次
        $repairRetries = 0;
        $lastModel = '';
        $lastUsage = [];

        for ($step = 0; $step <= $maxSteps; $step++) {
            // 软超时：超过 deadline 后不再发起新的工具轮次，转入循环外的强制最终答复
            if ($deadline > 0 && $step > 0 && time() >= $deadline) {
                break;
            }
            $result = $context['driver']->chat($context['workingMessage'], ['prompts' => $context['prompts'], 'stream' => 0]);
            $content = trim((string)($result['content'] ?? ''));
            $reasoning = trim((string)($result['reasoning'] ?? ''));
            $lastModel = (string)($result['model'] ?? '');
            $lastUsage = (array)($result['usage'] ?? []);
            $toolCall = $this->resolveToolCall($content, $reasoning);
            if ($content === '' && !$toolCall) {
                // 上游请求成功但零正文：有思考无正文多为推理模型思考耗尽 max_tokens，给出可定位的指引；
                // 零正文零思考多为网关/上游瞬时异常，透明重试一次，仍为空才报错
                if ($reasoning !== '') {
                    throw new \RuntimeException('AI 模型仅返回了思考过程、未产出正文，常见原因是推理模型耗尽 max_tokens，请在「AI Agent → 模型管理」调大输出上限或更换模型');
                }
                if ($emptyRetries < 1) {
                    $emptyRetries++;
                    continue;
                }
                Log::error('[AgentChat] 上游空响应', [
                    'message_len' => strlen($context['workingMessage']),
                    'prompts_len' => array_sum(array_map('strlen', $context['prompts'])),
                ]);
                throw new \RuntimeException('AI 模型未返回有效内容，请稍后重试或检查所选模型配置');
            }

            if (!$toolCall) {
                // 剥离前先识别原始输出是否为工具调用 JSON（含起点标记但解析失败/未闭合，多为被 max_tokens 截断）
                $brokenToolCall = str_contains($content, '{"tool_call"') || str_contains($content, '<|FunctionCallBegin|>');
                $content = $this->stripToolCallJson($content);
                $thinkReasoning = $this->extractThinkBlocks($content);
                if ($reasoning === '') {
                    $reasoning = $thinkReasoning;
                }
                if ($content === '') {
                    // 模型只输出了无法解析的工具调用 JSON：先注入系统提示要求重新输出（限一次自愈），
                    // 仍失败再优雅兜底，避免整个请求报错
                    if ($brokenToolCall && $repairRetries < 1) {
                        $repairRetries++;
                        $context['workingMessage'] .= "\n\n系统提示：你上一条回复输出的工具调用 JSON 不完整或无法解析（常见原因是输出被长度截断）。请重新输出：需要继续操作时，只输出一个完整且精简的工具调用 JSON（display 从简，不要输出其他叙述）；已有信息足够时，直接输出包含完整结果的最终答复。禁止重复输出残缺内容。";
                        continue;
                    }
                    if ($toolTrace !== []) {
                        Log::error('[AgentChat] 模型仅输出残缺工具调用 JSON，已兜底', ['raw_head' => mb_substr((string)($result['content'] ?? ''), 0, 500)]);
                        $content = '抱歉，本次未能基于工具结果生成有效答复，请重试或调整提问方式。';
                    } else {
                        Log::error('[AgentChat] 模型仅输出无法解析的内容', ['raw_head' => mb_substr((string)($result['content'] ?? ''), 0, 500)]);
                        throw new \RuntimeException('AI 模型未返回有效内容，请检查所选模型配置');
                    }
                }
                // 正文提问兜底：模型未走 crmeb_ask_user 而以正文直接向用户提问时，
                // 由服务端把该回复强制转为问询卡片，确保用户端弹出输入框（不依赖模型自觉）。
                // 提前剥离追问 JSON 防止混入卡片问题，结果暂存供最终答复复用，避免标签丢失
                $earlyFollowups = $this->extractFollowups($content);
                if ($content === '') {
                    // 整轮输出被剥离后只剩追问 JSON 等空内容：兜底话术，避免发出空答复
                    $content = '抱歉，本次未能生成有效答复，请重试或调整提问方式。';
                }
                if ($context['definitions'] !== [] && $this->looksLikeQuestionToUser($content)) {
                    $confirm = $this->forceAskUserCard($conversationId, $content, $context, [
                        'model_id' => $modelId,
                        'server_ids' => $serverIds,
                        'skill_keys' => $skillKeys,
                    ]);
                    return [
                        'conversation_id' => $conversationId,
                        'confirm' => [
                            'message_id' => $confirm['message_id'],
                            'tool_name' => McpToolService::ASK_USER_TOOL,
                            'arguments' => $confirm['arguments'],
                            'interaction' => 'ask_user',
                            'display' => $confirm['display'],
                        ],
                        'content' => '',
                        'reasoning' => $reasoning,
                        'model' => $lastModel,
                        'usage' => $lastUsage,
                        'tool_calls' => $toolTrace,
                        'skills' => array_keys($context['skillFiles']),
                    ];
                }
                $grounded = $this->ensureGroundedAnswer($context, $toolTrace, [
                    'content' => $content,
                    'reasoning' => $reasoning,
                    'model' => $lastModel,
                    'usage' => $lastUsage,
                ]);
                $content = $grounded['content'];
                $reasoning = $grounded['reasoning'];
                $lastModel = $grounded['model'];
                $lastUsage = $grounded['usage'];
                $followups = $this->extractFollowups($content);
                if ($followups === []) {
                    $followups = $earlyFollowups;
                }
                $this->agents->addMessage($conversationId, 'assistant', $content, '', [
                    'skills' => $context['skillsUsed'],
                    'model' => $lastModel,
                    'reasoning' => $reasoning,
                    'followups' => $followups,
                    'tool_calls' => $this->toolTraceNames($toolTrace),
                ]);
                return [
                    'conversation_id' => $conversationId,
                    'content' => $content,
                    'reasoning' => $reasoning,
                    'model' => $lastModel,
                    'usage' => $lastUsage,
                    'tool_calls' => $toolTrace,
                    'skills' => array_keys($context['skillFiles']),
                    'followups' => $followups,
                ];
            }

            if ($step >= $maxSteps) {
                break;
            }

            $toolName = $toolCall['name'];
            $arguments = $toolCall['arguments'];
            // 相同调用（工具名+参数）只允许尝试一次，防止模型对无效工具原样重试烧完配额
            $signature = $toolName . '|' . json_encode($arguments, JSON_UNESCAPED_UNICODE);
            if (isset($attempted[$signature])) {
                $context['workingMessage'] .= "\n\n系统提示：工具 {$toolName} 的相同调用已经执行过，结果已在上文。请勿重复调用，直接依据已有结果给出最终答复。";
                break;
            }
            $attempted[$signature] = true;
            $this->agents->addMessage(
                $conversationId,
                'assistant',
                $this->canonicalToolCall($toolName, $arguments),
                $toolName,
                ['arguments' => $arguments]
            );
            // 需用户交互的工具：暂停执行，等待用户在页面上确认/作答（confirm 消息承载快照与恢复上下文）；
            // 无人值守模式没有可交互的用户：需确认写工具已在工具清单过滤（只读）或授权直执（全自动），全部豁免确认
            $interaction = $unattended ? null : $this->resolveInteraction($toolName, $context['definitions']);
            if ($interaction !== null) {
                $display = $this->buildCardDisplay($toolCall, $context['definitions']);
                $confirmMessageId = $this->agents->addMessage(
                    $conversationId,
                    'confirm',
                    $this->canonicalToolCall($toolName, $arguments),
                    $toolName,
                    [
                        'arguments' => $arguments,
                        'status' => 'pending',
                        'interaction' => $interaction,
                        'display' => $display,
                        'model_id' => $modelId,
                        'server_ids' => $serverIds,
                        'skill_keys' => $skillKeys,
                        'skill_loaded' => array_keys($context['skillFiles']),
                        'allowed_tools' => $context['allowedTools'],
                    ]
                );
                return [
                    'conversation_id' => $conversationId,
                    'confirm' => ['message_id' => $confirmMessageId, 'tool_name' => $toolName, 'arguments' => $arguments, 'interaction' => $interaction, 'display' => $display],
                    'content' => '',
                    'reasoning' => '',
                    'model' => $lastModel,
                    'usage' => $lastUsage,
                    'tool_calls' => $toolTrace,
                    'skills' => array_keys($context['skillFiles']),
                ];
            }
            if (in_array($toolName, $context['allowedTools'], true)) {
                try {
                    // 无人值守邮件收件校验：仅允许任务通知邮箱与系统白名单
                    $this->guardUnattendedEmail($toolName, $arguments, $context);
                    // 本地虚拟工具（Skill 按需加载/自动化任务管理）：拦截在 MCP 分发之前
                    $toolResult = match (true) {
                        $toolName === self::SKILL_LOAD_TOOL => $this->loadSkillForContext($context, (string)($arguments['key'] ?? ''), (string)($arguments['file'] ?? '')),
                        $toolName === McpToolService::TASK_CREATE_TOOL => $this->createTaskForContext((int)$context['admin_id'], $arguments),
                        $toolName === McpToolService::TASK_LIST_TOOL => $this->listTasksForContext((int)$context['admin_id'], $arguments),
                        default => $this->mcp->call($toolName, $arguments, $context['allowedTools']),
                    };
                } catch (\Throwable $e) {
                    $toolResult = [
                        'error' => true,
                        'message' => $e->getMessage(),
                    ];
                }
            } else {
                // 幻觉工具名：附上可用清单引导模型纠正，而不是让它反复猜测
                $toolResult = [
                    'error'       => true,
                    'message'     => "工具 {$toolName} 不在本对话可用工具清单中，禁止调用。",
                    'valid_tools' => $context['allowedTools'],
                ];
            }
            $this->agents->addMessage(
                $conversationId,
                'tool',
                json_encode($toolResult, JSON_UNESCAPED_UNICODE),
                $toolName,
                ['arguments' => $arguments, 'result' => $toolResult]
            );
            $toolTrace[] = ['name' => $toolName, 'arguments' => $arguments, 'result' => $toolResult];
            $context['workingMessage'] .= $this->buildToolResultContext($toolName, $toolResult);
        }

        $finalPrompts = $context['prompts'];
        $finalPrompts[] = '工具调用阶段已结束。上文已经包含工具返回结果，请直接严格依据结果给出最终答复，不要再输出工具调用 JSON，也不要声称未收到工具结果。';
        $result = $context['driver']->chat($context['workingMessage'], ['prompts' => $finalPrompts, 'stream' => 0]);
        $content = $this->stripToolCallJson(trim((string)($result['content'] ?? '')));
        $reasoning = trim((string)($result['reasoning'] ?? ''));
        $thinkReasoning = $this->extractThinkBlocks($content);
        if ($reasoning === '') {
            $reasoning = $thinkReasoning;
        }
        if ($content === '') {
            // 强制答复轮仍只输出工具 JSON：已有工具结果时优雅兜底
            if ($toolTrace !== []) {
                $content = '抱歉，本次未能基于工具结果生成有效答复，请重试或调整提问方式。';
            } else {
                throw new \RuntimeException('AI 模型未返回有效内容，请检查所选模型配置');
            }
        }
        $grounded = $this->ensureGroundedAnswer($context, $toolTrace, [
            'content' => $content,
            'reasoning' => $reasoning,
            'model' => (string)($result['model'] ?? $lastModel),
            'usage' => (array)($result['usage'] ?? $lastUsage),
        ]);
        $content = $grounded['content'];
        $reasoning = $grounded['reasoning'];
        $model = $grounded['model'];
        $followups = $this->extractFollowups($content);
        $this->agents->addMessage($conversationId, 'assistant', $content, '', [
            'skills' => array_keys($context['skillFiles']),
            'model' => $model,
            'reasoning' => $reasoning,
            'followups' => $followups,
            'tool_calls' => $this->toolTraceNames($toolTrace),
        ]);

        return [
            'conversation_id' => $conversationId,
            'content' => $content,
            'reasoning' => $reasoning,
            'model' => $model,
            'usage' => $grounded['usage'],
            'tool_calls' => $toolTrace,
            'skills' => $context['skillsUsed'],
            'followups' => $followups,
        ];
    }

    /**
     * 流式对话（SSE）：思维链与正文逐块推送，工具调用轮次间同样流式
     *
     * 事件约定（由调用方以回调形式下发到客户端）：
     * - {event: start, conversation_id}        会话就绪
     * - {event: skills, skills}                本轮挂载的 Skill 清单（工具循环开始即下发）
     * - {event: reasoning, text}               思维链增量
     * - {event: delta, text}                   正文增量
     * - {event: tool, name, arguments, result} 一次工具调用及其结果
     * - {event: done, tool_calls, skills}      对话结束
     *
     * @param int $adminId 管理员 ID
     * @param int $conversationId 会话 ID（0 表示新建会话）
     * @param string $message 用户消息
     * @param int $modelId 模型 ID（0 = 内置一号通默认）
     * @param array $serverIds 选中的连接器（MCP Server）ID 列表
     * @param array $skillKeys 选中的 Skill 标识列表
     * @param callable $send 事件推送回调 function(array $payload): void
     */
    public function chatStream(
        int $adminId,
        int $conversationId,
        string $message,
        int $modelId = 0,
        array $serverIds = [],
        array $skillKeys = [],
        ?callable $send = null
    ): void {
        $message = trim($message);
        if ($message === '') {
            throw new \RuntimeException('请输入对话内容');
        }
        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            throw new \RuntimeException('对话内容不能超过 ' . self::MAX_MESSAGE_LENGTH . ' 字');
        }
        $send = $send ?: static function (array $payload): void {
        };

        if ($conversationId > 0) {
            $this->agents->assertConversation($conversationId, $adminId);
        } else {
            $conversationId = $this->agents->createConversation(0, $adminId, $message);
        }
        $this->agents->addMessage($conversationId, 'user', $message);
        // 新用户消息到达：旧的待确认操作自动过期，避免旧确认卡与新上下文错位
        $this->agents->expirePendingConfirms($conversationId);

        $send(['event' => 'start', 'conversation_id' => $conversationId]);
        try {
            $context = $this->prepareContext($conversationId, $serverIds, $skillKeys, $modelId, [], [], $adminId);
            $this->streamLoop($conversationId, $context, $send, [
                'model_id' => $modelId,
                'server_ids' => $serverIds,
                'skill_keys' => $skillKeys,
            ]);
        } catch (\Throwable $e) {
            // 模型解析/调用失败（未配置、停用、一号通凭证缺失、上游密钥无效等）：
            // 以普通对话消息输出配置指引并落库，前端按正常回复渲染，不再依赖错误弹窗
            $this->sendModelGuide($conversationId, $send, $e);
        }
    }

    /**
     * 模型不可用兜底：以普通 assistant 消息输出配置指引（先落库再流式推送），前端按正常回复渲染。
     *
     * 覆盖模型未配置/停用、一号通凭证缺失、上游接口报错（如密钥无效）等场景；
     * 消息落库后在历史会话中同样可见，替代原先一闪而过的错误弹窗。
     * @param int $conversationId 会话 ID
     * @param callable $send SSE 事件推送回调
     * @param \Throwable $e 失败异常
     */
    private function sendModelGuide(int $conversationId, callable $send, \Throwable $e): void
    {
        // 与流式错误白名单同一口径：仅 RuntimeException 的消息可直出，其余异常统一脱敏文案
        $error = $e instanceof \RuntimeException ? $e->getMessage() : '服务异常，请稍后重试';
        $content = $this->buildModelGuide($error);
        $this->agents->addMessage($conversationId, 'assistant', $content);
        $send(['event' => 'delta', 'text' => $content]);
        $send(['event' => 'done', 'tool_calls' => [], 'skills' => []]);
    }

    /**
     * 按错误内容归类生成配置指引文案（含后台配置页地址），以 Markdown 随对话下发
     * @param string $error 脱敏后的错误消息
     * @return string
     */
    private function buildModelGuide(string $error): string
    {
        // 一号通凭证缺失或一号通接口报错：引导完成一号通配置，或改用第三方模型
        if (str_contains($error, '一号通')) {
            return "AI 模型暂不可用：{$error}\n\n"
                . "可选择以下任一方式配置后再试：\n"
                . "- 使用一号通 AI：前往[系统设置 → 一号通账户](/admin/setting/yihaotong/index)完成一号通登录；\n"
                . '- 使用第三方模型：前往[第三方模型配置](/admin/plugin/mcp/config)完成配置。';
        }
        // 第三方模型上游报错（如 Invalid API Key）：引导检查登记模型的密钥与地址配置
        if (str_contains($error, 'AI接口错误')) {
            return "AI 模型调用失败：{$error}\n\n"
                . '请前往[系统设置 → 模型管理](/admin/setting/ai_model/index)检查所选模型的接口地址与密钥配置，或更换其他可用模型后重试。';
        }
        // 模型不存在/停用等（错误文案本身已指向模型管理）：补充具体配置页地址
        if (str_contains($error, '模型管理')) {
            return "{$error}\n\n模型管理入口：[系统设置 → 模型管理](/admin/setting/ai_model/index)。";
        }
        return $error;
    }

    /**
     * 流式工具调用循环：从模型流式读取到解析工具调用、执行（含确认拦截）直至最终答复。
     *
     * @param int $conversationId 会话 ID
     * @param array $context prepareContext 构建的对话上下文
     * @param callable $send SSE 事件推送回调
     * @param array $confirmContext 确认快照上下文（model_id/server_ids/skill_keys），拦截需确认工具时落库
     * @param string $openingTrace 起始过程提示文案
     * @param array $initialToolTrace 确认续跑前已执行的工具结果
     */
    private function streamLoop(
        int $conversationId,
        array $context,
        callable $send,
        array $confirmContext,
        string $openingTrace = '正在分析你的问题',
        array $initialToolTrace = []
    ): void {
        // confirm_stream 在进入循环前已经按用户确认执行过一个工具。
        // 该结果必须带入，后续模型偶发空响应时仍能给出确定性结果，不能误报模型配置失败。
        $toolTrace = $initialToolTrace;
        $attempted = [];
        // 叙述中断续跑计数：最多注入 2 次系统提示要求模型继续，防止死循环
        $nudges = 0;
        // 上游 200 空响应重试计数：透明重试 1 次，仍为空才报错
        $emptyRetries = 0;
        // 残缺工具调用 JSON 修复计数：输出被截断导致 JSON 无法解析时注入系统提示重试，限 1 次
        $repairRetries = 0;
        $skillsUsed = $context['skillsUsed'];
        // 部分模型（如普通 Chat 模型）不会返回 reasoning_content；补充可审计的处理过程，
        // 既保证页面有实时反馈，也不伪造或暴露模型内部思维链。
        $processTrace = [];
        // 手动停止兜底：前端终止会断开 SSE 连接，正常完成路径的落库不再执行。
        // 必须先关闭「客户端断开即终止脚本」的默认行为（默认 Off 时 FPM 在 echo 瞬间直接杀脚本，
        // 下方 connection_aborted 检测根本不会执行），改为由输出出口主动检测：
        // 断开时先落库部分回复再 exit；若代理层迟迟未上报断开，脚本正常跑完并完整落库，同样不丢记录。
        @ignore_user_abort(true);
        // lastAcc 实时累积已推送给前端的可见正文/思维链，避免「手动停止后回复记录丢失」。
        $lastAcc = ['content' => '', 'reasoning' => ''];
        $responsePersisted = false;
        $savePartialReply = function () use ($conversationId, &$skillsUsed, &$toolTrace, &$processTrace, &$lastAcc, &$responsePersisted): void {
            $content = trim($lastAcc['content']);
            if ($responsePersisted || $content === '') {
                return;
            }
            $this->agents->addMessage($conversationId, 'assistant', $content, '', [
                'skills' => $skillsUsed,
                'reasoning' => $this->mergeReasoning($processTrace, trim($lastAcc['reasoning'])),
                'followups' => [],
                'tool_calls' => $this->toolTraceNames($toolTrace),
            ]);
        };
        $send = function (array $payload) use ($send, $savePartialReply): void {
            $send($payload);
            if (connection_aborted()) {
                $savePartialReply();
                exit;
            }
        };
        // 已加载的 Skill（key+中文名）一开始即下发（显式选择的或确认续跑预载的）；按需加载的随加载动态更新
        $send(['event' => 'skills', 'skills' => $skillsUsed]);
        $trace = function (string $text) use (&$processTrace, $send): void {
            $processTrace[] = $text;
            $send(['event' => 'reasoning', 'text' => $text . "\n"]);
        };
        $trace($openingTrace);

        // 流式读取一轮：思考自然语言安全透传，正文完整累积后再区分最终答复与工具调用。
        $streamOnce = function (array $prompts) use (&$context, $send, &$lastAcc): array {
            $acc = ['content' => '', 'rawContent' => '', 'reasoning' => '', 'suppressed' => false];
            $filter = $this->newStreamFilter();
            $reasoningFilter = $this->newReasoningFilter();
            $privateCallFilter = $this->newPrivateCallFilter();
            $onReasoning = function (string $text) use (&$acc, &$reasoningFilter, &$privateCallFilter, &$lastAcc, $send): void {
                if ($text === '') {
                    return;
                }
                $acc['reasoning'] .= $text;
                // 同步到断开兜底容器（手动停止时按此落库部分回复）
                $lastAcc['reasoning'] .= $text;
                $this->filterPrivateCallChunk($privateCallFilter, $text, function (string $safeText) use (&$reasoningFilter, $send): void {
                    $this->filterReasoningChunk($reasoningFilter, $safeText, static function (string $visibleText) use ($send): void {
                        $send(['event' => 'reasoning', 'text' => $visibleText]);
                    });
                });
            };
            $onContent = function (string $text) use (&$acc, &$lastAcc, $send): void {
                if ($text === '') {
                    return;
                }
                $acc['content'] .= $text;
                // 同步到断开兜底容器（手动停止时按此落库部分回复）
                $lastAcc['content'] .= $text;
                $send(['event' => 'delta', 'text' => $text]);
            };
            AiProtocol::chatStream(
                $context['driver'],
                $context['workingMessage'],
                function (array $delta) use ($onReasoning, $onContent, &$filter, &$acc): void {
                    $text = (string)($delta['text'] ?? '');
                    if (($delta['type'] ?? 'content') === 'reasoning') {
                        $onReasoning($text);
                        return;
                    }
                    // rawContent 始终保留，避免纯 JSON 被展示过滤器抑制后无法执行。
                    $acc['rawContent'] .= $text;
                    $this->filterStreamChunk($filter, $text, $onReasoning, $onContent);
                },
                ['prompts' => $prompts]
            );
            // 流结束：冲刷跨块截断的残留缓冲
            $this->flushStreamFilter($filter, $onReasoning, $onContent);
            $this->flushPrivateCallFilter($privateCallFilter, function (string $safeText) use (&$reasoningFilter, $send): void {
                $this->filterReasoningChunk($reasoningFilter, $safeText, static function (string $visibleText) use ($send): void {
                    $send(['event' => 'reasoning', 'text' => $visibleText]);
                });
            });
            $this->flushReasoningFilter($reasoningFilter, static function (string $safeText) use ($send): void {
                $send(['event' => 'reasoning', 'text' => $safeText]);
            });
            $acc['suppressed'] = (bool)($filter['blocked'] ?? false);
            return $acc;
        };

        for ($step = 0; $step <= self::MAX_STEPS; $step++) {
            $acc = $streamOnce($context['prompts']);
            $content = trim((string)$acc['rawContent']);
            $inlineReasoning = $this->extractThinkBlocks($content);
            $reasoning = trim((string)$acc['reasoning']);
            if ($reasoning === '') {
                $reasoning = $inlineReasoning;
            }
            $toolCall = $this->resolveToolCall($content, $reasoning);
            if ($content === '' && !$toolCall) {
                // 部分 OpenAI 兼容网关会偶发返回 HTTP 200 的空 SSE 流，但同一请求的
                // 非流式响应正常。先透明重试一次，再降级为非流式请求取回实际答复。
                if ($emptyRetries < 1) {
                    $emptyRetries++;
                    $trace('模型本次未返回内容，正在重试');
                    continue;
                }
                $fallback = $context['driver']->chat($context['workingMessage'], [
                    'prompts' => $context['prompts'],
                    'stream'  => 0,
                ]);
                $content = trim((string)($fallback['content'] ?? ''));
                $fallbackReasoning = trim((string)($fallback['reasoning'] ?? ''));
                if ($fallbackReasoning !== '') {
                    $reasoning = $fallbackReasoning;
                }
                $toolCall = $this->resolveToolCall($content, $reasoning);
                if ($content === '' && !$toolCall) {
                    Log::error('[AgentChat] 上游空响应', [
                        'message_len' => strlen($context['workingMessage']),
                        'prompts_len' => array_sum(array_map('strlen', $context['prompts'])),
                        'has_reasoning' => $reasoning !== '',
                    ]);
                    throw new \RuntimeException($reasoning !== ''
                        ? 'AI 模型仅返回了思考过程、未产出正文，常见原因是推理模型耗尽 max_tokens，请在「AI Agent → 模型管理」调大输出上限或更换模型'
                        : 'AI 模型未返回有效内容，请稍后重试或检查所选模型配置');
                }
                $trace('流式响应为空，已切换为非流式响应继续处理');
            }

            if (!$toolCall) {
                // 剥离前先识别原始输出是否为工具调用 JSON（含起点标记但解析失败/未闭合，多为被 max_tokens 截断）
                $brokenToolCall = str_contains($content, '{"tool_call"') || str_contains($content, '<|FunctionCallBegin|>');
                $content = $this->stripToolCallJson($content);
                if ($content === '') {
                    // 模型只输出了无法解析的工具调用 JSON：先注入系统提示要求重新输出（限一次自愈），
                    // 仍失败再优雅兜底，避免整个请求报错
                    if ($brokenToolCall && $repairRetries < 1) {
                        $repairRetries++;
                        $trace('模型输出的工具调用 JSON 不完整（可能被输出长度截断），正在要求模型重新输出');
                        $context['workingMessage'] .= "\n\n系统提示：你上一条回复输出的工具调用 JSON 不完整或无法解析（常见原因是输出被长度截断）。请重新输出：需要继续操作时，只输出一个完整且精简的工具调用 JSON（display 从简，不要输出其他叙述）；已有信息足够时，直接输出包含完整结果的最终答复。禁止重复输出残缺内容。";
                        continue;
                    }
                    if ($toolTrace !== []) {
                        Log::error('[AgentChat] 模型仅输出残缺工具调用 JSON，已兜底', ['raw_head' => mb_substr((string)$acc['rawContent'], 0, 500)]);
                        $content = '抱歉，本次未能基于工具结果生成有效答复，请重试或调整提问方式。';
                    } else {
                        Log::error('[AgentChat] 模型仅输出无法解析的内容', ['raw_head' => mb_substr((string)$acc['rawContent'], 0, 500)]);
                        throw new \RuntimeException('AI 模型未返回有效内容，请检查所选模型配置');
                    }
                }
                // 正文提问兜底：模型未走 crmeb_ask_user 而以正文直接向用户提问时，
                // 由服务端把该回复强制转为问询卡片，确保用户端弹出输入框（不依赖模型自觉）。
                // 提前剥离追问 JSON 防止混入卡片问题，结果暂存供最终答复复用，避免标签丢失
                $earlyFollowups = $this->extractFollowups($content);
                if ($content === '') {
                    // 整轮输出被剥离后只剩追问 JSON 等空内容：兜底话术，避免发出空答复
                    $content = '抱歉，本次未能生成有效答复，请重试或调整提问方式。';
                }
                if ($context['definitions'] !== [] && $this->looksLikeQuestionToUser($content)) {
                    $confirm = $this->forceAskUserCard($conversationId, $content, $context, $confirmContext);
                    $responsePersisted = true;
                    $trace('检测到模型以正文直接提问，已转为卡片提问');
                    $send([
                        'event' => 'confirm',
                        'message_id' => $confirm['message_id'],
                        'tool_name' => McpToolService::ASK_USER_TOOL,
                        'arguments' => $confirm['arguments'],
                        'interaction' => 'ask_user',
                        'display' => $confirm['display'],
                    ]);
                    $send(['event' => 'done', 'tool_calls' => $toolTrace, 'skills' => $skillsUsed]);
                    return;
                }
                // 叙述中断检测：模型输出了「接下来我将…」式计划后未给出工具调用就停笔，
                // 直接结束会让用户看到"说了要继续却突然没了"。注入系统提示要求继续，有限重试。
                if ($context['definitions'] !== [] && $nudges < 2 && $this->looksLikeUnfinishedNarration($content)) {
                    $nudges++;
                    $context['workingMessage'] .= "\n\n系统提示：你上一条回复只输出了计划性叙述（如「接下来我将……」），没有输出工具调用 JSON，流程已中断。请立即继续："
                        . "还需要查询或操作数据时，只输出一个工具调用 JSON（格式见工具清单说明）；信息已足够时，直接输出包含完整结果的最终答复。"
                        . "禁止再输出任何计划性叙述而不采取实际动作。";
                    $trace('检测到回复在计划叙述后中断，正在让模型继续');
                    continue;
                }
                $grounded = $this->ensureGroundedAnswer($context, $toolTrace, [
                    'content' => $content,
                    'reasoning' => $reasoning,
                    'model' => '',
                    'usage' => [],
                ]);
                $content = $grounded['content'];
                $reasoning = $this->mergeReasoning($processTrace, $grounded['reasoning']);
                $followups = $this->extractFollowups($content);
                if ($followups === []) {
                    $followups = $earlyFollowups ?? [];
                }
                // 先落库最终答复再推送校正内容：即使推送时客户端断开（手动停止），记录也已保存
                $this->agents->addMessage($conversationId, 'assistant', $content, '', [
                    'skills' => $skillsUsed,
                    'reasoning' => $reasoning,
                    'followups' => $followups,
                    'tool_calls' => $this->toolTraceNames($toolTrace),
                ]);
                $responsePersisted = true;
                $this->syncStreamFinalAnswer($send, $acc, $content);
                if ($followups !== []) {
                    $send(['event' => 'followups', 'questions' => $followups]);
                }
                $send(['event' => 'done', 'tool_calls' => $toolTrace, 'skills' => $skillsUsed]);
                return;
            }

            if ($step >= self::MAX_STEPS) {
                break;
            }

            $toolName = $toolCall['name'];
            $arguments = $toolCall['arguments'];
            $trace("已识别需要实时数据，准备调用工具：{$toolName}");
            // 相同调用（工具名+参数）只允许尝试一次，防止模型对无效工具原样重试烧完配额
            $signature = $toolName . '|' . json_encode($arguments, JSON_UNESCAPED_UNICODE);
            if (isset($attempted[$signature])) {
                $context['workingMessage'] .= "\n\n系统提示：工具 {$toolName} 的相同调用已经执行过，结果已在上文。请勿重复调用，直接依据已有结果给出最终答复。";
                break;
            }
            $attempted[$signature] = true;
            $this->agents->addMessage(
                $conversationId,
                'assistant',
                $this->canonicalToolCall($toolName, $arguments),
                $toolName,
                ['arguments' => $arguments]
            );
            // 需用户交互的工具：暂停执行，推送交互事件等待用户确认/作答（confirm 消息承载快照与恢复上下文）
            $interaction = $this->resolveInteraction($toolName, $context['definitions']);
            if ($interaction !== null) {
                $display = $this->buildCardDisplay($toolCall, $context['definitions']);
                $confirmMessageId = $this->agents->addMessage(
                    $conversationId,
                    'confirm',
                    $this->canonicalToolCall($toolName, $arguments),
                    $toolName,
                    [
                        'arguments' => $arguments,
                        'status' => 'pending',
                        'interaction' => $interaction,
                        'display' => $display,
                        'model_id' => (int)($confirmContext['model_id'] ?? 0),
                        'server_ids' => (array)($confirmContext['server_ids'] ?? []),
                        'skill_keys' => (array)($confirmContext['skill_keys'] ?? []),
                        'skill_loaded' => array_keys($context['skillFiles']),
                        'allowed_tools' => $context['allowedTools'],
                    ]
                );
                $responsePersisted = true;
                $trace($interaction === 'ask_user' ? "需要用户补充信息，已发起提问：{$toolName}" : "工具 {$toolName} 涉及数据修改，已暂停等待用户确认");
                $send([
                    'event' => 'confirm',
                    'message_id' => $confirmMessageId,
                    'tool_name' => $toolName,
                    'arguments' => $arguments,
                    'interaction' => $interaction,
                    'display' => $display,
                ]);
                $send(['event' => 'done', 'tool_calls' => $toolTrace, 'skills' => $skillsUsed]);
                return;
            }
            if (in_array($toolName, $context['allowedTools'], true)) {
                try {
                    // 无人值守邮件收件校验：仅允许任务通知邮箱与系统白名单
                    $this->guardUnattendedEmail($toolName, $arguments, $context);
                    // 本地虚拟工具（Skill 按需加载/自动化任务管理）：拦截在 MCP 分发之前
                    if ($toolName === self::SKILL_LOAD_TOOL) {
                        $toolResult = $this->loadSkillForContext($context, (string)($arguments['key'] ?? ''), (string)($arguments['file'] ?? ''));
                        if (empty($toolResult['error'])) {
                            // loadSkillForContext 已更新清单；附件加载不改变 Skill 清单。
                            $skillsUsed = $context['skillsUsed'];
                            $send(['event' => 'skills', 'skills' => $skillsUsed]);
                            $skillName = (string)($toolResult['skill']['name'] ?? ($arguments['key'] ?? ''));
                            if (!empty($toolResult['file'])) {
                                $trace("已加载 Skill [{$skillName}] 的附属资料：{$toolResult['file']}");
                            } else {
                                $trace("检测到问题匹配 Skill 领域，规则 [{$skillName}] 已加载");
                            }
                        }
                    } elseif ($toolName === McpToolService::TASK_CREATE_TOOL) {
                        $toolResult = $this->createTaskForContext((int)($context['admin_id'] ?? 0), $arguments);
                        $trace(empty($toolResult['error']) ? "自动化任务 [{$toolResult['name']}] 已创建并启用" : '自动化任务创建失败');
                    } elseif ($toolName === McpToolService::TASK_LIST_TOOL) {
                        $toolResult = $this->listTasksForContext((int)($context['admin_id'] ?? 0), $arguments);
                        $trace('已查询自动化任务清单');
                    } else {
                        $toolResult = $this->mcp->call($toolName, $arguments, $context['allowedTools']);
                    }
                } catch (\Throwable $e) {
                    $toolResult = ['error' => true, 'message' => $e->getMessage()];
                }
            } else {
                // 幻觉工具名：附上可用清单引导模型纠正，而不是让它反复猜测
                $toolResult = [
                    'error'       => true,
                    'message'     => "工具 {$toolName} 不在本对话可用工具清单中，禁止调用。",
                    'valid_tools' => $context['allowedTools'],
                ];
            }
            $this->agents->addMessage(
                $conversationId,
                'tool',
                json_encode($toolResult, JSON_UNESCAPED_UNICODE),
                $toolName,
                ['arguments' => $arguments, 'result' => $toolResult]
            );
            $toolTrace[] = ['name' => $toolName, 'arguments' => $arguments, 'result' => $toolResult];
            $trace(
                is_array($toolResult) && empty($toolResult['error'])
                    ? "工具 {$toolName} 已返回结果，正在整理回答"
                    : "工具 {$toolName} 调用失败，正在根据已有信息继续处理"
            );
            // 已输出的文字只是本轮工具调用前言，前端收到 tool 后会清掉并等待工具结果后的正式答复。
            // 工具调用前的正文片段，前端会随 tool 事件转入思考区展示：后端同步并入断开兜底的 reasoning，
            // 避免断开落库时这些前言碎片混入正文
            $lastAcc['reasoning'] .= ($lastAcc['reasoning'] !== '' && $lastAcc['content'] !== '' ? "\n" : '') . $lastAcc['content'];
            $lastAcc['content'] = '';
            $send(['event' => 'tool', 'name' => $toolName, 'arguments' => $arguments, 'result' => $toolResult]);
            $context['workingMessage'] .= $this->buildToolResultContext($toolName, $toolResult);
        }

        // 工具轮次达上限：强制输出最终答复
        $finalPrompts = $context['prompts'];
        $finalPrompts[] = '工具调用阶段已结束。上文已经包含工具返回结果，请直接严格依据结果给出最终答复，不要再输出工具调用 JSON，也不要声称未收到工具结果。';
        $trace('正在基于工具结果生成最终答复');
        $acc = $streamOnce($finalPrompts);
        $content = trim((string)$acc['rawContent']);
        $inlineReasoning = $this->extractThinkBlocks($content);
        $reasoning = trim((string)$acc['reasoning']);
        if ($reasoning === '') {
            $reasoning = $inlineReasoning;
        }
        $content = $this->stripToolCallJson($content);
        if ($content === '') {
            // 强制答复轮仍只输出工具 JSON：已有工具结果时优雅兜底
            if ($toolTrace !== []) {
                $content = '抱歉，本次未能基于工具结果生成有效答复，请重试或调整提问方式。';
            } else {
                throw new \RuntimeException('AI 模型未返回有效内容，请检查所选模型配置');
            }
        }
        $grounded = $this->ensureGroundedAnswer($context, $toolTrace, [
            'content' => $content,
            'reasoning' => $reasoning,
            'model' => '',
            'usage' => [],
        ]);
        $content = $grounded['content'];
        $reasoning = $this->mergeReasoning($processTrace, $grounded['reasoning']);
        $followups = $this->extractFollowups($content);
        $this->agents->addMessage($conversationId, 'assistant', $content, '', [
            'skills' => $skillsUsed,
            'reasoning' => $reasoning,
            'followups' => $followups,
            'tool_calls' => $this->toolTraceNames($toolTrace),
        ]);
        $responsePersisted = true;
        $this->syncStreamFinalAnswer($send, $acc, $content);
        if ($followups !== []) {
            $send(['event' => 'followups', 'questions' => $followups]);
        }
        $send(['event' => 'done', 'tool_calls' => $toolTrace, 'skills' => $skillsUsed]);
    }

    /**
     * 流式处理用户对交互卡片的决定（SSE，事件类型与 chatStream 一致）。
     *
     * 按 interaction 类型分流：
     * - execute：approve=按 confirm 消息中的参数快照执行工具，结果作为权威事实继续工具调用循环直至最终答复；
     *            reject=不执行工具、不调用模型，直接落库固定话术（确定性强，节省一次模型调用）
     * - ask_user：approve=把用户提交的答案（answers/custom）作为工具结果回给模型继续循环；
     *             reject=以「用户未回答」工具结果回给模型继续循环，模型可自行调整
     * 循环中若再次命中需交互工具，会再次推送 confirm 事件（多步确认链式进行）
     *
     * @param int $adminId 管理员 ID
     * @param int $conversationId 会话 ID
     * @param int $messageId confirm 消息 ID
     * @param string $action approve=确认/作答 / reject=取消/跳过
     * @param array $answers ask_user 场景用户选中的选项 value 列表
     * @param string $custom ask_user 场景用户自定义输入文本
     * @param callable|null $send SSE 事件推送回调
     */
    public function confirmStream(
        int $adminId,
        int $conversationId,
        int $messageId,
        string $action,
        array $answers = [],
        string $custom = '',
        ?callable $send = null
    ): void {
        $send = $send ?: static function (array $payload): void {
        };
        $message = $this->agents->getPendingConfirm($conversationId, $messageId, $adminId);
        $payload = $message['tool_payload'];
        $toolName = (string)$message['tool_name'];
        $arguments = is_array($payload['arguments'] ?? null) ? $payload['arguments'] : [];
        $interaction = (string)($payload['interaction'] ?? 'execute');

        // 执行类卡片被拒绝：固定话术，不调用模型
        if ($interaction !== 'ask_user' && $action !== 'approve') {
            $this->agents->updateConfirmStatus($messageId, 'rejected');
            $content = '好的，已取消执行「' . $toolName . '」操作，系统未做任何修改。如需继续，请告诉我调整后的要求。';
            $this->agents->addMessage($conversationId, 'assistant', $content);
            $send(['event' => 'delta', 'text' => $content]);
            $send(['event' => 'done', 'tool_calls' => [], 'skills' => []]);
            return;
        }

        $this->agents->updateConfirmStatus($messageId, $action === 'approve' ? 'approved' : 'rejected');
        // 以确认时快照重建上下文（连接器/Skill/模型），执行参数以快照为准，不重新让模型生成；
        // 确认前已加载的 Skill 一并预载，模型无需重复调用加载工具
        try {
            $context = $this->prepareContext(
                $conversationId,
                array_map('intval', (array)($payload['server_ids'] ?? [])),
                array_map('strval', (array)($payload['skill_keys'] ?? [])),
                (int)($payload['model_id'] ?? 0),
                array_map('strval', (array)($payload['skill_loaded'] ?? [])),
                [],
                $adminId
            );
        } catch (\Throwable $e) {
            $this->sendModelGuide($conversationId, $send, $e);
            return;
        }

        if ($interaction === 'ask_user') {
            $toolResult = $action === 'approve'
                ? $this->buildAskUserAnswer($arguments, $answers, $custom)
                : ['status' => 'skipped', 'message' => '用户未回答该问题，请基于已有信息继续，不要重复追问同一问题'];
            $this->agents->addMessage(
                $conversationId,
                'tool',
                json_encode($toolResult, JSON_UNESCAPED_UNICODE),
                $toolName,
                ['arguments' => $arguments, 'result' => $toolResult]
            );
            $send(['event' => 'tool', 'name' => $toolName, 'arguments' => $arguments, 'result' => $toolResult]);
            $context['workingMessage'] .= $this->buildToolResultContext($toolName, $toolResult);
            try {
                $this->streamLoop($conversationId, $context, $send, [
                    'model_id' => (int)($payload['model_id'] ?? 0),
                    'server_ids' => (array)($payload['server_ids'] ?? []),
                    'skill_keys' => (array)($payload['skill_keys'] ?? []),
                ], $action === 'approve' ? '用户已作答，正在继续处理' : '用户已跳过该问题，正在继续处理', [[
                    'name' => $toolName,
                    'arguments' => $arguments,
                    'result' => $toolResult,
                ]]);
            } catch (\Throwable $e) {
                $this->sendModelGuide($conversationId, $send, $e);
            }
            return;
        }

        // 执行类卡片确认：按快照执行工具（本地虚拟工具在 MCP 分发之前拦截）
        $allowed = is_array($payload['allowed_tools'] ?? null) && $payload['allowed_tools'] !== []
            ? $payload['allowed_tools']
            : $context['allowedTools'];
        try {
            $toolResult = match (true) {
                $toolName === McpToolService::TASK_CREATE_TOOL => $this->createTaskForContext($adminId, $arguments),
                $toolName === McpToolService::TASK_LIST_TOOL => $this->listTasksForContext($adminId, $arguments),
                default => $this->mcp->call($toolName, $arguments, $allowed),
            };
        } catch (\Throwable $e) {
            $toolResult = ['error' => true, 'message' => $e->getMessage()];
        }
        $this->agents->addMessage(
            $conversationId,
            'tool',
            json_encode($toolResult, JSON_UNESCAPED_UNICODE),
            $toolName,
            ['arguments' => $arguments, 'result' => $toolResult]
        );
        $send(['event' => 'tool', 'name' => $toolName, 'arguments' => $arguments, 'result' => $toolResult]);
        $context['workingMessage'] .= $this->buildToolResultContext($toolName, $toolResult);
        try {
            $this->streamLoop($conversationId, $context, $send, [
                'model_id' => (int)($payload['model_id'] ?? 0),
                'server_ids' => (array)($payload['server_ids'] ?? []),
                'skill_keys' => (array)($payload['skill_keys'] ?? []),
            ], '用户已确认，正在执行操作', [[
                'name' => $toolName,
                'arguments' => $arguments,
                'result' => $toolResult,
            ]]);
        } catch (\Throwable $e) {
            $this->sendModelGuide($conversationId, $send, $e);
        }
    }

    /**
     * 把用户对问询卡片的作答规范化为工具结果（选项 value 映射回 value+label，附自定义输入）
     * @param array $arguments 问询工具入参（含 options 定义）
     * @param array $answers 用户选中的选项 value 列表
     * @param string $custom 用户自定义输入文本
     * @return array
     */
    private function buildAskUserAnswer(array $arguments, array $answers, string $custom): array
    {
        $options = [];
        foreach ((array)($arguments['options'] ?? []) as $option) {
            if (is_array($option)) {
                $value = trim((string)($option['value'] ?? ''));
                $label = trim((string)($option['label'] ?? ''));
            } else {
                $value = trim((string)$option);
                $label = $value;
            }
            if ($value !== '') {
                $options[$value] = ['value' => $value, 'label' => $label !== '' ? $label : $value];
            }
        }
        $selected = [];
        foreach ($answers as $answer) {
            $value = trim((string)$answer);
            if (isset($options[$value])) {
                $selected[] = $options[$value];
            }
        }
        $custom = mb_substr(trim($custom), 0, 500);
        $summary = [];
        foreach ($selected as $item) {
            $summary[] = $item['label'];
        }
        if ($custom !== '') {
            $summary[] = '自定义：' . $custom;
        }
        return [
            'status' => 'answered',
            'selected' => $selected,
            'custom' => $custom,
            'message' => '用户已作答' . ($summary !== [] ? '：' . implode('；', $summary) : '（未选择任何选项）')
                . '。请依据该结果继续，不要重复提问',
        ];
    }

    /**
     * 工具需要的用户交互类型（依据本次对话挂载的工具定义标记）
     * @param string $toolName 工具名
     * @param array $definitions 本次对话挂载的工具定义
     * @return string|null execute=确认后执行 / ask_user=用户作答后回传 / null=无需交互直接执行
     */
    private function resolveInteraction(string $toolName, array $definitions): ?string
    {
        foreach ($definitions as $definition) {
            if (($definition['name'] ?? '') === $toolName) {
                if (empty($definition['requires_confirm'])) {
                    return null;
                }
                $interaction = (string)($definition['interaction'] ?? 'execute');
                return $interaction !== '' ? $interaction : 'execute';
            }
        }
        return null;
    }

    /**
     * 交互卡片的中文展示信息：优先使用模型输出的 display（中文标题+参数说明），缺失时降级为原始字段
     * @param array $toolCall parseToolCall 解析结果
     * @return array{title:string,params:array}
     */
    private function buildCardDisplay(array $toolCall, array $definitions = []): array
    {
        $display = is_array($toolCall['display'] ?? null) ? $toolCall['display'] : [];
        $title = trim((string)($display['title'] ?? ''));
        $params = is_array($display['params'] ?? null) ? $display['params'] : [];
        // 模型未输出中文说明时，用工具定义自带的中文元数据兜底（描述→标题、入参 Schema 说明→参数标签），
        // 避免确认卡降级直出工具名与英文字段名
        if ($title === '' || $params === []) {
            $definition = $this->findDefinitionByName((string)($toolCall['name'] ?? ''), $definitions);
            if ($definition !== null) {
                if ($title === '') {
                    $title = $this->displayTitleFromDescription((string)($definition['description'] ?? ''));
                }
                if ($params === []) {
                    $params = $this->displayParamsFromSchema(
                        is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [],
                        is_array($definition['inputSchema'] ?? null) ? $definition['inputSchema'] : []
                    );
                }
            }
        }
        if ($params === []) {
            // 最终降级：参数名直出
            $params = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        }
        return ['title' => $title, 'params' => $params];
    }

    /**
     * 按名称在工具定义清单中查找定义
     * @param string $name 工具名
     * @param array $definitions 工具定义清单
     * @return array|null
     */
    private function findDefinitionByName(string $name, array $definitions): ?array
    {
        foreach ($definitions as $definition) {
            if (($definition['name'] ?? '') === $name) {
                return $definition;
            }
        }
        return null;
    }

    /**
     * 从工具中文描述提炼确认卡标题（取首个句号/分号/换行前主干句子；首句仍超长时按逗号取主干，截断兜底）
     * 如「修改系统设置（系统配置）中指定配置项的值；需传入…」→「修改系统设置（系统配置）中指定配置项的值」
     * 如「修改第三方驱动的配置项值，支持短信/邮件/…。需传入…」→「修改第三方驱动的配置项值」
     * @param string $description 工具中文描述
     * @return string
     */
    private function displayTitleFromDescription(string $description): string
    {
        $main = preg_split('/[。；;\n]/u', trim($description))[0] ?? '';
        $main = trim((string)$main);
        if (mb_strlen($main) > 30) {
            $main = trim((string)(preg_split('/[，,]/u', $main)[0] ?? $main));
        }
        if (mb_strlen($main) > 30) {
            $main = mb_substr($main, 0, 30) . '…';
        }
        return $main;
    }

    /**
     * 用入参 Schema 的中文 description 作为确认卡参数标签（取首个逗号/括号/分号/冒号前的主干），无说明时退回字段名；
     * 标签超长截断兜底，避免整段枚举说明（如「驱动类型：sms=短信 email=邮件…」）直接上卡
     * 如「配置变量名，如 site_url」→ 标签「配置变量名」；「驱动启停状态：1=启用 0=禁用」→ 标签「驱动启停状态」
     * @param array $arguments 工具入参
     * @param array $schema 工具 inputSchema
     * @return array<string,string> 参数标签 => 取值
     */
    private function displayParamsFromSchema(array $arguments, array $schema): array
    {
        $properties = is_array($schema['properties'] ?? null) ? $schema['properties'] : [];
        $params = [];
        foreach ($arguments as $name => $value) {
            $label = (string)$name;
            $description = is_array($properties[$name] ?? null) ? trim((string)($properties[$name]['description'] ?? '')) : '';
            if ($description !== '') {
                $main = preg_split('/[，,（(；;：:]/u', $description)[0] ?? '';
                $main = trim((string)$main);
                if ($main !== '') {
                    $label = $main;
                }
            }
            if (mb_strlen($label) > 12) {
                $label = mb_substr($label, 0, 12) . '…';
            }
            $params[$label] = $this->displayValueOf((string)$name, $value);
        }
        return $params;
    }

    /**
     * 确认卡参数取值展示：对象序列化为 JSON（嵌套的敏感键同样打码），敏感字段值不明文展示
     * @param string $name 字段名
     * @param mixed $value 字段值
     * @return string
     */
    private function displayValueOf(string $name, $value): string
    {
        if (is_array($value)) {
            array_walk_recursive($value, function (&$v, $k) {
                if (is_scalar($v) && self::isSensitiveField((string)$k)) {
                    $v = $this->maskSensitiveValue((string)$v);
                }
            });
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (self::isSensitiveField($name)) {
            return $this->maskSensitiveValue((string)$value);
        }
        return (string)$value;
    }

    /**
     * 判断字段名是否为敏感字段（密码/授权码/密钥/令牌类），确认卡展示时需打码
     * @param string $name 字段名
     * @return bool
     */
    private static function isSensitiveField(string $name): bool
    {
        return (bool)preg_match('/password|passwd|secret|auth[_-]?code|token|app[_-]?key|api[_-]?key|access[_-]?key/i', $name);
    }

    /**
     * 敏感值打码：保留末 4 位便于用户核对，其余以 * 号遮蔽
     * @param string $value 原始值
     * @return string
     */
    private function maskSensitiveValue(string $value): string
    {
        if ($value === '') {
            return '';
        }
        return mb_strlen($value) <= 4 ? '******' : '******' . mb_substr($value, -4);
    }

    /**
     * 创建思考过程过滤器：自然语言实时透传，工具调用 JSON 暂存并隐藏。
     * @return array<string, mixed>
     */
    private function newReasoningFilter(): array
    {
        return [
            'pending' => '',
            'inToolCall' => false,
            'depth' => 0,
            'inString' => false,
            'escape' => false,
        ];
    }

    /** @return array{pending:string,inPrivateCall:bool} */
    private function newPrivateCallFilter(): array
    {
        return ['pending' => '', 'inPrivateCall' => false];
    }

    /**
     * 隐藏一号通可能输出的私有函数调用区块，兼容开始/结束标记跨数据块。
     * @param array{pending:string,inPrivateCall:bool} $state
     */
    private function filterPrivateCallChunk(array &$state, string $text, callable $emit): void
    {
        $begin = '<|FunctionCallBegin|>';
        $end = '<|FunctionCallEnd|>';
        $state['pending'] .= $text;

        while ($state['pending'] !== '') {
            if (!$state['inPrivateCall']) {
                $position = strpos($state['pending'], $begin);
                if ($position !== false) {
                    if ($position > 0) {
                        $emit(substr($state['pending'], 0, $position));
                    }
                    $state['pending'] = substr($state['pending'], $position + strlen($begin));
                    $state['inPrivateCall'] = true;
                    continue;
                }
                $holdLength = $this->reasoningMarkerSuffixLength($state['pending'], $begin);
                $emitLength = strlen($state['pending']) - $holdLength;
                if ($emitLength > 0) {
                    $emit(substr($state['pending'], 0, $emitLength));
                }
                $state['pending'] = $holdLength > 0 ? substr($state['pending'], -$holdLength) : '';
                return;
            }

            $position = strpos($state['pending'], $end);
            if ($position !== false) {
                $state['pending'] = substr($state['pending'], $position + strlen($end));
                $state['inPrivateCall'] = false;
                continue;
            }
            // 私有调用区块内的内容全部丢弃，仅保留可能构成结束标记的后缀。
            $holdLength = $this->reasoningMarkerSuffixLength($state['pending'], $end);
            $state['pending'] = $holdLength > 0 ? substr($state['pending'], -$holdLength) : '';
            return;
        }
    }

    /** @param array{pending:string,inPrivateCall:bool} $state */
    private function flushPrivateCallFilter(array &$state, callable $emit): void
    {
        if (!$state['inPrivateCall'] && $state['pending'] !== '') {
            $emit($state['pending']);
        }
        $state['pending'] = '';
    }

    /**
     * 实时过滤 reasoning 中的 {"tool_call":...}，支持标记和 JSON 跨数据块。
     * @param array<string, mixed> $state
     * @param callable $emit function(string $safeText): void
     */
    private function filterReasoningChunk(array &$state, string $text, callable $emit): void
    {
        $marker = '{"tool_call"';
        $state['pending'] .= $text;

        while ($state['pending'] !== '') {
            if (!$state['inToolCall']) {
                $position = strpos($state['pending'], $marker);
                if ($position !== false) {
                    if ($position > 0) {
                        $emit(substr($state['pending'], 0, $position));
                    }
                    $state['pending'] = substr($state['pending'], $position);
                    $state['inToolCall'] = true;
                    $state['depth'] = 0;
                    $state['inString'] = false;
                    $state['escape'] = false;
                    continue;
                }

                $holdLength = $this->reasoningMarkerSuffixLength($state['pending'], $marker);
                $emitLength = strlen($state['pending']) - $holdLength;
                if ($emitLength > 0) {
                    $emit(substr($state['pending'], 0, $emitLength));
                }
                $state['pending'] = $holdLength > 0 ? substr($state['pending'], -$holdLength) : '';
                return;
            }

            $buffer = $state['pending'];
            $length = strlen($buffer);
            for ($index = 0; $index < $length; $index++) {
                $char = $buffer[$index];
                if ($state['inString']) {
                    if ($state['escape']) {
                        $state['escape'] = false;
                    } elseif ($char === '\\') {
                        $state['escape'] = true;
                    } elseif ($char === '"') {
                        $state['inString'] = false;
                    }
                    continue;
                }
                if ($char === '"') {
                    $state['inString'] = true;
                } elseif ($char === '{') {
                    $state['depth']++;
                } elseif ($char === '}') {
                    $state['depth']--;
                    if ($state['depth'] === 0) {
                        $state['pending'] = substr($buffer, $index + 1);
                        $state['inToolCall'] = false;
                        $state['inString'] = false;
                        $state['escape'] = false;
                        continue 2;
                    }
                }
            }
            // 工具 JSON 尚未闭合，状态已记录扫描进度；已处理内容无需继续保留。
            $state['pending'] = '';
            return;
        }
    }

    /**
     * 计算文本末尾与标记前缀的最长部分匹配长度（用于跨块截断的标签缓冲）
     * @param string $text 文本内容
     * @param string $marker 完整标记（如 think 闭合标签）
     * @return int 匹配长度，最长为标记长度减一，无匹配返回 0
     */
    private function reasoningMarkerSuffixLength(string $text, string $marker): int
    {
        $maximum = min(strlen($text), strlen($marker) - 1);
        for ($length = $maximum; $length > 0; $length--) {
            if (substr($text, -$length) === substr($marker, 0, $length)) {
                return $length;
            }
        }
        return 0;
    }

    /** @param array<string, mixed> $state */
    private function flushReasoningFilter(array &$state, callable $emit): void
    {
        // 未进入工具 JSON 时，pending 只是可能匹配标记的短前缀，应正常输出。
        if (!$state['inToolCall'] && $state['pending'] !== '') {
            $emit((string)$state['pending']);
        }
        $state['pending'] = '';
    }

    /**
     * 创建流式内容过滤器状态
     *
     * @return array 过滤器状态（引用传递给 filterStreamChunk / flushStreamFilter）
     */
    private function newStreamFilter(): array
    {
        return [
            'inThink' => false,   // 当前是否位于 <think> 标签内部
            'pending' => '',      // 跨块截断的标签前缀缓冲
            'held' => '',         // 抑制判定前暂存的正文
            'decided' => false,   // 是否已完成「是否疑似工具调用 JSON」判定
            'blocked' => false,   // 判定为疑似工具调用 JSON，整轮流式正文全部抑制
            // 推荐追问 JSON 位于回答末尾，不能在最终提取前透传到前端。
            // 用独立缓冲兼容 {"followups" 标记与 JSON 对象跨 SSE 数据块的情况。
            'followupPending' => '',
            'inFollowup' => false,
            'followupDepth' => 0,
            'followupInString' => false,
            'followupEscape' => false,
        ];
    }

    /**
     * 过滤器逐块处理正文增量
     *
     * 1. 推理模型可能不返回标准 reasoning 字段，而是把思维链以 <think>...</think>
     *    （含 </think_xxx> 等变体闭合标签）内嵌在正文流中：标签内文本改道为 reasoning 增量；
     * 2. 工具调用 JSON（首段非空白字符为 { 或 ```）在流式阶段无法与正文区分，
     *    一旦命中即整轮抑制正文外发，待流结束后由调用方统一判定（执行工具或补发正文）。
     *
     * @param array $state 过滤器状态
     * @param string $text 本次到达的正文增量（标签可能跨块截断）
     * @param callable $onReasoning function(string $text): void 思维链增量出站
     * @param callable $onContent function(string $text): void 正文增量出站
     */
    private function filterStreamChunk(array &$state, string $text, callable $onReasoning, callable $onContent): void
    {
        $buffer = ($state['pending'] ?? '') . $text;
        $state['pending'] = '';

        while ($buffer !== '') {
            if ($state['inThink']) {
                // 思维链模式：扫描闭合标签（兼容 </think_xxx> 变体）
                $matched = preg_match('~</think[^>]*>~i', $buffer, $m, PREG_OFFSET_CAPTURE) ? $m[0] : null;
                if ($matched === null) {
                    $hold = $this->streamHoldSuffix($buffer);
                    $emit = $hold === '' ? $buffer : substr($buffer, 0, strlen($buffer) - strlen($hold));
                    if ($emit !== '') {
                        $onReasoning($emit);
                    }
                    $state['pending'] = $hold;
                    return;
                }
                $pos = (int)$matched[1];
                if ($pos > 0) {
                    $onReasoning(substr($buffer, 0, $pos));
                }
                $buffer = substr($buffer, $pos + strlen((string)$matched[0]));
                $state['inThink'] = false;
                continue;
            }

            // 正文模式：扫描 <think> 开标签
            $matched = preg_match('~<think[^>]*>~i', $buffer, $m, PREG_OFFSET_CAPTURE) ? $m[0] : null;
            // 部分兼容网关会漏掉开标签、只输出 </think_xxx>。闭合标记本身不能进入最终答复。
            $closing = preg_match('~</think[^>]*>~i', $buffer, $close, PREG_OFFSET_CAPTURE) ? $close[0] : null;
            if ($closing !== null && ($matched === null || (int)$closing[1] < (int)$matched[1])) {
                $pos = (int)$closing[1];
                if ($pos > 0) {
                    $this->emitStreamContent($state, substr($buffer, 0, $pos), $onContent);
                }
                $buffer = substr($buffer, $pos + strlen((string)$closing[0]));
                continue;
            }
            if ($matched === null) {
                $hold = $this->streamHoldSuffix($buffer);
                $emit = $hold === '' ? $buffer : substr($buffer, 0, strlen($buffer) - strlen($hold));
                if ($emit !== '') {
                    $this->emitStreamContent($state, $emit, $onContent);
                }
                $state['pending'] = $hold;
                return;
            }
            $pos = (int)$matched[1];
            if ($pos > 0) {
                $this->emitStreamContent($state, substr($buffer, 0, $pos), $onContent);
            }
            $buffer = substr($buffer, $pos + strlen((string)$matched[0]));
            $state['inThink'] = true;
        }
    }

    /**
     * 流结束后冲刷过滤器残留缓冲（跨块截断的未闭合标签前缀）
     * @param array $state 过滤器状态
     * @param callable $onReasoning function(string $text): void
     * @param callable $onContent function(string $text): void
     */
    private function flushStreamFilter(array &$state, callable $onReasoning, callable $onContent): void
    {
        $pending = (string)($state['pending'] ?? '');
        if ($pending === '') {
            $this->flushFollowupStreamContent($state, $onContent);
            return;
        }
        $state['pending'] = '';
        if ($state['inThink']) {
            $onReasoning($pending);
        } else {
            $this->emitStreamContent($state, $pending, $onContent);
        }
        $this->flushFollowupStreamContent($state, $onContent);
    }

    /**
     * 过滤器正文出站：完成「疑似工具调用 JSON」抑制判定
     * @param array $state 过滤器状态
     * @param string $text 待出站正文
     * @param callable $onContent function(string $text): void
     */
    private function emitStreamContent(array &$state, string $text, callable $onContent): void
    {
        if ($text === '') {
            return;
        }
        if (!($state['decided'])) {
            // 判定前全部暂存，直到首段非空白内容可判定走向
            $state['held'] .= $text;
            $trimmed = ltrim((string)$state['held']);
            if ($trimmed === '') {
                return;
            }
            $state['decided'] = true;
            $first = $trimmed[0];
            if ($first === '{' || str_starts_with($trimmed, '```')) {
                // 疑似工具调用 JSON：整轮抑制正文外发
                $state['blocked'] = true;
                return;
            }
            $this->emitFollowupSafeContent($state, (string)$state['held'], $onContent);
            $state['held'] = '';
            return;
        }
        if (!($state['blocked'])) {
            $this->emitFollowupSafeContent($state, $text, $onContent);
        }
    }

    /**
     * 输出正文时拦截模型附在尾部的推荐追问 JSON。
     * 原始内容仍由调用方累计，流结束后 extractFollowups() 会解析并作为独立 SSE 事件发送。
     */
    private function emitFollowupSafeContent(array &$state, string $text, callable $onContent): void
    {
        $marker = '{"followups"';
        $buffer = (string)($state['followupPending'] ?? '') . $text;
        $state['followupPending'] = '';

        while ($buffer !== '') {
            if (!empty($state['inFollowup'])) {
                $length = strlen($buffer);
                for ($index = 0; $index < $length; $index++) {
                    $char = $buffer[$index];
                    if (!empty($state['followupInString'])) {
                        if (!empty($state['followupEscape'])) {
                            $state['followupEscape'] = false;
                        } elseif ($char === '\\') {
                            $state['followupEscape'] = true;
                        } elseif ($char === '"') {
                            $state['followupInString'] = false;
                        }
                        continue;
                    }
                    if ($char === '"') {
                        $state['followupInString'] = true;
                    } elseif ($char === '{') {
                        $state['followupDepth']++;
                    } elseif ($char === '}') {
                        $state['followupDepth']--;
                        if ($state['followupDepth'] === 0) {
                            $state['inFollowup'] = false;
                            $state['followupInString'] = false;
                            $state['followupEscape'] = false;
                            $buffer = substr($buffer, $index + 1);
                            continue 2;
                        }
                    }
                }
                // 追问对象尚未结束：整段继续抑制，等待下一个 SSE 数据块。
                return;
            }

            $position = strpos($buffer, $marker);
            if ($position !== false) {
                if ($position > 0) {
                    $onContent(substr($buffer, 0, $position));
                }
                $state['inFollowup'] = true;
                $state['followupDepth'] = 0;
                $state['followupInString'] = false;
                $state['followupEscape'] = false;
                $buffer = substr($buffer, $position);
                continue;
            }

            // 标记可能被 SSE 块切开，暂存可构成标记前缀的尾部字符。
            $keepLength = $this->followupMarkerSuffixLength($buffer, $marker);
            if ($keepLength > 0) {
                $onContent(substr($buffer, 0, -$keepLength));
                $state['followupPending'] = substr($buffer, -$keepLength);
            } else {
                $onContent($buffer);
            }
            return;
        }
    }

    /** 计算文本尾部与追问标记前缀的最长匹配，供跨 SSE 块缓冲。 */
    private function followupMarkerSuffixLength(string $text, string $marker): int
    {
        $maximum = min(strlen($text), strlen($marker) - 1);
        for ($length = $maximum; $length > 0; $length--) {
            if (substr($text, -$length) === substr($marker, 0, $length)) {
                return $length;
            }
        }
        return 0;
    }

    /** 流结束时仅补发普通正文残留；未闭合的追问 JSON 一律不透传。 */
    private function flushFollowupStreamContent(array &$state, callable $onContent): void
    {
        if (empty($state['inFollowup']) && ($state['followupPending'] ?? '') !== '') {
            $onContent((string)$state['followupPending']);
        }
        $state['followupPending'] = '';
    }

    /**
     * 保留缓冲区尾部可能构成标签的前缀（自最后一个未闭合的 < 起）
     * @param string $buffer 当前缓冲
     * @return string 需要留待下一块拼接的后缀，无需保留时返回空串
     */
    private function streamHoldSuffix(string $buffer): string
    {
        $lastLt = strrpos($buffer, '<');
        if ($lastLt === false) {
            return '';
        }
        $suffix = substr($buffer, $lastLt);
        // 已闭合（含 >）说明不是跨块截断的标签前缀
        if (strpos($suffix, '>') !== false) {
            return '';
        }
        return $suffix;
    }

    /**
     * 准备对话上下文：连接器/Skill 挂载、提示词与转录、模型驱动
     * @param int $conversationId 会话 ID
     * @param array $serverIds 连接器 ID 列表
     * @param array $skillKeys Skill 标识列表
     * @param int $modelId 模型 ID
     * @param array $preloadSkillKeys 确认续跑场景预载的 Skill 标识（仅收窄到目录内，避免模型重复加载）
     * @param array $runtimeOptions 运行时选项（unattended/autonomy，见 chat() 注释）
     * @return array{definitions:array,allowedTools:array,skillCatalog:array,skillFiles:array,skillsUsed:array,prompts:array,workingMessage:string,driver:\crmeb\services\ai\AiInterface}
     */
    private function prepareContext(int $conversationId, array $serverIds, array $skillKeys, int $modelId, array $preloadSkillKeys = [], array $runtimeOptions = [], int $adminId = 0): array
    {
        $unattended = !empty($runtimeOptions['unattended']);
        $autonomy = (int)($runtimeOptions['autonomy'] ?? 1);
        // 未指定连接器时自动挂载全部可用项（内置工具 + 启用中的远程 Server）
        if ($serverIds === []) {
            $serverIds = array_map('intval', array_column($this->mcp->serverOptions(), 'id'));
        }
        $skillFiles = [];
        $catalog = [];
        if ($skillKeys === []) {
            // 未指定 Skill 时不做全文注入：仅下发目录（标识|名称|说明），模型判断问题相关后
            // 经 crmeb_skill_load 按需加载全文规则，避免全部 Skill 全文占用 token
            $catalog = array_map(fn(array $skill) => [
                'key'         => $skill['key'],
                'name'        => $skill['name'],
                'description' => $skill['description'],
            ], array_values(array_filter(
                $this->skills->all(),
                fn(array $skill) => (int)$skill['status'] === 1
            )));
            // 确认续跑场景：恢复确认前已加载的 Skill（仅收窄到目录内）
            $skillFiles = $this->skills->load(array_values(array_intersect(
                array_map('strval', $preloadSkillKeys),
                array_column($catalog, 'key')
            )));
        } else {
            // 显式选择的 Skill 语义为「确定要用」：直接全文注入，无需二次加载
            $skillFiles = $this->skills->load($skillKeys);
        }
        $definitions = $this->mcp->definitionsForServers($serverIds, $unattended, $autonomy);
        if ($catalog !== []) {
            $definitions[] = $this->skillLoadDefinition();
        }
        // 已加载 Skill 的展示清单（key + 中文名），随 skills 事件下发前端标签展示
        $skillNames = array_column($this->skills->all(), 'name', 'key');
        $skillsUsed = [];
        foreach (array_keys($skillFiles) as $key) {
            $skillsUsed[] = ['key' => $key, 'name' => (string)($skillNames[$key] ?? $key)];
        }
        $recent = $this->agents->recentMessages($conversationId, 50);
        if (!in_array('user', array_column($recent, 'role'), true)) {
            $latestUser = $this->agents->latestUserMessage($conversationId);
            if ($latestUser !== null) {
                array_unshift($recent, $latestUser);
            }
        }
        return [
            'admin_id'     => $adminId,
            'definitions'  => $definitions,
            'allowedTools' => array_column($definitions, 'name'),
            'skillCatalog' => $catalog,
            'skillFiles'   => $skillFiles,
            'skillsUsed'   => $skillsUsed,
            'unattended'   => $unattended,
            // 任务登记的通知邮箱：无人值守邮件发送仅允许此处与系统收件白名单
            'notify_email' => (string)($runtimeOptions['notify_email'] ?? ''),
            // 本轮已加载的 Skill 附属资料（key:file => true），用于附件重复加载幂等
            'skillAttachmentFiles' => [],
            'prompts'      => $this->buildPrompts($definitions, $skillFiles, $catalog, $unattended),
            'workingMessage' => $this->buildTranscript($recent),
            'driver'       => app()->make(AiModelServices::class)->getChatHandler($modelId),
        ];
    }

    /**
     * 无人值守邮件收件校验：自动化任务执行时，邮件仅允许发往任务登记的通知邮箱
     * 与系统「邮件收件白名单」（aiagent_email_recipients，逗号/分号/换行分隔）中的地址，
     * 防止模型把工具查询到的业务数据发往任意外部邮箱；两者都未配置时禁止发送。
     * 人工对话场景不走本校验（发送前已有确认卡供用户核对收件人）。
     * @param string $toolName 工具名（builtin.crmeb_email_send）
     * @param array $arguments 工具入参
     * @param array $context 对话上下文（unattended/notify_email）
     * @throws \RuntimeException 收件地址不在允许范围时抛出
     */
    private function guardUnattendedEmail(string $toolName, array $arguments, array $context): void
    {
        if (empty($context['unattended'])) {
            return;
        }
        if ($toolName !== self::EMAIL_TOOL && !str_ends_with($toolName, '.' . self::EMAIL_TOOL)) {
            return;
        }
        $to = strtolower(trim((string)($arguments['to'] ?? '')));
        $taskEmail = strtolower(trim((string)($context['notify_email'] ?? '')));
        $allowed = $taskEmail !== '' ? [$taskEmail] : [];
        $whitelist = preg_split('/[,;\n\r]+/', (string) sys_config('aiagent_email_recipients', '')) ?: [];
        foreach ($whitelist as $item) {
            $item = strtolower(trim((string)$item));
            if ($item !== '') {
                $allowed[] = $item;
            }
        }
        if ($to === '' || !in_array($to, array_unique($allowed), true)) {
            throw new \RuntimeException($taskEmail !== ''
                ? "无人值守任务仅允许把邮件发送到任务登记的通知邮箱（{$taskEmail}）或系统邮件收件白名单中的地址"
                : '无人值守任务未登记通知邮箱且系统未配置邮件收件白名单，禁止发送邮件；请在任务配置中登记收件邮箱');
        }
    }

    /**
     * 组装系统提示词（AGENTS.md 兜底规则 + Skill 规则 + 可用工具说明）
     * @param array $definitions 本次对话挂载的工具定义
     * @param array $skillFiles 本次全文注入的 Skill（key => 正文）
     * @param array $catalog 按需加载的 Skill 目录（key/name/description），非空时注入目录提示
     * @param bool $unattended 无人值守模式（自动化任务执行），注入执行者角色提示并禁用提问类指引
     * @return array<int, string>
     */
    private function buildPrompts(array $definitions, array $skillFiles, array $catalog = [], bool $unattended = false): array
    {
        $prompts = [];
        // 当前时间锚点：模型自身无法获取日期（无人值守下更无上下文可推断），
        // 不注入会导致邮件落款等时间表述照抄 Skill 文档示例日期（如 2026-06-01）
        $prompts[] = '当前系统时间：' . date('Y-m-d H:i') . '，凡涉及「今天/当前/生成时间」等时间表述一律以此为准，禁止照抄任何文档示例日期。';
        if ($unattended) {
            // 无人值守执行者角色提示：先于其他规则注入，明确任务边界与交付纪律，
            // 避免模型把任务指令当作需求咨询来回答（输出方案说明而非实际执行）
            $prompts[] = '运行模式（最高优先级）：你是无人值守的定时任务执行器，调度器只负责按频率唤起你，'
                . '任务指令中关于执行频率/时间的描述（如「每10分钟执行一次」）由调度器兑现，与你无关——本次调用只需完成一个周期的实际工作后立即结束。'
                . '禁止向用户提问或等待确认（无人应答，ask_user 类工具不可用），信息不足时按任务上下文与合理默认值继续执行；'
                . '必须优先调用工具完成实际业务动作（查询数据、执行操作），禁止只输出方案说明、实现建议或「系统能力不足」类答复；'
                . '任务要求产出数据类结果（统计/报表/清单）时，必须先调用数据查询类工具取得真实数据，再基于查询结果交付，禁止用占位描述或虚构数据充当结果；'
                . '任务涉及结果送达（发送邮件/推送通知）时，完成业务动作后必须调用对应交付工具（如 builtin.crmeb_email_send / builtin.crmeb_qywechat_send）'
                . '把最终结果完整发送出去，收件目标以任务描述为准。';
        }
        // 兜底规则：恒定最先注入 .crmeb/AGENTS.md（随代码发布，不可在后台删停）
        $agentsRule = $this->loadAgentsRule();
        if ($agentsRule !== '') {
            $prompts[] = "以下是本系统的智能体基础规则（AGENTS.md），请始终遵循：\n{$agentsRule}";
        }
        foreach ($skillFiles as $key => $content) {
            $prompts[] = "以下是当前启用的 Skill [{$key}]，请遵循其中的工作规则：\n{$content}";
        }
        if ($catalog !== []) {
            $lines = [];
            foreach ($catalog as $skill) {
                $lines[] = '- ' . $skill['key'] . ' | ' . $skill['name'] . ' | ' . $skill['description'];
            }
            $prompts[] = "以下是可按需加载的 Skill 目录（格式：标识 | 名称 | 说明）：\n" . implode("\n", $lines) . "\n"
                . '当判断当前问题涉及某个 Skill 的领域时，先调用工具 ' . self::SKILL_LOAD_TOOL
                . ' 加载其完整工作规则（{"tool_call":{"name":"' . self::SKILL_LOAD_TOOL . '","arguments":{"key":"目录中的标识"}}}），拿到规则后再继续；'
                . '部分 Skill 附带资料附件：加载结果中的 attachments 列出可用附件，需要更完整资料时在 arguments 中加 file 加载，同一附件只需加载一次；'
                . '问题与所有 Skill 均无关时禁止加载；同一 Skill 只需加载一次。';
        }
        // 输出纪律：非推理模型常把思维链混进正文输出，正文中的独白前端无法与最终答复区分，故强约束
        $prompts[] = '输出纪律（最高优先级）：回复正文只允许包含面向用户的最终答复。'
            . '一切内部推敲、犹豫、自我质疑与纠错（如「不对」「再看看」「等一下」之类的自言自语）必须在你内部完成，'
            . '绝对禁止写入正文；也不要以过程性叙述（如「让我看看」「我需要先确认」）开场，'
            . '正文第一句就必须是正式回答或工具调用 JSON。';
        $prompts[] = '回答完成后，如存在用户可能想继续追问的问题，在回答正文之后另起一行输出一行 JSON：'
            . '{"followups":["问题一","问题二","问题三"]}；'
            . '问题必须与本次回答主题相关、口语化、简短（不超过30字）、可直接作为新提问发送；最多 3 个；'
            . '无合适的追问时不要输出该 JSON。除该 JSON 外不要输出任何多余内容。';
        if ($definitions) {
            $prompts[] = "你可以调用以下 MCP 工具：\n"
                . json_encode($definitions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                . "\n需要调用工具时，只输出一个 JSON 对象，不要使用 Markdown："
                . '{"tool_call":{"name":"工具名","arguments":{}}}'
                . '；调用会修改数据的工具（requires_confirm:true）时必须附带 display 字段，用中文向用户展示操作标题与参数说明，'
                . '格式：{"tool_call":{"name":"工具名","arguments":{},"display":{"title":"中文操作标题","params":{"中文参数名":"中文取值说明"}}}}，'
                . 'title 与 params 禁止出现工具名、字段名、枚举值等技术词汇；'
                . 'params 每项取值必须是 20 字以内的中文摘要，正文/内容类参数只写内容概括（如「平台用户统计汇总报表 HTML 正文」），'
                . '禁止把正文全文、HTML 源码或长文本原文放进 params（确认卡会基于 arguments 自动提供内容渲染预览）。'
                . ($unattended
                    ? '本次为无人值守执行，ask_user 类提问工具不可用，信息不足时按合理默认值继续执行，禁止在正文中向用户提问。'
                    : '需要用户补充信息（如时间范围、筛选条件）、做出选择或在执行前拍板时，必须调用 crmeb_ask_user 工具发起卡片提问'
                    . '（自由填写用 type:text，系统会弹出输入框/选择卡，用户提交后答案回传），'
                    . '禁止在正文中直接向用户提问并等待回复——纯文本提问不会弹出输入框。')
                . '每次只输出一个调用，禁止在一条回复中连续输出多个调用 JSON。'
                . '拿到工具结果后，再继续回答或调用下一个工具。'
                . '工具名必须与上述清单完全一致，禁止调用未列出的工具；同一调用失败后禁止原样重复，应换用清单中的其他工具或直接回答。'
                . '除工具调用 JSON 外，回答必须直接给出结论，禁止输出内心独白、推理过程或自我纠错文本。'
                . '禁止输出 <|FunctionCallBegin|>、<|FunctionCallEnd|> 等任何平台私有函数调用标记，统一使用上述 JSON 格式。';
        } else {
            $prompts[] = '当前对话没有挂载任何工具，请直接依据已有知识回答；'
                . '禁止输出 <|FunctionCallBegin|>、<|FunctionCallEnd|> 等任何函数调用标记。'
                . '禁止在回复中输出思考过程、内部推理或复述会话记录原文，直接给出最终答复。';
        }
        return $prompts;
    }

    /**
     * Skill 按需加载虚拟工具定义：目录非空时追加到工具清单，模型按目录判断后调用
     * 支持 file 参数加载 Skill 目录内的附属资料文件（如完整索引、文档清单）
     * @return array{name:string,description:string,inputSchema:array}
     */
    private function skillLoadDefinition(): array
    {
        return [
            'name'        => self::SKILL_LOAD_TOOL,
            'description' => '加载指定 Skill 的完整工作规则（规则以工具结果返回，仅本对话生效）。'
                . '仅当问题涉及 Skill 目录中某项领域时调用；一次只加载一个，同一 Skill 只需加载一次。'
                . '部分 Skill 附带资料附件：加载结果的 attachments 列出可用附件，'
                . '需要更完整的资料（完整清单、官方文档链接等）时，再在 arguments 中传 file 加载对应附件。',
            'inputSchema' => [
                'type'       => 'object',
                'required'   => ['key'],
                'properties' => [
                    'key'  => ['type' => 'string', 'description' => 'Skill 标识，必须取自 Skill 目录中的标识'],
                    'file' => ['type' => 'string', 'description' => '可选：附属资料文件名，必须取自该 Skill 加载结果 attachments 中的 file；不传时加载 Skill 工作规则本身'],
                ],
            ],
        ];
    }

    /**
     * 执行 Skill 按需加载（本地虚拟工具，拦截在 MCP 分发之前）
     * 不传 file 时加载 Skill 工作规则全文（写入 context.skillFiles 随 prompts 生效）；
     * 传 file 时加载 Skill 目录内的附属资料文件（内容仅随工具结果进入上下文，同附件幂等）
     * @param array $context 对话上下文（skillFiles/skillAttachmentFiles 以引用方式更新）
     * @param string $key 模型传入的 Skill 标识
     * @param string $file 可选附属资料文件名（取自 attachments 列表）
     * @return array 工具结果（含 skill 元信息、内容与可用附件清单）
     */
    private function loadSkillForContext(array &$context, string $key, string $file = ''): array
    {
        $key = trim($key);
        $meta = null;
        foreach ($context['skillCatalog'] as $skill) {
            if ($skill['key'] === $key) {
                $meta = $skill;
                break;
            }
        }
        if ($meta === null) {
            return [
                'error'        => true,
                'message'      => "Skill {$key} 不在本对话的 Skill 目录中，禁止调用。",
                'valid_skills' => array_column($context['skillCatalog'], 'key'),
            ];
        }
        // 附件加载：文件名必须取自 Skill 目录附属资料，加载成功仅随工具结果进入上下文
        $file = trim($file);
        if ($file !== '') {
            $attachmentKey = $key . ':' . $file;
            if (!empty($context['skillAttachmentFiles'][$attachmentKey])) {
                $loaded = $this->skills->loadAttachment($key, $file);
                if ($loaded !== null) {
                    return ['skill' => $meta, 'file' => $file, 'already_loaded' => true, 'content' => $loaded['content']];
                }
            }
            $loaded = $this->skills->loadAttachment($key, $file);
            if ($loaded === null) {
                return [
                    'error'       => true,
                    'message'     => "Skill {$key} 不存在附属资料 {$file}，可用附件以加载 Skill 时返回的 attachments 列表为准。",
                    'attachments' => $this->skills->attachments($key),
                ];
            }
            $context['skillAttachmentFiles'][$attachmentKey] = true;
            return ['skill' => $meta, 'file' => $file, 'content' => $loaded['content']];
        }
        if (isset($context['skillFiles'][$key])) {
            // 重复加载幂等：直接返回已加载内容，不重复写上下文
            return ['skill' => $meta, 'already_loaded' => true, 'content' => $context['skillFiles'][$key], 'attachments' => $this->skills->attachments($key)];
        }
        $loaded = $this->skills->load([$key]);
        if (!isset($loaded[$key])) {
            return ['error' => true, 'message' => "Skill {$key} 已停用或不存在，请改用目录中的其他能力或直接回答"];
        }
        $context['skillFiles'][$key] = $loaded[$key];
        $context['skillsUsed'][] = ['key' => $key, 'name' => (string)$meta['name']];
        $context['prompts'] = $this->buildPrompts(
            $context['definitions'], $context['skillFiles'], $context['skillCatalog'], (bool)$context['unattended']
        );
        return ['skill' => $meta, 'content' => $loaded[$key], 'attachments' => $this->skills->attachments($key)];
    }

    /**
     * 创建自动化任务（本地虚拟工具，拦截在 MCP 分发之前，处理人工对话场景）
     * 由模型把用户的周期性诉求落成任务定义：默认只读自主级别、创建即启用；
     * server_ids 留空，无人值守执行时 prepareContext 自动挂载全部可用连接器
     * @param int $adminId 管理员 ID（任务归属）
     * @param array $arguments 模型入参（name/instruction/schedule_type/schedule_value/notify_channel/notify_email）
     * @return array 工具结果
     */
    private function createTaskForContext(int $adminId, array $arguments): array
    {
        $channel = (string)($arguments['notify_channel'] ?? 'is_email');
        if (!in_array($channel, ['is_email', 'is_ent_wechat'], true)) {
            $channel = 'is_email';
        }
        $email = trim((string)($arguments['notify_email'] ?? ''));
        if ($channel === 'is_email' && $email === '') {
            return ['error' => true, 'message' => '推送渠道为邮件时必须提供收件邮箱，请先向用户询问后再创建任务'];
        }
        $tasks = app()->make(AgentTaskServices::class);
        $taskId = $tasks->save($adminId, [
            'name'            => (string)($arguments['name'] ?? ''),
            'instruction'     => (string)($arguments['instruction'] ?? ''),
            'autonomy'        => AgentTaskServices::AUTONOMY_READONLY,
            'schedule_type'   => (int)($arguments['schedule_type'] ?? AgentTaskServices::SCHEDULE_DAILY),
            'schedule_value'  => (string)($arguments['schedule_value'] ?? ''),
            'notify_channels' => [$channel],
            'notify_email'    => $email,
            'status'          => 1,
        ]);
        $task = $tasks->read($taskId, $adminId);
        return [
            'success'       => true,
            'task_id'       => $taskId,
            'name'          => (string)$task['name'],
            'schedule'      => $this->scheduleText((int)$task['schedule_type'], (string)$task['schedule_value']),
            'next_run_text' => (int)$task['next_run_time'] > 0 ? date('Y-m-d H:i', (int)$task['next_run_time']) : '',
            'message'       => '自动化任务已创建并启用，到期由调度器自动执行',
        ];
    }

    /**
     * 查询当前管理员的自动化任务清单（本地虚拟工具，只读直执行）
     * @param int $adminId 管理员 ID
     * @param array $arguments 模型入参（page 可选）
     * @return array 工具结果
     */
    private function listTasksForContext(int $adminId, array $arguments): array
    {
        $result = app()->make(AgentTaskServices::class)->index($adminId, max(1, (int)($arguments['page'] ?? 1)), 20);
        $list = array_map(fn(array $task): array => [
            'id'            => (int)$task['id'],
            'name'          => (string)$task['name'],
            'instruction'   => mb_substr((string)$task['instruction'], 0, 80),
            'schedule'      => $this->scheduleText((int)$task['schedule_type'], (string)$task['schedule_value']),
            'status'        => (int)$task['status'] === 1 ? '启用' : '停用',
            'next_run_text' => (int)$task['next_run_time'] > 0 ? date('Y-m-d H:i', (int)$task['next_run_time']) : '—',
            'last_run_text' => (int)$task['last_run_time'] > 0 ? date('Y-m-d H:i', (int)$task['last_run_time']) : '—',
        ], $result['list']);
        return ['count' => $result['count'], 'list' => $list];
    }

    /**
     * 调度类型与值转中文描述（供模型向用户转述）
     * @param int $type 调度类型
     * @param string $value 调度值
     * @return string
     */
    private function scheduleText(int $type, string $value): string
    {
        $weekNames = ['1' => '一', '2' => '二', '3' => '三', '4' => '四', '5' => '五', '6' => '六', '7' => '日'];
        return match ($type) {
            AgentTaskServices::SCHEDULE_MINUTES => "每隔 {$value} 分钟",
            AgentTaskServices::SCHEDULE_DAILY   => "每天 {$value}",
            AgentTaskServices::SCHEDULE_WEEKLY  => (isset($weekNames[$value[0] ?? '']) ? "每周{$weekNames[$value[0]]} " . substr($value, 2) : "每周调度 {$value}"),
            AgentTaskServices::SCHEDULE_MONTHLY => "每月 " . str_replace('|', ' 日 ', $value) . ' 执行',
            AgentTaskServices::SCHEDULE_YEARLY  => "每年 " . str_replace('|', ' 月 ', substr($value, 0, strrpos($value, '|'))) . ' 日 ' . substr($value, strrpos($value, '|') + 1) . ' 执行',
            default                             => "调度 {$value}",
        };
    }

    /**
     * 读取 .crmeb/AGENTS.md 兜底规则（文件缺失时返回空串，不阻断对话）
     * @return string
     */
    private function loadAgentsRule(): string
    {
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.crmeb' . DIRECTORY_SEPARATOR . 'AGENTS.md';
        if (!is_file($file)) {
            return '';
        }
        return $this->limitText((string)file_get_contents($file), 16000);
    }

    /**
     * 将历史消息构建为纯文本会话转录（作为非对话接口的上下文注入）
     * @param array $messages 历史消息列表
     * @return string 会话转录文本
     */
    private function buildTranscript(array $messages): string
    {
        $labels = ['user' => '用户', 'assistant' => '助手', 'tool' => '工具'];
        // 历史消息按角色限长：当前轮工具结果由 workingMessage 追加提供全文，历史工具结果只保留摘要，
        // 避免大结果（如全量配置清单）常驻转录把请求上下文撑爆、触发上游空响应
        $limits = ['user' => 4000, 'assistant' => 4000, 'tool' => 600];
        $messages = array_values(array_filter($messages, static fn(array $message) => ($message['role'] ?? '') !== 'confirm'));
        $lastUser = -1;
        foreach ($messages as $index => $message) {
            if (($message['role'] ?? '') === 'user') {
                $lastUser = $index;
            }
        }
        if ($lastUser >= 0) {
            // 历史只保留可见问答；当前轮保留工具调用及结果，且始终锚定原问题。
            $previous = array_values(array_filter(array_slice($messages, 0, $lastUser), static fn(array $message) =>
                in_array($message['role'] ?? '', ['user', 'assistant'], true) && empty($message['tool_name'])
            ));
            $current = array_slice($messages, $lastUser);
            if (count($current) > 16) {
                $current = array_merge([$current[0]], array_slice($current, -15));
            }
            $messages = array_merge(array_slice($previous, -8), $current);
        } else {
            $messages = array_slice($messages, -16);
        }
        $lines = ['以下是当前会话记录，请回答最后一条用户消息：'];
        foreach ($messages as $message) {
            $role = $labels[$message['role']] ?? $message['role'];
            $limit = !empty($message['tool_name']) && $message['role'] === 'assistant'
                ? 600 : ($limits[$message['role']] ?? 4000);
            $content = $this->limitText((string)$message['content'], $limit);
            $lines[] = "{$role}: {$content}";
        }
        return implode("\n\n", $lines);
    }

    /**
     * 同时兼容普通模型与推理模型的工具调用位置。
     * 标准情况下优先解析正文；仅当正文为空时，才从 reasoning 中兜底提取，
     * 避免把推理里用于举例的 JSON 误执行。
     *
     * @return array{name:string,arguments:array}|null
     */
    private function resolveToolCall(string $content, string $reasoning): ?array
    {
        $toolCall = $this->parseToolCall($content);
        if ($toolCall || trim($content) !== '') {
            return $toolCall;
        }
        return $this->parseToolCall($reasoning);
    }

    /** @param array<string, mixed> $arguments */
    private function canonicalToolCall(string $toolName, array $arguments): string
    {
        return (string)json_encode([
            'tool_call' => [
                'name' => $toolName,
                'arguments' => $arguments,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 将工具结果作为权威事实追加到当轮上下文，并显式提供可核对的数据摘要。
     * 这段文本同时适用于一号通和 OpenAI 兼容的自定义模型。
     * Skill 附属资料特殊处理：内容为纯 Markdown，直接注入（不走 JSON 转义）并放宽截断上限，避免长资料被截断。
     *
     * @param mixed $toolResult
     */
    private function buildToolResultContext(string $toolName, $toolResult): string
    {
        if ($toolName === self::SKILL_LOAD_TOOL
            && is_array($toolResult)
            && empty($toolResult['error'])
            && empty($toolResult['file'])) {
            $key = (string)($toolResult['skill']['key'] ?? '');
            $attachments = (string)json_encode($toolResult['attachments'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return "\n\n[系统提供的权威工具结果]\nSkill [{$key}] 已加载到本轮系统规则中。"
                . "\n可用附属资料：{$attachments}\n[/系统提供的权威工具结果]\n"
                . '请遵循已加载规则，按需加载附属资料或继续回答。';
        }
        // Skill 附属资料加载成功：原文即权威内容
        if ($toolName === self::SKILL_LOAD_TOOL
            && is_array($toolResult)
            && empty($toolResult['error'])
            && !empty($toolResult['file'])) {
            return "\n\n[系统提供的权威工具结果]\nSkill 附属资料：{$toolResult['file']}（已加载全文，引用时以原文为准）\n"
                . "内容：\n"
                . $this->limitText((string)($toolResult['content'] ?? ''), 32000)
                . "\n[/系统提供的权威工具结果]\n"
                . '如需其他资料可继续输出工具调用 JSON，否则直接给出最终答复。';
        }
        $encoded = (string)json_encode($toolResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $facts = [];
        if (is_array($toolResult) && empty($toolResult['error'])) {
            $facts[] = '工具调用状态：成功';
            if (isset($toolResult['count']) && is_numeric($toolResult['count'])) {
                $facts[] = 'count=' . (int)$toolResult['count'];
            }
            if (isset($toolResult['list']) && is_array($toolResult['list'])) {
                $facts[] = 'list实际条数=' . count($toolResult['list']);
            }
        } else {
            $facts[] = '工具调用状态：失败';
        }

        return "\n\n[系统提供的权威工具结果]\n工具：{$toolName}\n"
            . implode('；', $facts)
            . "\nJSON：\n"
            . $this->limitText($encoded, 16000)
            . "\n[/系统提供的权威工具结果]\n"
            . '必须以该结果为准：非空列表不得回答为空或未收到结果。'
            . '如需其他工具可继续输出工具调用 JSON，否则直接给出最终答复。';
    }

    /**
     * 工具已返回非空数据但模型声称为空时，自动进行一次纠正；仍不一致则直接返回权威结果。
     *
     * @param array $context
     * @param array $toolTrace
     * @param array{content:string,reasoning:string,model:string,usage:array} $answer
     * @return array{content:string,reasoning:string,model:string,usage:array}
     */
    private function ensureGroundedAnswer(array $context, array $toolTrace, array $answer): array
    {
        if (!$this->toolTraceHasData($toolTrace) || !$this->claimsNoData((string)$answer['content'])) {
            return $answer;
        }

        $prompts = $context['prompts'];
        $prompts[] = '你上一次的结论与工具返回事实冲突：工具明确返回了非空数据。'
            . '请重新读取用户消息下方最后一个“系统提供的权威工具结果”，准确列出结果并直接回答。'
            . '禁止回答未查到、列表为空、没有收到工具结果，也禁止再次调用工具。';
        try {
            $corrected = $context['driver']->chat($context['workingMessage'], [
                'prompts' => $prompts,
                'stream' => 0,
            ]);
        } catch (\Throwable $e) {
            return [
                'content' => $this->authoritativeToolFallback($toolTrace),
                'reasoning' => '',
                'model' => (string)$answer['model'],
                'usage' => (array)$answer['usage'],
            ];
        }
        $content = trim((string)($corrected['content'] ?? ''));
        $reasoning = trim((string)($corrected['reasoning'] ?? ''));
        $inlineReasoning = $this->extractThinkBlocks($content);
        if ($reasoning === '') {
            $reasoning = $inlineReasoning;
        }
        $content = $this->stripToolCallJson($content);
        if ($content !== '' && !$this->claimsNoData($content)) {
            return [
                'content' => $content,
                'reasoning' => $reasoning,
                'model' => (string)($corrected['model'] ?? $answer['model']),
                'usage' => (array)($corrected['usage'] ?? $answer['usage']),
            ];
        }

        return [
            'content' => $this->authoritativeToolFallback($toolTrace),
            'reasoning' => '',
            'model' => (string)$answer['model'],
            'usage' => (array)$answer['usage'],
        ];
    }

    /**
     * 判断本轮工具调用轨迹中是否存在返回有效数据的工具结果
     * @param array $toolTrace 工具调用轨迹（name/result 等）
     * @return bool 存在无错误且含 count>0 / 非空 list / 非空 data 的结果时为 true
     */
    private function toolTraceHasData(array $toolTrace): bool
    {
        foreach ($toolTrace as $trace) {
            $result = $trace['result'] ?? null;
            if (!is_array($result) || !empty($result['error'])) {
                continue;
            }
            if (isset($result['count']) && is_numeric($result['count']) && (int)$result['count'] > 0) {
                return true;
            }
            if (isset($result['list']) && is_array($result['list']) && $result['list'] !== []) {
                return true;
            }
            if (isset($result['data']) && is_array($result['data']) && $result['data'] !== []) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断模型正文是否声称「未查到数据/没有数据」之类的空结果表述（事实校验用）
     * @param string $content 模型生成的正文
     * @return bool 命中空结果表述时为 true
     */
    private function claimsNoData(string $content): bool
    {
        return (bool)preg_match(
            '/未查到|未查询到|没有(?:收到|获取到).*工具|工具.*(?:未返回|没有返回)|(?:列表|数据|记录)(?:为|是)?空|暂无(?:相关)?(?:数据|记录)|没有(?:任何)?(?:用户|商品|订单|记录|数据)/u',
            $content
        );
    }

    /**
     * 生成事实校正兜底回复：模型声称无数据但工具实际有数据时，直接下发系统确认的工具结果
     * @param array $toolTrace 工具调用轨迹（name/result 等）
     * @return string 兜底回复文本
     */
    private function authoritativeToolFallback(array $toolTrace): string
    {
        $results = [];
        foreach ($toolTrace as $trace) {
            if (!isset($trace['result']) || !is_array($trace['result']) || !empty($trace['result']['error'])) {
                continue;
            }
            $results[] = [
                'tool' => (string)($trace['name'] ?? ''),
                'result' => $trace['result'],
            ];
        }
        $json = (string)json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return "工具已成功返回数据，但模型生成的总结与结果不一致。以下为系统确认的工具结果：\n"
            . $this->limitText($json, 12000);
    }

    /**
     * 正文已经在本轮流式下发时不做额外操作；仅在被过滤、清洗或事实校正后与已输出文本不一致时替换。
     * @param array{content:string,rawContent:string,reasoning:string,suppressed:bool} $acc
     */
    private function syncStreamFinalAnswer(callable $send, array $acc, string $content): void
    {
        $streamed = trim((string)($acc['content'] ?? ''));
        if ($streamed === $content) {
            return;
        }
        $send([
            'event' => 'replace',
            'content' => $content,
            // 被纠正轮的 reasoning 不再补发，避免与已经实时展示的思考过程重复。
            'reasoning' => '',
        ]);
    }

    /** @param array<int, string> $processTrace */
    private function mergeReasoning(array $processTrace, string $modelReasoning): string
    {
        $parts = array_filter(array_merge($processTrace, [trim($modelReasoning)]));
        return implode("\n", $parts);
    }

    /**
     * 判断最终答复是否为「在正文中直接向用户提问/索要信息」：纯文本提问不会弹出输入框，
     * 命中时由服务端强制转为 crmeb_ask_user 问询卡片。判定宁缺勿滥，避免误伤带数据的完整答复。
     * @param string $content 剥离工具调用 JSON 与追问 JSON 后的最终答复正文
     * @return bool
     */
    private function looksLikeQuestionToUser(string $content): bool
    {
        $content = trim($content);
        $length = mb_strlen($content);
        // 完整答复通常携带数据与结论、长度明显更长；超长内容不转换
        if ($length === 0 || $length > 300) {
            return false;
        }
        // 明确的索取指令（可无问号，如「请补充结束日期」「请直接回复…」）
        if (preg_match(
            '~请(?:直接)?(?:补充|提供|回复|输入|告知)|麻烦(?:你|您)?(?:补充|提供|告知)|请告诉我|我(?:仍|还)?需要你(?:提供|补充|确认)~u',
            $content
        )) {
            return true;
        }
        // 疑问句兜底（如「请问您希望分析哪个时间段的数据？」）：短回复 + 问号
        if (mb_strpos($content, '？') === false && strpos($content, '?') === false) {
            return false;
        }
        // 含表格、标题、代码块等数据特征视为完整答复，不转换
        return !preg_match('~(^|\n)\s*(?:\||#|```)|\n\d+[.、]\s~', $content);
    }

    /**
     * 把模型以正文直接提问的回复强制转为 ask_user 问询卡片：落库 confirm 消息
     * （question 取回复正文、type=text 自由填写），用户提交答案后经 confirmStream 续跑工具循环
     * @param int $conversationId 会话 ID
     * @param string $question 模型回复正文（作为卡片问题）
     * @param array $context 对话上下文（取 allowedTools 与已加载 Skill 快照）
     * @param array $confirmContext 确认快照上下文（model_id/server_ids/skill_keys）
     * @return array{message_id:int,arguments:array,display:array}
     */
    private function forceAskUserCard(int $conversationId, string $question, array $context, array $confirmContext): array
    {
        $arguments = ['question' => $question, 'type' => 'text', 'allow_custom' => true];
        $display = ['title' => 'AI 需要你补充信息', 'params' => []];
        $confirmMessageId = $this->agents->addMessage(
            $conversationId,
            'confirm',
            $this->canonicalToolCall(McpToolService::ASK_USER_TOOL, $arguments),
            McpToolService::ASK_USER_TOOL,
            [
                'arguments' => $arguments,
                'status' => 'pending',
                'interaction' => 'ask_user',
                'display' => $display,
                'model_id' => (int)($confirmContext['model_id'] ?? 0),
                'server_ids' => (array)($confirmContext['server_ids'] ?? []),
                'skill_keys' => (array)($confirmContext['skill_keys'] ?? []),
                'skill_loaded' => array_keys($context['skillFiles']),
                'allowed_tools' => $context['allowedTools'],
            ]
        );
        return ['message_id' => $confirmMessageId, 'arguments' => $arguments, 'display' => $display];
    }

    /**
     * 判断回复是否为「计划性叙述后中断」：只说了接下来要做什么，没有交付实际内容。
     * 特征：文本较短（真正的最终答复会携带数据与结论，长度明显更长）且以未来动作叙述收尾。
     * 仅用于挂载了工具的对话轮次做继续提示，判定宁缺勿滥，避免把正常答复误判为中断。
     * @param string $content 剥离工具调用 JSON 后的正文
     * @return bool
     */
    private function looksLikeUnfinishedNarration(string $content): bool
    {
        $length = mb_strlen(trim($content));
        if ($length === 0 || $length > 300) {
            return false;
        }
        return (bool)preg_match(
            '~(接下来|下面|现在|马上)(我将|我会|我将要|为你|开始)|让我来|正在为你|正在查询|正在整理|我将逐一|逐一核查|开始查询|开始分析~u',
            $content
        );
    }

    /**
     * 提取工具调用轨迹中的工具名清单（用于持久化到 assistant 消息 payload，供前端历史回显「调用 xxx」标签；
     * 只存名称不存参数与结果，避免消息体膨胀，完整轨迹仅随 SSE done 事件下发）
     * @param array $toolTrace 工具调用轨迹（name/arguments/result）
     * @return array<int, array{name: string}>
     */
    private function toolTraceNames(array $toolTrace): array
    {
        return array_values(array_map(
            static fn(array $item): array => ['name' => (string)($item['name'] ?? '')],
            $toolTrace
        ));
    }

    /**
     * 解析模型回复中的工具调用意图
     *
     * 兼容格式：
     * 1. 纯 JSON / Markdown 包裹：{"tool_call":{"name":...,"arguments":{...}}}
     * 2. 混入正文或多个 JSON 连排（花括号深度扫描提取首个完整对象）
     * 3. 一号通平台私有标记：<|FunctionCallBegin|>[{"name":...,"arguments":{...}}]<|FunctionCallEnd|>
     * @return array{name:string,arguments:array}|null
     */
    private function parseToolCall(string $content): ?array
    {
        $raw = trim($content);
        // 依次尝试：整体原文 → Markdown 代码块 → 正文内首个完整工具调用对象
        $candidates = [$raw];
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/si', $raw, $match)) {
            $candidates[] = $match[1];
        }
        $extracted = $this->extractFirstToolCallObject($raw);
        if ($extracted !== null) {
            $candidates[] = $extracted;
        }
        foreach ($candidates as $candidate) {
            $payload = json_decode($candidate, true);
            $call = is_array($payload) ? ($payload['tool_call'] ?? null) : null;
            if (is_array($call) && trim((string)($call['name'] ?? '')) !== '') {
                return [
                    'name' => trim((string)$call['name']),
                    'arguments' => is_array($call['arguments'] ?? null) ? $call['arguments'] : [],
                    'display' => is_array($call['display'] ?? null) ? $call['display'] : [],
                ];
            }
        }
        // 一号通平台私有函数调用标记：<|FunctionCallBegin|>{...}|[...]<|FunctionCallEnd|>
        if (preg_match('/<\|FunctionCallBegin\|>\s*(\{.*?\}|\[.*?\])\s*<\|FunctionCallEnd\|>/s', $raw, $match)) {
            $decoded = json_decode($match[1], true);
            // 数组形式取首个调用
            if (isset($decoded[0]) && is_array($decoded[0])) {
                $decoded = $decoded[0];
            }
            if (is_array($decoded) && trim((string)($decoded['name'] ?? '')) !== '') {
                return [
                    'name' => trim((string)$decoded['name']),
                    'arguments' => is_array($decoded['arguments'] ?? null) ? $decoded['arguments'] : [],
                    'display' => is_array($decoded['display'] ?? null) ? $decoded['display'] : [],
                ];
            }
        }
        return null;
    }

    /**
     * 从文本中提取首个完整的 {"tool_call":{...}} JSON 对象（花括号深度扫描，忽略字符串内括号）
     *
     * 模型可能将多个工具调用 JSON 连排输出（如 {..}{..}{..}），正则贪婪/懒惰匹配均易截取错误，
     * 此处以首个 {"tool_call" 为起点按深度配对提取，保证取到第一个完整对象。
     * @param string $text 待扫描文本
     * @return string|null 完整 JSON 对象文本，未找到时返回 null
     */
    private function extractFirstToolCallObject(string $text): ?string
    {
        $start = strpos($text, '{"tool_call"');
        if ($start === false) {
            return null;
        }
        $depth = 0;
        $inString = false;
        $escape = false;
        $length = strlen($text);
        for ($i = $start; $i < $length; $i++) {
            $char = $text[$i];
            if ($inString) {
                if ($escape) {
                    $escape = false;
                } elseif ($char === '\\') {
                    $escape = true;
                } elseif ($char === '"') {
                    $inString = false;
                }
                continue;
            }
            if ($char === '"') {
                $inString = true;
            } elseif ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($text, $start, $i - $start + 1);
                }
            }
        }
        return null;
    }

    /**
     * 从最终答复中提取并剥离推荐追问 JSON（{"followups":[...]}，支持标记跨多次扫描）
     * @param string $content 模型最终答复（引用传递，剥离后为纯正文）
     * @return array<int, string> 推荐问题列表（最多 3 个），无有效内容时返回空数组
     */
    private function extractFollowups(string &$content): array
    {
        $marker = '{"followups"';
        $questions = [];
        // 模型可能复读输出多段 followups JSON（如连答两遍各带一段）：逐段剥离，防止残留原文展示给用户
        while (($start = strpos($content, $marker)) !== false) {
            // 与工具调用对象同规则的花括号深度扫描，取完整 JSON 对象
            $depth = 0;
            $inString = false;
            $escape = false;
            $length = strlen($content);
            $object = null;
            for ($i = $start; $i < $length; $i++) {
                $char = $content[$i];
                if ($inString) {
                    if ($escape) {
                        $escape = false;
                    } elseif ($char === '\\') {
                        $escape = true;
                    } elseif ($char === '"') {
                        $inString = false;
                    }
                    continue;
                }
                if ($char === '"') {
                    $inString = true;
                } elseif ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $object = substr($content, $start, $i - $start + 1);
                        break;
                    }
                }
            }
            if ($object === null) {
                // 未闭合的 followups JSON（多为输出被截断）：必然出现在答复末尾，从起点截断防泄漏
                $content = trim(substr($content, 0, $start));
                break;
            }
            $content = trim(substr($content, 0, $start) . substr($content, $start + strlen($object)));
            $decoded = json_decode($object, true);
            foreach ((array)($decoded['followups'] ?? []) as $question) {
                $question = trim((string)$question);
                if ($question !== '' && mb_strlen($question) <= 100) {
                    $questions[] = $question;
                }
            }
        }
        return array_slice(array_values(array_unique($questions)), 0, 3);
    }

    /**
     * 清理最终答复：剥离平台私有函数调用标记与残留的工具调用 JSON（含连排多个），避免原样展示给用户
     * @param string $content 模型原始回复
     * @return string
     */
    private function stripToolCallJson(string $content): string
    {
        $content = preg_replace('/<\|FunctionCallBegin\|>.*?(?:<\|FunctionCallEnd\|>|$)/s', '', $content) ?? '';
        // Markdown 代码块包裹的工具调用
        $content = preg_replace('/```(?:json)?\s*\{[^`]*"tool_call"[^`]*\}\s*```/si', '', $content) ?? '';
        // 正文内散落的工具调用 JSON（连排多个时逐个剥离）
        while (($pos = strpos($content, '{"tool_call"')) !== false) {
            $object = $this->extractFirstToolCallObject(substr($content, $pos));
            if ($object === null) {
                // 未闭合的工具调用 JSON（多为输出被 max_tokens 截断）：无法配对但必然不是答复内容，
                // 从起点整体剥离，避免原文泄漏给用户、也避免截断 JSON 随转录进入下轮被模型复读
                $content = substr($content, 0, $pos);
                break;
            }
            $content = substr($content, 0, $pos) . substr($content, $pos + strlen($object));
        }
        return trim((string)$content);
    }

    /**
     * 从模型回复中提取 <think>...</think> 内嵌思维链（兼容 </think_xxx> 变体闭合标签）
     * @param string $content 模型原始回复（引用传递，提取后为剔除思维链的正文）
     * @return string 提取出的思维链文本，无内嵌思维链时返回空串
     */
    private function extractThinkBlocks(string &$content): string
    {
        $reasoning = '';
        $content = preg_replace_callback(
            '~<think[^>]*>(.*?)</think[^>]*>~si',
            function (array $m) use (&$reasoning): string {
                $reasoning .= $m[1];
                return '';
            },
            $content
        );
        // 某些模型会输出非标准的“仅闭合”标记：正文前半段实际上是思维链，
        // 例如：思维链...</think_never_used_xxx>最终答复。把标记前内容归入 reasoning，
        // 避免内部过程和标签混入最终回答。
        if (preg_match('~\\A\\s*(.*?)</think[^>]*>~si', (string)$content, $match)) {
            $reasoning .= $match[1];
            $content = substr((string)$content, strlen($match[0]));
        }
        // 清理正文中仍可能残留的孤立闭合标记（包括跨流分片组合后的情况）。
        $content = preg_replace('~</think[^>]*>~si', '', (string)$content) ?? '';
        // 未闭合的 <think> 标签：其后内容全部视为思维链
        if (preg_match('~<think[^>]*>~i', (string)$content)) {
            $content = preg_replace_callback(
                '~<think[^>]*>(.*)$~si',
                function (array $m) use (&$reasoning): string {
                    $reasoning .= $m[1];
                    return '';
                },
                (string)$content
            );
        }
        $content = trim((string)$content);
        return trim($reasoning);
    }

    /**
     * 文本超长时按字节截断并追加省略号
     * @param string $text 原始文本
     * @param int $length 最大字节长度
     * @return string 截断后的文本
     */
    private function limitText(string $text, int $length): string
    {
        return strlen($text) > $length ? mb_strcut($text, 0, $length, 'UTF-8') . '…' : $text;
    }
}
